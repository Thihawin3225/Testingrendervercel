<?php

namespace App\Services\Academic;

use App\Models\Subject;
use App\Repositories\Contracts\Academic\SubjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubjectService
{
    public function __construct(
        protected SubjectRepositoryInterface $subjectRepository
    ) {}

    /**
     * Get all subjects with pagination and filters.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->subjectRepository->getAll($filters);
    }

    /**
     * Find subject by ID.
     */
    public function findById(int $id): ?Subject
    {
        return $this->subjectRepository->findById($id);
    }

    /**
     * Create a new subject.
     */
    public function create(array $data): Subject
    {
        return $this->subjectRepository->create($data);
    }

    /**
     * Update an existing subject.
     */
    public function update(Subject $subject, array $data): Subject
    {
        return $this->subjectRepository->update($subject, $data);
    }

    /**
     * Delete a subject.
     */
    public function delete(Subject $subject): bool
    {
        return $this->subjectRepository->delete($subject);
    }
}
