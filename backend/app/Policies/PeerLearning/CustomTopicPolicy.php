<?php

namespace App\Policies\PeerLearning;

use App\Models\CustomTopic;
use App\Models\User;

class CustomTopicPolicy
{
    public function delete(User $user, CustomTopic $customTopic): bool
    {
        return $this->isActiveStudent($user)
            && (int) $customTopic->user_id === (int) $user->id;
    }

    private function isActiveStudent(User $user): bool
    {
        return $user->role === 'student'
            && $user->status === 'active';
    }
}
