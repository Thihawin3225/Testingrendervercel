<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Requests\Admin\CreateStudentRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\Auth\AuthUserResource;
use App\Models\User;
use App\Services\Admin\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct(
        private UserManagementService $userService
    ) {}

    public function store(CreateStudentRequest $request): JsonResponse
    {
        $student = $this->userService->createStudent(
            $request->validated()
        );

        return response()->json([
            'message' => 'Student created successfully.',
            'user' => new AuthUserResource($student),
        ], 201);
    }

    public function update(
        UpdateUserRequest $request,
        User $user
    ): JsonResponse {
        abort_unless($user->role === 'student', 404);

        $updatedUser = $this->userService->update(
            $user,
            $request->validated()
        );

        return response()->json([
            'message' => 'User updated successfully',
            'user' => new AuthUserResource($updatedUser),
        ], 200);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_unless($user->role === 'student', 404);

        $this->userService->delete($user);

        return response()->json([
            'message' => 'User deleted successfully.',
        ], 200);
    }

    public function index(): AnonymousResourceCollection
    {
        $students = $this->userService->getAllStudents();

        return AuthUserResource::collection($students);
    }
}