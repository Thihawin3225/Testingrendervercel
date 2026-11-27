<?php

namespace App\Repositories\Contracts\Campus;

use App\Models\CampusLocation;
use Illuminate\Support\Collection;

interface CampusLocationRepositoryInterface
{
    public function all(): Collection;

    public function getActiveLocations(): Collection;

    public function findById(int $id): CampusLocation;

    public function create(array $data): CampusLocation;

    public function update(
        CampusLocation $location,
        array $data
    ): CampusLocation;

    public function delete(CampusLocation $location): bool;
}
