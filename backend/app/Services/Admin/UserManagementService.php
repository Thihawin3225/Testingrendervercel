<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Services\Academic\StudentAcademicEnrollmentService;

class UserManagementService
{
    public function createStudent(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $student = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'student',
                'status' => 'active',
            ]);

            $student->studentProfile()->create([
                'student_number' => $data['student_number'],
            ]);

            return $student->load('studentProfile');
        });
    }

    public function createStudentWithEnrollment(array $data): array
    {
        return DB::transaction(function () use ($data): array {

            // 1. Create student
            $student = $this->createStudent([
                'name' => $data['name'],
                'email' => $data['email'],
                'student_number' => $data['student_number'],
                'password' => $data['password'],
            ]);

            // 2. Create enrollment using the new student's ID
            $enrollment = $this->enrollmentService->create([
                'user_id' => $student->id,
                'academic_year_id' => $data['academic_year_id'],
                'year_level_id' => $data['year_level_id'],
                'major_id' => $data['major_id'],
                'semester_id' => $data['semester_id'],
                'status' => $data['status'] ?? 'active',
            ]);

            // 3. Return both records
            return [
                'student' => $student->load('studentProfile'),
                'enrollment' => $enrollment,
            ];
        });
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        if (isset($data['password'])) {
            $user->password = $data['password'];
        }

        if (isset($data['status'])) {
            $user->status = $data['status'];
        }

        $user->save();

        if ($user->status !== 'active') {
            $user->tokens()->delete();
        }

        return $user->refresh();
    }

    public function delete(User $user): void
    {
        // Revoke all tokens belonging to this user
        $user->tokens()->delete();

        // Soft delete the user
        $user->delete();
    }

    public function getAllStudents(): LengthAwarePaginator
    {
        return User::query()
            ->with('studentProfile:id,user_id,student_number')
            ->where('role', 'student')
            ->latest()
            ->paginate(15);
    }

    public function __construct(
        protected StudentAcademicEnrollmentService $enrollmentService
    ) {}
}
