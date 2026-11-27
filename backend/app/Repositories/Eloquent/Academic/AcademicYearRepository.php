<?php

namespace App\Repositories\Eloquent\Academic;

use App\Models\AcademicYear;
use App\Repositories\Contracts\Academic\AcademicYearRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AcademicYearRepository implements AcademicYearRepositoryInterface
{
    public function __construct(
        protected AcademicYear $model
    ) {}

    /**
     * Get all academic years with pagination and filters.
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
            ->when(
                array_key_exists('is_active', $filters),
                function ($query) use ($filters) {
                    $query->where(
                        'is_active',
                        $filters['is_active']
                    );
                }
            )
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    /**
     * Find academic year by ID.
     */
    public function findById(int $id): ?AcademicYear
    {
        return $this->model
            ->newQuery()
            ->find($id);
    }

    /**
     * Create a new academic year.
     */
    public function create(array $data): AcademicYear
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing academic year.
     */
    public function update(
        AcademicYear $academicYear,
        array $data
    ): AcademicYear {
        $academicYear->update($data);

        return $academicYear->refresh();
    }

    /**
     * Delete an academic year.
     */
    public function delete(AcademicYear $academicYear): bool
    {
        return (bool) $academicYear->delete();
    }
}
