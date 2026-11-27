<?php

namespace App\Repositories\Contracts\PeerLearning;

use App\Models\CustomTopic;
use Illuminate\Database\Eloquent\Collection;

interface CustomTopicRepositoryInterface
{
    public function getRecentShared(?int $viewerId = null): Collection;

    public function createForUser(int $userId, array $data): CustomTopic;

    public function delete(CustomTopic $customTopic): bool;
}
