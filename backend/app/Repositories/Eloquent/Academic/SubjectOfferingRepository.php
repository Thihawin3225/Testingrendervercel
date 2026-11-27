<?php

namespace App\Repositories\Eloquent\Academic;

use App\Models\SubjectOffering;
use App\Repositories\Contracts\Academic\SubjectOfferingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubjectOfferingRepository implements SubjectOfferingRepositoryInterface
{
    public function __construct(
        protected SubjectOffering $model
    ) {}

    /**
     * Get all subject offerings with pagination and filters.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->newQuery()
            ->with([
                'academicYear',
                'yearLevel',
                'major',
                'semester',
                'subject',
            ])
            ->when(
                !empty($filters['search']),
                function ($query) use ($filters) {
                    $query->whereHas('subject', function ($query) use ($filters) {
                        $query->where('subject_name', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('subject_code', 'like', '%' . $filters['search'] . '%');
                    });
                }
            )
            ->when(
                !empty($filters['academic_year_id']),
                fn ($query) => $query->where(
                    'academic_year_id',
                    $filters['academic_year_id']
                )
            )
            ->when(
                !empty($filters['year_level_id']),
                fn ($query) => $query->where(
                    'year_level_id',
                    $filters['year_level_id']
                )
            )
            ->when(
                !empty($filters['major_id']),
                fn ($query) => $query->where(
                    'major_id',
                    $filters['major_id']
                )
            )
            ->when(
                !empty($filters['semester_id']),
                fn ($query) => $query->where(
                    'semester_id',
                    $filters['semester_id']
                )
            )
            ->when(
                !empty($filters['subject_id']),
                fn ($query) => $query->where(
                    'subject_id',
                    $filters['subject_id']
                )
            )
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    /**
     * Find subject offering by ID.
     */
    public function findById(int $id): ?SubjectOffering
    {
        return $this->model
            ->newQuery()
            ->with([
                'academicYear',
                'yearLevel',
                'major',
                'semester',
                'subject',
            ])
            ->find($id);
    }

    /**
     * Create a new subject offering.
     */
    public function create(array $data): SubjectOffering
    {
        $subjectOffering = $this->model->create($data);

        return $subjectOffering->load([
            'academicYear',
            'yearLevel',
            'major',
            'semester',
            'subject',
        ]);
    }

    /**
     * Update an existing subject offering.
     */
    public function update(
        SubjectOffering $subjectOffering,
        array $data
    ): SubjectOffering {
        $subjectOffering->update($data);

        return $subjectOffering->refresh()->load([
            'academicYear',
            'yearLevel',
            'major',
            'semester',
            'subject',
        ]);
    }

    /**
     * Delete a subject offering.
     */
    public function delete(SubjectOffering $subjectOffering): bool
    {
        return (bool) $subjectOffering->delete();
    }
}
