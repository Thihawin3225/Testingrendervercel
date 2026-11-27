<?php

namespace App\Policies\Academic;

use App\Models\YearLevel;
use App\Models\User;

class YearLevelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function view(User $user, YearLevel $yearLevel): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function update(User $user, YearLevel $yearLevel): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function delete(User $user, YearLevel $yearLevel): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }
}
