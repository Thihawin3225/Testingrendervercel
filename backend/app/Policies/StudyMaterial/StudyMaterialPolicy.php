<?php

namespace App\Policies\StudyMaterial;

use App\Models\StudyMaterial;
use App\Models\User;

class StudyMaterialPolicy
{
    public function create(User $user): bool
    {
        return $this->isActiveStudent($user);
    }

    public function viewAny(User $user): bool
    {
        return $this->isActiveStudent($user);
    }

    public function view(User $user, StudyMaterial $studyMaterial): bool
    {
        return $this->isActiveStudent($user)
            && (
                $studyMaterial->user_id === $user->id
                || $studyMaterial->status === 'active'
            );
    }

    public function update(User $user, StudyMaterial $studyMaterial): bool
    {
        return $this->isActiveStudent($user)
            && $studyMaterial->user_id === $user->id;
    }

    public function delete(User $user, StudyMaterial $studyMaterial): bool
    {
        return $this->isActiveStudent($user)
            && $studyMaterial->user_id === $user->id;
    }

    private function isActiveStudent(User $user): bool
    {
        return $user->role === 'student'
            && $user->status === 'active';
    }
}
