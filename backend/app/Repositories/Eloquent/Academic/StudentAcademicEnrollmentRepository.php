<?php

namespace App\Repositories\Eloquent\Academic;

use App\Models\StudentAcademicEnrollment;
use App\Repositories\Contracts\Academic\StudentAcademicEnrollmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StudentAcademicEnrollmentRepository implements StudentAcademicEnrollmentRepositoryInterface
{
    public function __construct(
        protected StudentAcademicEnrollment $model
    ) {}

    /**
     * Get all student academic enrollments with pagination and filters.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->newQuery()
            ->with([
                'user',
                'academicYear',
                'yearLevel',
                'major',
                'semester',
            ])
            ->when(
                !empty($filters['search']),
                function ($query) use ($filters) {
                    $query->whereHas('user', function ($userQuery) use ($filters) {
                        $userQuery->where('name', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                    });
                }
            )
            ->when(
                !empty($filters['status']),
                function ($query) use ($filters) {
                    $query->where('status', $filters['status']);
                }
            )
            ->when(
                !empty($filters['academic_year_id']),
                function ($query) use ($filters) {
                    $query->where('academic_year_id', $filters['academic_year_id']);
                }
            )
            ->when(
                !empty($filters['year_level_id']),
                function ($query) use ($filters) {
                    $query->where('year_level_id', $filters['year_level_id']);
                }
            )
            ->when(
                !empty($filters['major_id']),
                function ($query) use ($filters) {
                    $query->where('major_id', $filters['major_id']);
                }
            )
            ->when(
                !empty($filters['semester_id']),
                function ($query) use ($filters) {
                    $query->where('semester_id', $filters['semester_id']);
                }
            )
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    /**
     * Find student academic enrollment by ID.
     */
    public function findById(int $id): ?StudentAcademicEnrollment
    {
        return $this->model
            ->newQuery()
            ->with([
                'user',
                'academicYear',
                'yearLevel',
                'major',
                'semester',
            ])
            ->find($id);
    }

    /**
     * Create a new student academic enrollment.
     */
    public function create(array $data): StudentAcademicEnrollment
    {
        $enrollment = $this->model->create($data);

        return $enrollment->load([
            'user',
            'academicYear',
            'yearLevel',
            'major',
            'semester',
        ]);
    }

    /**
     * Update an existing student academic enrollment.
     */
    public function update(
        StudentAcademicEnrollment $enrollment,
        array $data
    ): StudentAcademicEnrollment {
        $enrollment->update($data);

        return $enrollment->refresh()->load([
            'user',
            'academicYear',
            'yearLevel',
            'major',
            'semester',
        ]);
    }

    /**
     * Delete a student academic enrollment.
     */
    public function delete(StudentAcademicEnrollment $enrollment): bool
    {
        return (bool) $enrollment->delete();
    }
}
