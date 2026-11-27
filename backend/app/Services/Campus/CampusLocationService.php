<?php

namespace App\Services\Campus;

use App\Models\CampusLocation;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\Campus\CampusLocationRepositoryInterface;

class CampusLocationService
{
    public function __construct(
        private CampusLocationRepositoryInterface $repository
    ) {}

    public function getAllLocations(): Collection
    {
        return $this->repository->all();
    }

    public function getActiveLocations(): Collection
    {
        return $this->repository->getActiveLocations();
    }

    public function getLocation(int $id): CampusLocation
    {

        return $this->repository->findById($id);
    }

    public function createLocation(array $data): CampusLocation
    {
        $data['is_active'] = $data['is_active'] ?? true;
        return $this->repository->create($data);
    }

    public function updateLocation(
        CampusLocation $location,
        array $data
    ): CampusLocation {
        return $this->repository->update($location, $data);
    }

    public function deleteLocation(CampusLocation $location): bool
    {
        return $this->repository->delete($location);
    }
}
