<?php

namespace App\Policies\Academic;

use App\Models\Major;
use App\Models\User;

class MajorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function view(User $user, Major $major): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function update(User $user, Major $major): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function delete(User $user, Major $major): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }
}
