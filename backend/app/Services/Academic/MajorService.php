<?php

namespace App\Services\Academic;

use App\Models\Major;
use App\Repositories\Contracts\Academic\MajorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MajorService
{
    public function __construct(
        protected MajorRepositoryInterface $majorRepository
    ) {}

    /**
     * Get all majors with pagination and filters.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->majorRepository->getAll($filters);
    }

    /**
     * Find major by ID.
     */
    public function findById(int $id): ?Major
    {
        return $this->majorRepository->findById($id);
    }

    /**
     * Create a new major.
     */
    public function create(array $data): Major
    {
        return $this->majorRepository->create($data);
    }

    /**
     * Update an existing major.
     */
    public function update(Major $major, array $data): Major
    {
        return $this->majorRepository->update($major, $data);
    }

    /**
     * Delete a major.
     */
    public function delete(Major $major): bool
    {
        return $this->majorRepository->delete($major);
    }
}