<?php

namespace App\Services\PeerLearning;

use App\Models\PeerLearningRequest;
use App\Repositories\Contracts\PeerLearning\PeerLearningRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PeerLearningRequestService
{
    public function __construct(
        protected PeerLearningRequestRepositoryInterface $repository
    ) {}

    public function getAllForLearner(
        int $learnerId,
        array $filters = [],
        ?int $perPage = null
    ): LengthAwarePaginator {
        return $this->repository->paginateForLearner(
            $learnerId,
            $filters,
            $perPage
        );
    }

    public function getOpenRequestsForFeed(
        int $learnerId,
        array $filters = [],
        ?int $perPage = null
    ): LengthAwarePaginator {
        return $this->repository->paginateOpenForFeed(
            $learnerId,
            $filters,
            $perPage
        );
    }

    public function getByIdForLearner(
        int $learnerId,
        int $requestId
    ): PeerLearningRequest {
        return $this->repository->findForLearner($learnerId, $requestId);
    }

    public function createForLearner(
        int $learnerId,
        array $data
    ): PeerLearningRequest {
        return $this->repository->createForLearner($learnerId, $data);
    }

    public function updateForLearner(
        int $learnerId,
        int $requestId,
        array $data
    ): PeerLearningRequest {
        $request = $this->repository->findForLearner(
            $learnerId,
            $requestId
        );

        return $this->repository->update($request, $data);
    }

    /**
     * Returns false when the request already has matches.
     */
    public function deleteForLearner(
        int $learnerId,
        int $requestId
    ): bool {
        $request = $this->repository->findForLearner(
            $learnerId,
            $requestId
        );

        if ($request->peerLearningMatches()->exists()) {
            return false;
        }

        return $this->repository->delete($request);
    }
}
