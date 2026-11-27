<?php

namespace App\Http\Controllers\Api\V1\Academic;

use App\Http\Requests\Academic\StoreSubjectOfferingRequest;
use App\Http\Requests\Academic\UpdateSubjectOfferingRequest;
use App\Http\Resources\Academic\SubjectOfferingResource;
use App\Models\SubjectOffering;
use App\Services\Academic\SubjectOfferingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as RoutingController;

class SubjectOfferingController extends RoutingController
{
    use AuthorizesRequests;

    public function __construct(
        protected SubjectOfferingService $service
    ) {}

    /**
     * Display a listing of subject offerings.
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

            'subject_id' => [
                'sometimes',
                'integer',
                'exists:subjects,id',
            ],

            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $this->authorize('viewAny', SubjectOffering::class);

        return SubjectOfferingResource::collection(
            $this->service->getAll($validated)
        );
    }

    public function store(StoreSubjectOfferingRequest $request)
    {
        $this->authorize('create', SubjectOffering::class);

        $data = $request->validated();

        if (isset($data['subject'])) {
            $subjectOffering = $this->service->createWithSubject($data);
        } else {
            $subjectOffering = $this->service->create([
                'subject_id' => $data['subject_id'],
                'academic_year_id' => $data['academic_year_id'],
                'year_level_id' => $data['year_level_id'],
                'major_id' => $data['major_id'],
                'semester_id' => $data['semester_id'],
            ]);
        }

        return (new SubjectOfferingResource($subjectOffering))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified subject offering.
     */
    public function show(int $subjectOffering)
    {
        $subjectOffering = $this->service->findById($subjectOffering);

        abort_if(!$subjectOffering, 404, 'Subject offering not found.');

        $this->authorize('view', $subjectOffering);

        return new SubjectOfferingResource($subjectOffering);
    }

    /**
     * Update the specified subject offering.
     */
    public function update(
        UpdateSubjectOfferingRequest $request,
        int $subjectOffering
    ) {
        $subjectOffering = $this->service->findById($subjectOffering);

        abort_if(!$subjectOffering, 404, 'Subject offering not found.');

        $this->authorize('update', $subjectOffering);

        $updatedSubjectOffering = $this->service->update(
            $subjectOffering,
            $request->validated()
        );

        return new SubjectOfferingResource($updatedSubjectOffering);
    }

    /**
     * Remove the specified subject offering.
     */
    public function destroy(int $subjectOffering): JsonResponse
    {
        $subjectOffering = $this->service->findById($subjectOffering);

        abort_if(!$subjectOffering, 404, 'Subject offering not found.');

        $this->authorize('delete', $subjectOffering);

        $this->service->delete($subjectOffering);

        return response()->json([
            'message' => 'Subject offering deleted successfully.',
        ]);
    }
}
