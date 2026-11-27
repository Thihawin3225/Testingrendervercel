<?php

namespace App\Http\Controllers\Api\V1\Academic;

use App\Http\Requests\Academic\StoreSemesterRequest;
use App\Http\Requests\Academic\UpdateSemesterRequest;
use App\Http\Resources\Academic\SemesterResource;
use App\Models\Semester;
use App\Services\Academic\SemesterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as RoutingController;

class SemesterController extends RoutingController
{
    use AuthorizesRequests;

    public function __construct(
        protected SemesterService $service
    ) {}

    /**
     * Display a listing of semesters.
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

        $this->authorize('viewAny', Semester::class);

        return SemesterResource::collection(
            $this->service->getAll($validated)
        );
    }

    /**
     * Store a newly created semester.
     */
    public function store(StoreSemesterRequest $request): JsonResponse
    {
        $this->authorize('create', Semester::class);

        $semester = $this->service->create(
            $request->validated()
        );

        return (new SemesterResource($semester))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified semester.
     */
    public function show(int $semester)
    {
        $semester = $this->service->findById($semester);

        abort_if(!$semester, 404, 'Semester not found.');

        $this->authorize('view', $semester);

        return new SemesterResource($semester);
    }

    /**
     * Update the specified semester.
     */
    public function update(
        UpdateSemesterRequest $request,
        int $semester
    ) {
        $semester = $this->service->findById($semester);

        abort_if(!$semester, 404, 'Semester not found.');

        $this->authorize('update', $semester);

        $updatedSemester = $this->service->update(
            $semester,
            $request->validated()
        );

        return new SemesterResource($updatedSemester);
    }

    /**
     * Remove the specified semester.
     */
    public function destroy(int $semester): JsonResponse
    {
        $semester = $this->service->findById($semester);

        abort_if(!$semester, 404, 'Semester not found.');

        $this->authorize('delete', $semester);

        $this->service->delete($semester);

        return response()->json([
            'message' => 'Semester deleted successfully.',
        ]);
    }
}
