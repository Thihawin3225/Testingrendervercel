<?php

namespace App\Policies\Academic;

use App\Models\StudentAcademicEnrollment;
use App\Models\User;

class StudentAcademicEnrollmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function view(
        User $user,
        StudentAcademicEnrollment $enrollment
    ): bool {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function update(
        User $user,
        StudentAcademicEnrollment $enrollment
    ): bool {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function delete(
        User $user,
        StudentAcademicEnrollment $enrollment
    ): bool {
        return $user->role === 'admin'
            && $user->status === 'active';
    }
}
