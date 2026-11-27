<?php

namespace App\Repositories\Eloquent\Academic;

use App\Models\Subject;
use App\Repositories\Contracts\Academic\SubjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubjectRepository implements SubjectRepositoryInterface
{
    public function __construct(
        protected Subject $model
    ) {}

    /**
     * Get all subjects with pagination and filters.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->newQuery()
            ->when(
                !empty($filters['search']),
                function ($query) use ($filters) {
                    $query->where(function ($query) use ($filters) {
                        $query->where(
                            'subject_name',
                            'like',
                            '%' . $filters['search'] . '%'
                        )
                        ->orWhere(
                            'subject_code',
                            'like',
                            '%' . $filters['search'] . '%'
                        );
                    });
                }
            )
            ->orderBy('subject_name')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    /**
     * Find subject by ID.
     */
    public function findById(int $id): ?Subject
    {
        return $this->model
            ->newQuery()
            ->find($id);
    }

    /**
     * Create a new subject.
     */
    public function create(array $data): Subject
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing subject.
     */
    public function update(Subject $subject, array $data): Subject
    {
        $subject->update($data);

        return $subject->refresh();
    }

    /**
     * Delete a subject.
     */
    public function delete(Subject $subject): bool
    {
        return (bool) $subject->delete();
    }
}
