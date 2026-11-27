<?php

namespace App\Repositories\Contracts\Academic;

use App\Models\Subject;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SubjectRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?Subject;

    public function create(array $data): Subject;

    public function update(Subject $subject, array $data): Subject;

    public function delete(Subject $subject): bool;
}
