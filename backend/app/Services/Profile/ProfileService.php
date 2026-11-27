<?php

namespace App\Services\Profile;

use App\Models\StudentProfile;
use App\Models\User;
use App\Services\Storage\CloudinaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProfileService
{
    public function __construct(
        private CloudinaryService $cloudinaryService
    ) {}

    public function uploadAvatar(
        User $user,
        UploadedFile $file
    ): array {

        // 1. Find the student's profile.
        $profile = $user->studentProfile()->firstOrFail();

        // 2. Keep the old image public ID.
        $oldPublicId = $profile->avatar_cloudinary_public_id;

        // 3. Upload the new image to Cloudinary.
        $uploadedImage = $this->cloudinaryService
            ->uploadImage($file);

        try {

            // 4. Save new Cloudinary information in MySQL.
            $profile->avatar_url =
                $uploadedImage['secure_url'];

            $profile->avatar_cloudinary_public_id =
                $uploadedImage['public_id'];

            $profile->avatar_cloudinary_asset_id =
                $uploadedImage['asset_id'];

            $profile->saveOrFail();
        } catch (Throwable $exception) {

            // 5. Database update failed.
            // Delete the newly uploaded image.
            try {
                $this->cloudinaryService->deleteImage(
                    $uploadedImage['public_id']
                );
            } catch (Throwable $cleanupException) {
                Log::error('Failed to clean up new Cloudinary avatar.', [
                    'public_id' => $uploadedImage['public_id'],
                    'error' => $cleanupException->getMessage(),
                ]);
            }

            throw $exception;
        }

        // 6. Delete the old image after the new image is saved.
        if ($oldPublicId !== null) {
            try {
                $this->cloudinaryService->deleteImage($oldPublicId);
            } catch (Throwable $exception) {
                Log::warning('Failed to delete old Cloudinary avatar.', [
                    'public_id' => $oldPublicId,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        // 7. Return updated profile information.
        return [
            'student_profile' => $profile->refresh(),
            'avatar_url' => $uploadedImage['secure_url'],
        ];
    }

    public function updateBio(User $user, ?string $bio): StudentProfile
    {
        $profile = $user->studentProfile()->firstOrFail();
        $profile->bio = $bio;
        $profile->saveOrFail();

        return $profile->refresh();
    }
}
