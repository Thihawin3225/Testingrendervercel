<?php

namespace App\Repositories\Eloquent\Campus;

use App\Models\CampusLocation;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\Campus\CampusLocationRepositoryInterface;

class EloquentCampusLocationRepository implements CampusLocationRepositoryInterface
{
    public function all(): Collection
    {
        return CampusLocation::latest()->get();
    }

    public function getActiveLocations(): Collection
    {
        return CampusLocation::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): CampusLocation
    {
        return CampusLocation::findOrFail($id);
    }

    public function create(array $data): CampusLocation
    {
        return CampusLocation::create($data);
    }

    public function update(
        CampusLocation $location,
        array $data
    ): CampusLocation {
        $location->update($data);

        return $location->refresh();
    }

    public function delete(CampusLocation $location): bool
    {
        return (bool) $location->delete();
    }
}
