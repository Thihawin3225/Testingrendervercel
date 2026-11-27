<?php

namespace App\Repositories\Contracts\Academic;

use App\Models\YearLevel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface YearLevelRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?YearLevel;

    public function create(array $data): YearLevel;

    public function update(
        YearLevel $yearLevel,
        array $data
    ): YearLevel;

    public function delete(YearLevel $yearLevel): bool;

    public function getNextSortOrder(): int;

    public function finalYearExists(?int $ignoreId = null): bool;
}
