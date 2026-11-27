<?php

namespace App\Http\Controllers\Api\V1\StudyMaterial;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudyMaterial\IndexBookmarkedStudyMaterialRequest;
use App\Http\Requests\StudyMaterial\IndexMyStudyMaterialRequest;
use App\Http\Requests\StudyMaterial\IndexStudyMaterialRequest;
use App\Http\Requests\StudyMaterial\StoreStudyMaterialRequest;
use App\Http\Requests\StudyMaterial\UpdateStudyMaterialRequest;
use App\Http\Resources\StudyMaterial\StudyMaterialResource;
use App\Models\StudyMaterial;
use App\Services\StudyMaterial\StudyMaterialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudyMaterialController extends Controller
{
    public function __construct(
        protected StudyMaterialService $service
    ) {}

    public function store(StoreStudyMaterialRequest $request): JsonResponse
    {
        $material = $this->service->upload(
            $request->user(),
            $request->validated()
        );

        return (new StudyMaterialResource($material))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateStudyMaterialRequest $request,
        StudyMaterial $studyMaterial
    ): StudyMaterialResource {
        $material = $this->service->update(
            $studyMaterial,
            $request->validated()
        );

        return new StudyMaterialResource($material);
    }

    public function destroy(StudyMaterial $studyMaterial): JsonResponse
    {
        $this->authorize('delete', $studyMaterial);

        $this->service->delete($studyMaterial);

        return response()->json([
            'message' => 'Study material deleted.',
        ]);
    }

    public function index(IndexStudyMaterialRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? null;

        unset($filters['per_page']);

        return StudyMaterialResource::collection(
            $this->service->browse(
                $filters,
                $perPage,
                (int) $request->user()->getAuthIdentifier()
            )
        );
    }

    public function mine(IndexMyStudyMaterialRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? null;

        unset($filters['per_page']);

        return StudyMaterialResource::collection(
            $this->service->getUploads(
                (int) $request->user()->getAuthIdentifier(),
                $filters,
                $perPage
            )
        );
    }

    public function bookmarks(IndexBookmarkedStudyMaterialRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? null;

        unset($filters['per_page']);

        return StudyMaterialResource::collection(
            $this->service->getBookmarkedMaterials(
                (int) $request->user()->getAuthIdentifier(),
                $filters,
                $perPage
            )
        );
    }

    public function download(
        Request $request,
        StudyMaterial $studyMaterial
    ): RedirectResponse {
        $this->authorize('view', $studyMaterial);

        return redirect()->away($this->service->downloadUrl($studyMaterial));
    }

    public function previewUrl(Request $request, StudyMaterial $studyMaterial): JsonResponse
    {
        $this->authorize('view', $studyMaterial);

        return response()->json([
            'url' => $this->service->previewUrl($studyMaterial),
        ]);
    }

    public function bookmark(Request $request, StudyMaterial $studyMaterial): JsonResponse
    {
        $this->authorize('view', $studyMaterial);

        $this->service->bookmark(
            (int) $request->user()->getAuthIdentifier(),
            $studyMaterial->id
        );

        return response()->json(['message' => 'Study material bookmarked.']);
    }

    public function removeBookmark(Request $request, StudyMaterial $studyMaterial): JsonResponse
    {
        $this->service->removeBookmark(
            (int) $request->user()->getAuthIdentifier(),
            $studyMaterial->id
        );

        return response()->json(['message' => 'Study material bookmark removed.']);
    }
}
