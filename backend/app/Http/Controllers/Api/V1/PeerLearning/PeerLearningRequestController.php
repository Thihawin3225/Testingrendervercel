<?php

namespace App\Http\Controllers\Api\V1\PeerLearning;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeerLearning\IndexLearningNeedsFeedRequest;
use App\Http\Requests\PeerLearning\IndexPeerLearningRequest;
use App\Http\Requests\PeerLearning\StorePeerLearningRequest;
use App\Http\Requests\PeerLearning\UpdatePeerLearningRequest;
use App\Http\Resources\PeerLearning\PeerLearningRequestResource;
use App\Models\PeerLearningRequest as PeerLearningRequestModel;
use App\Services\PeerLearning\PeerLearningRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeerLearningRequestController extends Controller
{
    public function __construct(
        protected PeerLearningRequestService $service
    ) {}

    public function index(IndexPeerLearningRequest $request)
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? null;

        unset($filters['per_page']);

        $requests = $this->service->getAllForLearner(
            (int) $request->user()->getAuthIdentifier(),
            $filters,
            $perPage
        );

        return PeerLearningRequestResource::collection($requests);
    }

    public function feed(IndexLearningNeedsFeedRequest $request)
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? null;

        unset($filters['per_page']);

        $requests = $this->service->getOpenRequestsForFeed(
            (int) $request->user()->getAuthIdentifier(),
            $filters,
            $perPage
        );

        return PeerLearningRequestResource::collection($requests);
    }

    public function store(StorePeerLearningRequest $request): JsonResponse
    {
        $learnerId = (int) $request->user()->getAuthIdentifier();

        $created = $this->service->createForLearner(
            $learnerId,
            $request->validated()
        );

        $record = $this->service->getByIdForLearner(
            $learnerId,
            $created->id
        );

        return (new PeerLearningRequestResource($record))
            ->response()
            ->setStatusCode(201);
    }

    public function show(
        Request $request,
        PeerLearningRequestModel $peerLearningRequest
    ): PeerLearningRequestResource {
        $this->authorize('view', $peerLearningRequest);

        $record = $this->service->getByIdForLearner(
            (int) $request->user()->getAuthIdentifier(),
            $peerLearningRequest->id
        );

        return new PeerLearningRequestResource($record);
    }

    public function update(
        UpdatePeerLearningRequest $request,
        PeerLearningRequestModel $peerLearningRequest
    ): PeerLearningRequestResource {
        $learnerId = (int) $request->user()->getAuthIdentifier();

        $this->service->updateForLearner(
            $learnerId,
            $peerLearningRequest->id,
            $request->validated()
        );

        $record = $this->service->getByIdForLearner(
            $learnerId,
            $peerLearningRequest->id
        );

        return new PeerLearningRequestResource($record);
    }

    public function destroy(
        Request $request,
        PeerLearningRequestModel $peerLearningRequest
    ): JsonResponse {
        $this->authorize('delete', $peerLearningRequest);

        $deleted = $this->service->deleteForLearner(
            (int) $request->user()->getAuthIdentifier(),
            $peerLearningRequest->id
        );

        if (! $deleted) {
            return response()->json([
                'message' => 'This request has matches and cannot be deleted.',
            ], 409);
        }

        return response()->json([
            'message' => 'Learning request deleted.',
        ]);
    }
}
