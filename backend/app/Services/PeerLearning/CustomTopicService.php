<?php

namespace App\Services\PeerLearning;

use App\Models\CustomTopic;
use App\Repositories\Contracts\PeerLearning\CustomTopicRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CustomTopicService
{
    public function __construct(
        protected CustomTopicRepositoryInterface $repository
    ) {}

    public function getRecentShared(?int $viewerId = null): Collection
    {
        return $this->repository->getRecentShared($viewerId);
    }

    public function createForUser(int $userId, array $data): CustomTopic
    {
        return $this->repository->createForUser($userId, $data);
    }

    public function delete(CustomTopic $customTopic): bool
    {
        return $this->repository->delete($customTopic);
    }
}
