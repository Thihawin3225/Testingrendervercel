<?php

namespace App\Repositories\Contracts\Academic;

use App\Models\Major;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MajorRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?Major;

    public function create(array $data): Major;

    public function update(Major $major, array $data): Major;

    public function delete(Major $major): bool;
}
