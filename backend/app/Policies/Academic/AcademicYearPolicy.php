<?php

namespace App\Policies\Academic;

use App\Models\AcademicYear;
use App\Models\User;

class AcademicYearPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function view(User $user, AcademicYear $academicYear): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function update(User $user, AcademicYear $academicYear): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }

    public function delete(User $user, AcademicYear $academicYear): bool
    {
        return $user->role === 'admin'
            && $user->status === 'active';
    }
}
