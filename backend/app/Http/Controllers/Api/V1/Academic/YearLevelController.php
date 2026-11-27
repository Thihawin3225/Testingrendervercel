<?php

namespace App\Http\Controllers\Api\V1\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreYearLevelRequest;
use App\Http\Requests\Academic\UpdateYearLevelRequest;
use App\Http\Resources\Academic\YearLevelResource;
use App\Models\YearLevel;
use App\Services\Academic\YearLevelService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as RoutingController;

class YearLevelController extends RoutingController
{
    use AuthorizesRequests;
    public function __construct(
        protected YearLevelService $service
    ) {}

    /**
     * Display a listing of year levels.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $this->authorize('viewAny', YearLevel::class);

        return YearLevelResource::collection(
            $this->service->getAll($validated)
        );
    }

    /**
     * Store a newly created year level.
     */
    public function store(StoreYearLevelRequest $request)
    {
        $this->authorize('create', YearLevel::class);

        $yearLevel = $this->service->create(
            $request->validated()
        );

        return (new YearLevelResource($yearLevel))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified year level.
     */
    public function show(int $yearLevel)
    {
        $yearLevel = $this->service->findById($yearLevel);

        abort_if(!$yearLevel, 404, 'Year level not found.');

        $this->authorize('view', $yearLevel);

        return new YearLevelResource($yearLevel);
    }

    /**
     * Update the specified year level.
     */
    public function update(
        UpdateYearLevelRequest $request,
        int $yearLevel
    ) {
        $yearLevel = $this->service->findById($yearLevel);

        abort_if(!$yearLevel, 404, 'Year level not found.');

        $this->authorize('update', $yearLevel);

        $updatedYearLevel = $this->service->update(
            $yearLevel,
            $request->validated()
        );

        return new YearLevelResource($updatedYearLevel);
    }

    /**
     * Remove the specified year level.
     */
    public function destroy(int $yearLevel): JsonResponse
    {
        $yearLevel = $this->service->findById($yearLevel);

        abort_if(!$yearLevel, 404, 'Year level not found.');

        $this->authorize('delete', $yearLevel);

        $this->service->delete($yearLevel);

        return response()->json([
            'message' => 'Year level deleted successfully.',
        ]);
    }
}
