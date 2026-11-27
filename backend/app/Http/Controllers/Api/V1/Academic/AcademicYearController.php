<?php

namespace App\Http\Controllers\Api\V1\Academic;

use App\Http\Requests\Academic\StoreAcademicYearRequest;
use App\Http\Requests\Academic\UpdateAcademicYearRequest;
use App\Http\Resources\Academic\AcademicYearResource;
use App\Models\AcademicYear;
use App\Services\Academic\AcademicYearService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as RoutingController;

class AcademicYearController extends RoutingController
{
    use AuthorizesRequests;

    public function __construct(
        protected AcademicYearService $service
    ) {}

    /**
     * Display a listing of academic years.
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

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $this->authorize('viewAny', AcademicYear::class);

        return AcademicYearResource::collection(
            $this->service->getAll($validated)
        );
    }

    /**
     * Get the latest academic year's start date.
     */
    public function latestStartDate(): JsonResponse
    {
        $latestAcademicYear = AcademicYear::query()
            ->orderByDesc('start_date')
            ->first();

        return response()->json([
            'data' => [
                'start_date' => $latestAcademicYear?->start_date,
            ],
        ]);
    }

    /**
     * Store a newly created academic year.
     */
    public function store(StoreAcademicYearRequest $request): JsonResponse
    {
        $this->authorize('create', AcademicYear::class);

        $academicYear = $this->service->create(
            $request->validated()
        );

        return (new AcademicYearResource($academicYear))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified academic year.
     */
    public function show(int $academicYear)
    {
        $academicYear = $this->service->findById($academicYear);

        abort_if(
            !$academicYear,
            404,
            'Academic year not found.'
        );

        $this->authorize('view', $academicYear);

        return new AcademicYearResource($academicYear);
    }

    /**
     * Update the specified academic year.
     */
    public function update(
        UpdateAcademicYearRequest $request,
        int $academicYear
    ) {
        $academicYear = $this->service->findById($academicYear);

        abort_if(
            !$academicYear,
            404,
            'Academic year not found.'
        );

        // Prevent editing inactive academic years
        abort_if(
            !$academicYear->is_active,
            403,
            'Inactive academic years cannot be edited.'
        );

        $this->authorize('update', $academicYear);

        $updatedAcademicYear = $this->service->update(
            $academicYear,
            $request->validated()
        );

        return new AcademicYearResource($updatedAcademicYear);
    }

    /**
     * Remove the specified academic year.
     */
    public function destroy(int $academicYear): JsonResponse
    {
        $academicYear = $this->service->findById($academicYear);

        abort_if(
            !$academicYear,
            404,
            'Academic year not found.'
        );

        abort(403, 'Academic years cannot be deleted.');
    }
}
