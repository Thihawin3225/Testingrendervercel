<?php

namespace App\Repositories\Contracts\Academic;

use App\Models\Semester;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SemesterRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?Semester;

    public function create(array $data): Semester;

    public function update(Semester $semester, array $data): Semester;

    public function delete(Semester $semester): bool;
}
