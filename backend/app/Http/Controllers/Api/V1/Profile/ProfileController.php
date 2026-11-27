<?php

namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Controller;

use App\Http\Requests\Profile\UploadAvatarRequest;
use App\Http\Requests\Profile\UpdateBioRequest;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as RoutingController;

class ProfileController extends RoutingController
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    public function uploadAvatar(
        UploadAvatarRequest $request
    ): JsonResponse {

        // Get the currently authenticated student.
        $user = $request->user();

        // Upload avatar through ProfileService.
        $result = $this->profileService->uploadAvatar(
            $user,
            $request->file('avatar')
        );

        // Return API response.
        return response()->json([
            'message' => 'Avatar uploaded successfully.',
            'data' => [
                'avatar_url' => $result['avatar_url'],
                'avatar_cloudinary_public_id' =>
                $result['student_profile']->avatar_cloudinary_public_id,
            ],
        ], 200);
    }

    public function updateBio(UpdateBioRequest $request): JsonResponse
    {
        $profile = $this->profileService->updateBio(
            $request->user(),
            $request->validated('bio'),
        );

        return response()->json([
            'message' => 'Profile bio updated successfully.',
            'data' => [
                'bio' => $profile->bio,
            ],
        ]);
    }
}
