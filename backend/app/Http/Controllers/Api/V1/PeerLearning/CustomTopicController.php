<?php

namespace App\Http\Controllers\Api\V1\PeerLearning;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeerLearning\StoreCustomTopicRequest;
use App\Http\Resources\PeerLearning\CustomTopicResource;
use App\Models\CustomTopic;
use App\Services\PeerLearning\CustomTopicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomTopicController extends Controller
{
    public function __construct(
        protected CustomTopicService $service
    ) {}

    public function recent(Request $request): AnonymousResourceCollection
    {
        return CustomTopicResource::collection(
            $this->service->getRecentShared(
                (int) $request->user()->getAuthIdentifier()
            )
        );
    }

    public function store(StoreCustomTopicRequest $request): JsonResponse
    {
        $topic = $this->service->createForUser(
            (int) $request->user()->getAuthIdentifier(),
            $request->validated()
        );

        return (new CustomTopicResource($topic))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(CustomTopic $customTopic): JsonResponse
    {
        $this->authorize('delete', $customTopic);

        $this->service->delete($customTopic);

        return response()->json([
            'message' => 'Custom topic deleted.',
        ]);
    }
}
