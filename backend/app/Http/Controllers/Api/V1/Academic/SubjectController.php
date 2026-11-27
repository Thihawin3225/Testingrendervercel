<?php

namespace App\Http\Controllers\Api\V1\Academic;

use App\Http\Requests\Academic\StoreSubjectRequest;
use App\Http\Requests\Academic\UpdateSubjectRequest;
use App\Http\Resources\Academic\SubjectResource;
use App\Models\Subject;
use App\Services\Academic\SubjectService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as RoutingController;

class SubjectController extends RoutingController
{
    use AuthorizesRequests;

    public function __construct(
        protected SubjectService $service
    ) {}

    /**
     * Display a listing of subjects.
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

        $this->authorize('viewAny', Subject::class);

        return SubjectResource::collection(
            $this->service->getAll($validated)
        );
    }

    /**
     * Store a newly created subject.
     */
    public function store(StoreSubjectRequest $request)
    {
        $this->authorize('create', Subject::class);

        $subject = $this->service->create(
            $request->validated()
        );

        return (new SubjectResource($subject))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified subject.
     */
    public function show(int $subject)
    {
        $subject = $this->service->findById($subject);

        abort_if(!$subject, 404, 'Subject not found.');

        $this->authorize('view', $subject);

        return new SubjectResource($subject);
    }

    /**
     * Update the specified subject.
     */
    public function update(
        UpdateSubjectRequest $request,
        int $subject
    ) {
        $subject = $this->service->findById($subject);

        abort_if(!$subject, 404, 'Subject not found.');

        $this->authorize('update', $subject);

        $updatedSubject = $this->service->update(
            $subject,
            $request->validated()
        );

        return new SubjectResource($updatedSubject);
    }

    /**
     * Remove the specified subject.
     */
    public function destroy(int $subject): JsonResponse
    {
        $subject = $this->service->findById($subject);

        abort_if(!$subject, 404, 'Subject not found.');

        $this->authorize('delete', $subject);

        $this->service->delete($subject);

        return response()->json([
            'message' => 'Subject deleted successfully.',
        ]);
    }
}
