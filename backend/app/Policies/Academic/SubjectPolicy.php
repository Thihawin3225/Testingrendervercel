<?php

namespace App\Policies\Academic;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function view(User $user, Subject $subject): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function update(User $user, Subject $subject): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function delete(User $user, Subject $subject): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active'
            && !$subject->subjectOfferings()->exists();
    }
}