<?php

namespace App\Services\Academic;
use App\Models\AcademicYear;
use Illuminate\Validation\ValidationException;

use App\Models\StudentAcademicEnrollment;
use App\Repositories\Contracts\Academic\StudentAcademicEnrollmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StudentAcademicEnrollmentService
{
    public function __construct(
        protected StudentAcademicEnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    /**
     * Get all student academic enrollments with pagination and filters.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->enrollmentRepository->getAll($filters);
    }

    /**
     * Find student academic enrollment by ID.
     */
    public function findById(int $id): ?StudentAcademicEnrollment
    {
        return $this->enrollmentRepository->findById($id);
    }
    
    /*  validation for the academic years */
    private function validateActiveAcademicYear(int $academicYearId): void
    {
        $academicYear = AcademicYear::findOrFail($academicYearId);

        if (!$academicYear->is_active) {
            throw ValidationException::withMessages([
                'academic_year_id' => [
                    'Cannot enroll because the academic year is inactive.'
                ],
            ]);
        }
    }
    /**
     * Create a new student academic enrollment.
     */
    public function create(array $data): StudentAcademicEnrollment
    {
        return DB::transaction(function () use ($data) {

            $this->validateActiveAcademicYear(
                (int) $data['academic_year_id']
            );

            return $this->enrollmentRepository->create($data);
        });
    }

    /**
     * Update an existing student academic enrollment.
     */
    public function update(
        StudentAcademicEnrollment $enrollment,
        array $data
    ): StudentAcademicEnrollment {
        return DB::transaction(function () use ($enrollment, $data) {

            // Validate only when changing to another academic year
            if (
                isset($data['academic_year_id']) &&
                (int) $data['academic_year_id'] !== (int) $enrollment->academic_year_id
            ) {
                $this->validateActiveAcademicYear(
                    (int) $data['academic_year_id']
                );
            }

            return $this->enrollmentRepository->update(
                $enrollment,
                $data
            );
        });
    }

    /**
     * Delete a student academic enrollment.
     */
    public function delete(StudentAcademicEnrollment $enrollment): bool
    {
        return DB::transaction(function () use ($enrollment) {

            return $this->enrollmentRepository->delete($enrollment);

        });
    }
}
