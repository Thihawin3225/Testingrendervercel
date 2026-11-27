<?php

namespace App\Http\Controllers\Api\V1\Academic;

use App\Http\Requests\Academic\StoreStudentEnrollmentRequest;
use App\Http\Requests\Academic\UpdateStudentEnrollmentRequest;
use App\Http\Resources\Academic\StudentEnrollmentResource;
use App\Models\StudentAcademicEnrollment;
use App\Services\Academic\StudentAcademicEnrollmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as RoutingController;
use Illuminate\Validation\Rule;

class StudentEnrollmentController extends RoutingController
{
    use AuthorizesRequests;

    public function __construct(
        protected StudentAcademicEnrollmentService $service
    ) {}

    /**
     * Display a listing of student academic enrollments.
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

            'status' => [
                'sometimes',
                'nullable',
                Rule::in([
                    'active',
                    'completed',
                    'withdrawn',
                    'suspended',
                ]),
            ],

            'academic_year_id' => [
                'sometimes',
                'integer',
                'exists:academic_years,id',
            ],

            'year_level_id' => [
                'sometimes',
                'integer',
                'exists:year_levels,id',
            ],

            'major_id' => [
                'sometimes',
                'integer',
                'exists:majors,id',
            ],

            'semester_id' => [
                'sometimes',
                'integer',
                'exists:semesters,id',
            ],

            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $this->authorize('viewAny', StudentAcademicEnrollment::class);

        return StudentEnrollmentResource::collection(
            $this->service->getAll($validated)
        );
    }

    /**
     * Store a newly created student academic enrollment.
     */
    public function store(StoreStudentEnrollmentRequest $request): JsonResponse
    {
        $this->authorize('create', StudentAcademicEnrollment::class);

        $enrollment = $this->service->create(
            $request->validated()
        );

        return (new StudentEnrollmentResource($enrollment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified student academic enrollment.
     */
    public function show(int $enrollment)
    {
        $enrollment = $this->service->findById($enrollment);

        abort_if(
            !$enrollment,
            404,
            'Student enrollment not found.'
        );

        $this->authorize('view', $enrollment);

        return new StudentEnrollmentResource($enrollment);
    }

    /**
     * Update the specified student academic enrollment.
     */
    public function update(
        UpdateStudentEnrollmentRequest $request,
        int $enrollment
    ) {
        $enrollment = $this->service->findById($enrollment);

        abort_if(
            !$enrollment,
            404,
            'Student enrollment not found.'
        );

        $this->authorize('update', $enrollment);

        $updatedEnrollment = $this->service->update(
            $enrollment,
            $request->validated()
        );

        return new StudentEnrollmentResource($updatedEnrollment);
    }

    /**
     * Remove the specified student academic enrollment.
     */
    public function destroy(int $enrollment): JsonResponse
    {
        $enrollment = $this->service->findById($enrollment);

        abort_if(
            !$enrollment,
            404,
            'Student enrollment not found.'
        );

        $this->authorize('delete', $enrollment);

        $this->service->delete($enrollment);

        return response()->json([
            'message' => 'Student enrollment deleted successfully.',
        ]);
    }
}
