<?php

namespace App\Repositories\Eloquent\StudyMaterial;

use App\Models\MaterialBookmark;
use App\Models\StudyMaterial;
use App\Repositories\Contracts\StudyMaterial\StudyMaterialRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StudyMaterialRepository implements StudyMaterialRepositoryInterface
{
    private const DEFAULT_PER_PAGE = 10;

    private const DEFAULT_BOOKMARKS_PER_PAGE = 20;

    public function create(array $attributes): StudyMaterial
    {
        return StudyMaterial::query()->create($attributes);
    }

    public function update(
        StudyMaterial $material,
        array $attributes
    ): StudyMaterial {
        $material->update($attributes);

        return $material->refresh();
    }

    public function delete(StudyMaterial $material): bool
    {
        return (bool) $material->delete();
    }

    public function existsByFileHash(string $fileHash): bool
    {
        return StudyMaterial::query()
            ->where('file_hash', $fileHash)
            ->exists();
    }

    public function bookmark(
        int $userId,
        int $materialId
    ): MaterialBookmark {
        return MaterialBookmark::query()->firstOrCreate([
            'user_id' => $userId,
            'study_material_id' => $materialId,
        ]);
    }

    public function removeBookmark(
        int $userId,
        int $materialId
    ): bool {
        return (bool) MaterialBookmark::query()
            ->where('user_id', $userId)
            ->where('study_material_id', $materialId)
            ->delete();
    }

    public function paginateActive(
        array $filters = [],
        ?int $perPage = null,
        ?int $viewerId = null
    ): LengthAwarePaginator {
        $query = $this->baseQuery()->where('status', 'active');

        $this->applyFilters($query, $filters);
        $this->withBookmarkState($query, $viewerId);

        return $query
            ->latest('id')
            ->paginate($perPage ?? self::DEFAULT_PER_PAGE);
    }

    public function paginateForUser(
        int $userId,
        array $filters = [],
        ?int $perPage = null
    ): LengthAwarePaginator {
        $query = $this->baseQuery()->where('user_id', $userId);

        $this->applyFilters($query, $filters);
        $this->withBookmarkState($query, $userId);

        return $query
            ->latest('id')
            ->paginate($perPage ?? self::DEFAULT_PER_PAGE);
    }

    public function paginateBookmarkedByUser(
        int $userId,
        array $filters = [],
        ?int $perPage = null
    ): LengthAwarePaginator {
        $query = $this->baseQuery()
            ->select(
                'study_materials.*',
                'material_bookmarks.created_at as bookmarked_at'
            )
            ->join(
                'material_bookmarks',
                function ($join) use ($userId): void {
                    $join->on(
                        'material_bookmarks.study_material_id',
                        '=',
                        'study_materials.id'
                    )->where('material_bookmarks.user_id', $userId);
                }
            )
            ->where('study_materials.status', 'active');

        $this->applyFilters($query, $filters);
        $this->withBookmarkState($query, $userId);

        return $query
            ->latest('material_bookmarks.created_at')
            ->paginate($perPage ?? self::DEFAULT_BOOKMARKS_PER_PAGE);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('original_filename', 'like', "%{$search}%")
                        ->orWhereHas('subjectOffering.subject', function (Builder $subjectQuery) use ($search): void {
                            $subjectQuery->where('subject_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['material_type'] ?? null, function (Builder $query, string $materialType): void {
                $query->{$materialType === 'official' ? 'whereNotNull' : 'whereNull'}(
                    'subject_offering_id'
                );
            })
            ->when($filters['academic_year_id'] ?? null, function (Builder $query, int|string $academicYearId): void {
                $query->whereHas('subjectOffering', function (Builder $offeringQuery) use ($academicYearId): void {
                    $offeringQuery->where('academic_year_id', $academicYearId);
                });
            })
            ->when($filters['year_level_id'] ?? null, function (Builder $query, int|string $yearLevelId): void {
                $query->whereHas('subjectOffering', function (Builder $offeringQuery) use ($yearLevelId): void {
                    $offeringQuery->where('year_level_id', $yearLevelId);
                });
            });
    }

    private function baseQuery(): Builder
    {
        return StudyMaterial::query()->with([
            'uploader:id,name',
            'subjectOffering.subject',
        ]);
    }

    private function withBookmarkState(Builder $query, ?int $viewerId): void
    {
        if ($viewerId === null) {
            return;
        }

        $query->withExists([
            'bookmarks as is_bookmarked' => fn (Builder $bookmarkQuery) => $bookmarkQuery
                ->where('user_id', $viewerId),
        ]);
    }
}
