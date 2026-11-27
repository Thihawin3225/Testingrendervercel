<?php

namespace App\Repositories\Eloquent\Academic;

use App\Models\YearLevel;
use App\Repositories\Contracts\Academic\YearLevelRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class YearLevelRepository implements YearLevelRepositoryInterface
{
    public function __construct(
        protected YearLevel $model
    ) {}

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

    public function findById(int $id): ?YearLevel
    {
        return $this->model->newQuery()->find($id);
    }

    public function create(array $data): YearLevel
    {
        return $this->model->create($data);
    }

    public function update(
        YearLevel $yearLevel,
        array $data
    ): YearLevel {
        $yearLevel->update($data);

        return $yearLevel->refresh();
    }

    public function delete(YearLevel $yearLevel): bool
    {
        return (bool) $yearLevel->delete();
    }

    /**
     * Get the next available sort order.
     */
    public function getNextSortOrder(): int
    {
        $maximumSortOrder = $this->model
            ->newQuery()
            ->max('sort_order');

        return ($maximumSortOrder ?? 0) + 1;
    }

    /**
     * Check whether a Final Year already exists.
     */
    public function finalYearExists(?int $ignoreId = null): bool
    {
        return $this->model
            ->newQuery()
            ->whereRaw(
                "LOWER(name) REGEXP ?",
                ['(^|[^a-z])final([^a-z]|$)']
            )
            ->when(
                $ignoreId !== null,
                fn ($query) => $query->where('id', '!=', $ignoreId)
            )
            ->exists();
    }
}
