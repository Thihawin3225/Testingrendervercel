<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CampusLocation;

class CampusLocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'student';
    }

    public function view(User $user, CampusLocation $location): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, CampusLocation $location): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, CampusLocation $location): bool
    {
        return $user->role === 'admin';
    }
}
