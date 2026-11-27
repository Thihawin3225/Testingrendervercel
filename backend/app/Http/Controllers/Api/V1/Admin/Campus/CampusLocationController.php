<?php

namespace App\Http\Controllers\Api\V1\Admin\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusLocation;
use App\Services\Campus\CampusLocationService;
use App\Http\Resources\Campus\CampusLocationResource;
use App\Http\Requests\Campus\StoreCampusLocationRequest;
use App\Http\Requests\Campus\UpdateCampusLocationRequest;
use Illuminate\Routing\Controller as RoutingController;
use Illuminate\Support\Facades\Gate;

class CampusLocationController extends RoutingController
{
    public function __construct(
        private CampusLocationService $service
    ) {}

    // GET: All locations (Admin)
    public function index()
    {
        Gate::authorize('viewAny', CampusLocation::class);

        $locations = $this->service->getAllLocations();

        return CampusLocationResource::collection($locations);
    }

    // GET: Single location (Admin)
    public function show(int $id)
    {
        $location = $this->service->getLocation($id);

        Gate::authorize('view', $location);

        return new CampusLocationResource($location);
    }

    // POST: Create location
    public function store(StoreCampusLocationRequest $request)
    {
        $location = $this->service->createLocation(
            $request->validated()
        );

        return (new CampusLocationResource($location))
            ->response()
            ->setStatusCode(201);
    }

    // PUT/PATCH: Update location
    public function update(
        UpdateCampusLocationRequest $request,
        int $id
    ) {
        $location = $this->service->getLocation($id);

        Gate::authorize('update', $location);

        $location = $this->service->updateLocation(
            $location,
            $request->validated()
        );

        return new CampusLocationResource($location);
    }

    // DELETE: Delete location
    public function destroy(int $id)
    {
        $location = $this->service->getLocation($id);

        Gate::authorize('delete', $location);

        $this->service->deleteLocation($location);

        return response()->json([
            'message' => 'Campus location deleted successfully.',
        ]);
    }

    // GET: Active locations (Student dropdown)
    public function activeLocations()
    {
        $locations = $this->service->getActiveLocations();

        return CampusLocationResource::collection($locations);
    }
    public function deactivate(int $id)
    {
        // 1. Find location
        $location = $this->service->getLocation($id);

        // 2. Check admin permission
        Gate::authorize('update', $location);

        // 3. Deactivate location
        $location = $this->service->updateLocation(
            $location,
            ['is_active' => false]
        );

        // 4. Return response
        return response()->json([
            'message' => 'Campus location deactivated successfully.',
            'data' => new CampusLocationResource($location),
        ]);
    }
}
