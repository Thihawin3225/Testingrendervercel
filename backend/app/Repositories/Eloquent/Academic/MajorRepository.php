<?php

namespace App\Repositories\Eloquent\Academic;

use App\Models\Major;
use App\Repositories\Contracts\Academic\MajorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MajorRepository implements MajorRepositoryInterface
{
    public function __construct(
        protected Major $model
    ) {}

    /**
     * Get all majors with pagination and filters.
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
                            'name',
                            'like',
                            '%' . $filters['search'] . '%'
                        )
                        ->orWhere(
                            'code',
                            'like',
                            '%' . $filters['search'] . '%'
                        );
                    });
                }
            )
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    /**
     * Find major by ID.
     */
    public function findById(int $id): ?Major
    {
        return $this->model
            ->newQuery()
            ->find($id);
    }

    /**
     * Create a new major.
     */
    public function create(array $data): Major
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing major.
     */
    public function update(Major $major, array $data): Major
    {
        $major->update($data);

        return $major->refresh();
    }

    /**
     * Delete a major.
     */
    public function delete(Major $major): bool
    {
        return (bool) $major->delete();
    }
}
