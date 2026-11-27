<?php

namespace App\Repositories\Contracts\StudyMaterial;

use App\Models\MaterialBookmark;
use App\Models\StudyMaterial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StudyMaterialRepositoryInterface
{
    public function create(array $attributes): StudyMaterial;

    public function update(
        StudyMaterial $material,
        array $attributes
    ): StudyMaterial;

    public function delete(StudyMaterial $material): bool;

    public function existsByFileHash(string $fileHash): bool;

    public function bookmark(
        int $userId,
        int $materialId
    ): MaterialBookmark;

    public function removeBookmark(
        int $userId,
        int $materialId
    ): bool;

    public function paginateActive(
        array $filters = [],
        ?int $perPage = null,
        ?int $viewerId = null
    ): LengthAwarePaginator;

    public function paginateForUser(
        int $userId,
        array $filters = [],
        ?int $perPage = null
    ): LengthAwarePaginator;

    public function paginateBookmarkedByUser(
        int $userId,
        array $filters = [],
        ?int $perPage = null
    ): LengthAwarePaginator;
}
