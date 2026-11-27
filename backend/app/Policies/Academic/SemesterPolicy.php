<?php

namespace App\Policies\Academic;

use App\Models\Semester;
use App\Models\User;

class SemesterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function view(User $user, Semester $semester): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function update(User $user, Semester $semester): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function delete(User $user, Semester $semester): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }
}
