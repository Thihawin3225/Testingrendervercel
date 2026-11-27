<?php

namespace App\Policies\PeerLearning;

use App\Models\PeerLearningRequest;
use App\Models\User;

class PeerLearningRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isActiveStudent($user);
    }

    public function viewFeed(User $user): bool
    {
        return $this->isActiveStudent($user);
    }

    public function view(User $user, PeerLearningRequest $peerLearningRequest): bool
    {
        return $this->isActiveStudent($user)
            && $peerLearningRequest->learner_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->isActiveStudent($user);
    }

    public function update(User $user, PeerLearningRequest $peerLearningRequest): bool
    {
        return $this->isActiveStudent($user)
            && $peerLearningRequest->learner_id === $user->id;
    }

    public function delete(User $user, PeerLearningRequest $peerLearningRequest): bool
    {
        return $this->isActiveStudent($user)
            && $peerLearningRequest->learner_id === $user->id;
    }

    private function isActiveStudent(User $user): bool
    {
        return $user->role === 'student'
            && $user->status === 'active';
    }
}
