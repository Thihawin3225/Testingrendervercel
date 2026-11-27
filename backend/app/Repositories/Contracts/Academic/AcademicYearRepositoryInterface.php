<?php

namespace App\Repositories\Contracts\Academic;

use App\Models\AcademicYear;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AcademicYearRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?AcademicYear;

    public function create(array $data): AcademicYear;

    public function update(AcademicYear $academicYear, array $data): AcademicYear;

    public function delete(AcademicYear $academicYear): bool;
}
