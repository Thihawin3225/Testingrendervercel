<?php

namespace App\Services\Academic;

use App\Models\AcademicYear;
use App\Repositories\Contracts\Academic\AcademicYearRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class AcademicYearService
{
    public function __construct(
        protected AcademicYearRepositoryInterface $academicYearRepository
    ) {}

    /**
     * Get all academic years with pagination and filters.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->academicYearRepository->getAll($filters);
    }

    /**
     * Find academic year by ID.
     */
    public function findById(int $id): ?AcademicYear
    {
        return $this->academicYearRepository->findById($id);
    }

    /**
     * Create a new academic year.
     */
    public function create(array $data): AcademicYear
    {
        return DB::transaction(function () use ($data) {

            $latestAcademicYear = AcademicYear::query()
                ->orderBy('start_date', 'desc')
                ->first();

            if (
                $latestAcademicYear &&
                $data['start_date'] <= $latestAcademicYear->start_date
            ) {
                throw ValidationException::withMessages([
                    'name' => 'You cannot create an academic year before the latest existing academic year.',
                ]);
            }

            AcademicYear::query()->update(['is_active' => false]);

            $data['is_active'] = true;

            return $this->academicYearRepository->create($data);
        });
    }

    /**
     * Update an existing academic year.
     */
    public function update(
        AcademicYear $academicYear,
        array $data
    ): AcademicYear {
        return DB::transaction(function () use ($academicYear, $data) {

            // Prevent editing inactive academic years
            abort_unless(
                $academicYear->is_active,
                403,
                'Inactive academic years cannot be edited.'
            );

            // Do not allow changing active status through update
            unset($data['is_active']);

            return $this->academicYearRepository->update(
                $academicYear,
                $data
            );
        });
    }

    /**
     * Delete an academic year.
     */
    public function delete(AcademicYear $academicYear): bool
    {
        abort(
            403,
            'Academic years cannot be deleted.'
        );
    }
}