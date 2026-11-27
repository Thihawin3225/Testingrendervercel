<?php

namespace App\Repositories\Contracts\Academic;

use App\Models\StudentAcademicEnrollment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StudentAcademicEnrollmentRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?StudentAcademicEnrollment;

    public function create(array $data): StudentAcademicEnrollment;

    public function update(
        StudentAcademicEnrollment $enrollment,
        array $data
    ): StudentAcademicEnrollment;

    public function delete(StudentAcademicEnrollment $enrollment): bool;
}
