<?php

namespace App\Repositories\Eloquent\Academic;

use App\Models\Semester;
use App\Repositories\Contracts\Academic\SemesterRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SemesterRepository implements SemesterRepositoryInterface
{
    public function __construct(
        protected Semester $model
    ) {}

    /**
     * Get all semesters with pagination and filters.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->newQuery()
            ->when(
                !empty($filters['search']),
                function ($query) use ($filters) {
                    $query->where(
                        'name',
                        'like',
                        '%' . $filters['search'] . '%'
                    );
                }
            )
            ->orderBy('sort_order')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    /**
     * Find semester by ID.
     */
    public function findById(int $id): ?Semester
    {
        return $this->model
            ->newQuery()
            ->find($id);
    }

    /**
     * Create a new semester.
     */
    public function create(array $data): Semester
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing semester.
     */
    public function update(Semester $semester, array $data): Semester
    {
        $semester->update($data);

        return $semester->refresh();
    }

    /**
     * Delete a semester.
     */
    public function delete(Semester $semester): bool
    {
        return (bool) $semester->delete();
    }
}
