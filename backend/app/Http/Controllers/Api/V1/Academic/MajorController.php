<?php

namespace App\Http\Controllers\Api\V1\Academic;

use App\Http\Requests\Academic\StoreMajorRequest;
use App\Http\Requests\Academic\UpdateMajorRequest;
use App\Http\Resources\Academic\MajorResource;
use App\Models\Major;
use App\Services\Academic\MajorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as RoutingController;

class MajorController extends RoutingController
{
    use AuthorizesRequests;

    public function __construct(
        protected MajorService $service
    ) {}

    /**
     * Display a listing of majors.
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

        $this->authorize('viewAny', Major::class);

        return MajorResource::collection(
            $this->service->getAll($validated)
        );
    }

    /**
     * Store a newly created major.
     */
    public function store(StoreMajorRequest $request)
    {
        $this->authorize('create', Major::class);

        $major = $this->service->create(
            $request->validated()
        );

        return (new MajorResource($major))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified major.
     */
    public function show(int $major)
    {
        $major = $this->service->findById($major);

        abort_if(!$major, 404, 'Major not found.');

        $this->authorize('view', $major);

        return new MajorResource($major);
    }

    /**
     * Update the specified major.
     */
    public function update(
        UpdateMajorRequest $request,
        int $major
    ) {
        $major = $this->service->findById($major);

        abort_if(!$major, 404, 'Major not found.');

        $this->authorize('update', $major);

        $updatedMajor = $this->service->update(
            $major,
            $request->validated()
        );

        return new MajorResource($updatedMajor);
    }

    /**
     * Remove the specified major.
     */
    public function destroy(int $major): JsonResponse
    {
        $major = $this->service->findById($major);

        abort_if(!$major, 404, 'Major not found.');

        $this->authorize('delete', $major);

        $this->service->delete($major);

        return response()->json([
            'message' => 'Major deleted successfully.',
        ]);
    }
}