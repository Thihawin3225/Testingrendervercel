<?php

namespace App\Repositories\Contracts\Academic;

use App\Models\SubjectOffering;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SubjectOfferingRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?SubjectOffering;

    public function create(array $data): SubjectOffering;

    public function update(SubjectOffering $subjectOffering, array $data): SubjectOffering;

    public function delete(SubjectOffering $subjectOffering): bool;
}
