<?php

namespace App\Repositories\Eloquent\PeerLearning;

use App\Models\CustomTopic;
use App\Repositories\Contracts\PeerLearning\CustomTopicRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CustomTopicRepository implements CustomTopicRepositoryInterface
{
    private const RECENT_TOPICS_LIMIT = 5;

    public function __construct(
        protected CustomTopic $model
    ) {}

    public function getRecentShared(?int $viewerId = null): Collection
    {
        $recentIds = $this->model
            ->newQuery()
            ->latest('created_at')
            ->latest('id')
            ->limit(self::RECENT_TOPICS_LIMIT)
            ->pluck('id');

        return $this->model
            ->newQuery()
            ->when(
                $viewerId !== null,
                fn ($query) => $query->where(function ($query) use ($recentIds, $viewerId) {
                    $query->whereIn('id', $recentIds)
                        ->orWhere('user_id', $viewerId);
                }),
                fn ($query) => $query->whereIn('id', $recentIds),
            )
            ->latest('created_at')
            ->latest('id')
            ->get();
    }

    public function createForUser(int $userId, array $data): CustomTopic
    {
        $data['user_id'] = $userId;

        return $this->model
            ->newQuery()
            ->create($data);
    }

    public function delete(CustomTopic $customTopic): bool
    {
        return (bool) $customTopic->delete();
    }
}
