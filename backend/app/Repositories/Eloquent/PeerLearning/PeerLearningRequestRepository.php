<?php

namespace App\Repositories\Eloquent\PeerLearning;

use App\Models\PeerLearningRequest;
use App\Repositories\Contracts\PeerLearning\PeerLearningRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PeerLearningRequestRepository implements PeerLearningRequestRepositoryInterface
{
    private const DEFAULT_PER_PAGE = 10;

    public function __construct(
        protected PeerLearningRequest $model
    ) {}

    public function paginateForLearner(
        int $learnerId,
        array $filters = [],
        ?int $perPage = null
    ): LengthAwarePaginator {
        $perPage ??= self::DEFAULT_PER_PAGE;

        return $this->model
            ->newQuery()
            ->where('learner_id', $learnerId)
            ->with(['subjectOffering.subject', 'customTopic'])
            ->withCount('peerLearningMatches')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['academic_year_id'] ?? null, function ($query, $academicYearId) {
                $query->whereHas('subjectOffering', function ($offeringQuery) use ($academicYearId) {
                    $offeringQuery->where('academic_year_id', $academicYearId);
                });
            })
            ->when($filters['year_level_id'] ?? null, function ($query, $yearLevelId) {
                $query->whereHas('subjectOffering', function ($offeringQuery) use ($yearLevelId) {
                    $offeringQuery->where('year_level_id', $yearLevelId);
                });
            })
            ->when($filters['topic_type'] ?? null, function ($query, $topicType) {
                $query->{$topicType === 'official' ? 'whereNotNull' : 'whereNull'}(
                    'subject_offering_id'
                );
            })
            ->latest('id')
            ->paginate($perPage);
    }

    public function paginateOpenForFeed(
        int $learnerId,
        array $filters = [],
        ?int $perPage = null
    ): LengthAwarePaginator {
        $perPage ??= self::DEFAULT_PER_PAGE;

        return $this->model
            ->newQuery()
            ->where('learner_id', '!=', $learnerId)
            ->where('status', 'open')
            ->whereHas('learner', function ($query) {
                $query->where('role', 'student')
                    ->where('status', 'active');
            })
            ->with([
                'customTopic:id,title',
                'learner:id,name',
                'subjectOffering.subject',
            ])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('customTopic', function ($topicQuery) use ($search) {
                            $topicQuery->where('title', 'like', "%{$search}%");
                        })
                        ->orWhereHas('subjectOffering.subject', function ($subjectQuery) use ($search) {
                            $subjectQuery->where('subject_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['academic_year_id'] ?? null, function ($query, $academicYearId) {
                $query->whereHas('subjectOffering', function ($offeringQuery) use ($academicYearId) {
                    $offeringQuery->where('academic_year_id', $academicYearId);
                });
            })
            ->when($filters['year_level_id'] ?? null, function ($query, $yearLevelId) {
                $query->whereHas('subjectOffering', function ($offeringQuery) use ($yearLevelId) {
                    $offeringQuery->where('year_level_id', $yearLevelId);
                });
            })
            ->when($filters['topic_type'] ?? null, function ($query, $topicType) {
                $query->{$topicType === 'official' ? 'whereNotNull' : 'whereNull'}(
                    'subject_offering_id'
                );
            })
            ->latest('id')
            ->paginate($perPage);
    }

    public function findForLearner(
        int $learnerId,
        int $requestId
    ): PeerLearningRequest {
        return $this->model
            ->newQuery()
            ->where('learner_id', $learnerId)
            ->with(['subjectOffering.subject', 'customTopic'])
            ->withCount('peerLearningMatches')
            ->findOrFail($requestId);
    }

    public function createForLearner(
        int $learnerId,
        array $data
    ): PeerLearningRequest {
        $data['learner_id'] = $learnerId;

        return $this->model
            ->newQuery()
            ->create($data);
    }

    public function update(
        PeerLearningRequest $request,
        array $data
    ): PeerLearningRequest {
        $request->update($data);

        return $request->refresh();
    }

    public function delete(PeerLearningRequest $request): bool
    {
        return (bool) $request->delete();
    }

    public function hasConflictingScheduleForLearner(
        int $learnerId,
        string $startDate,
        ?string $endDate,
        string $startTime,
        string $endTime,
        ?int $ignoreRequestId = null
    ): bool {
        $effectiveEndDate = $endDate ?: $startDate;

        return $this->model
            ->newQuery()
            ->where('learner_id', $learnerId)
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->whereNotNull('preferred_start_date')
            ->whereNotNull('preferred_start_time')
            ->whereNotNull('preferred_end_time')
            ->when(
                $ignoreRequestId !== null,
                fn ($query) => $query->where('id', '!=', $ignoreRequestId)
            )
            ->where(function ($query) use ($startDate, $effectiveEndDate) {
                $query->where('preferred_start_date', '<=', $effectiveEndDate)
                    ->whereRaw('COALESCE(preferred_end_date, preferred_start_date) >= ?', [$startDate]);
            })
            ->where('preferred_start_time', '<', $endTime)
            ->where('preferred_end_time', '>', $startTime)
            ->exists();
    }
}
