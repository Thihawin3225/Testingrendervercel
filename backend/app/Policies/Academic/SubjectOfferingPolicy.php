<?php

namespace App\Policies\Academic;

use App\Models\SubjectOffering;
use App\Models\User;

class SubjectOfferingPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, SubjectOffering $subjectOffering): bool
    {
        return $this->canView($user);
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function update(User $user, SubjectOffering $subjectOffering): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function delete(User $user, SubjectOffering $subjectOffering): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active'
            && ! $subjectOffering->studyMaterials()->exists()
            && ! $subjectOffering->peerLearningRequests()->exists();
    }

    private function canView(User $user): bool
    {
        return in_array($user->role, ['admin', 'student'], true)
            && $user->status === 'active';
    }
}
