<?php

namespace App\Repositories\Contracts\PeerLearning;

use App\Models\PeerLearningRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PeerLearningRequestRepositoryInterface
{
    public function paginateForLearner(
        int $learnerId,
        array $filters = [],
        ?int $perPage = null
    ): LengthAwarePaginator;

    public function paginateOpenForFeed(
        int $learnerId,
        array $filters = [],
        ?int $perPage = null
    ): LengthAwarePaginator;

    public function findForLearner(
        int $learnerId,
        int $requestId
    ): PeerLearningRequest;

    public function createForLearner(
        int $learnerId,
        array $data
    ): PeerLearningRequest;

    public function update(
        PeerLearningRequest $request,
        array $data
    ): PeerLearningRequest;

    public function delete(PeerLearningRequest $request): bool;

    public function hasConflictingScheduleForLearner(
        int $learnerId,
        string $startDate,
        ?string $endDate,
        string $startTime,
        string $endTime,
        ?int $ignoreRequestId = null
    ): bool;
}
