<?php

namespace App\Services\Storage;

use Cloudinary\Asset\File;
use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary;
    }

    /**
     * Upload one image to Cloudinary.
     */
    public function uploadImage(
        UploadedFile $file,
        string $folder = 'campus_bridge/student_avatars'
    ): array {
        $result = $this->cloudinary
            ->uploadApi()
            ->upload(
                $file->getRealPath(),
                [
                    'folder' => $folder,
                    'resource_type' => 'image',
                ]
            );

        return [
            'public_id' => $result['public_id'],
            'asset_id' => $result['asset_id'],
            'secure_url' => $result['secure_url'],
            'resource_type' => $result['resource_type'] ?? 'image',
            'delivery_type' => $result['type'] ?? 'upload',
        ];
    }

    /**
     * Upload a study document as an authenticated Cloudinary raw asset.
     *
     * @return array{public_id: string, asset_id: string, resource_type: string, delivery_type: string}
     */
    public function uploadRawFile(UploadedFile $file): array
    {
        $originalName = basename(str_replace('\\', '/', $file->getClientOriginalName()));

        $result = $this->cloudinary
            ->uploadApi()
            ->upload(
                $file->getRealPath(),
                [
                    'folder' => 'campus_bridge/study_materials',
                    'resource_type' => 'raw',
                    'type' => 'authenticated',
                    'use_filename' => true,
                    'unique_filename' => false,
                    'overwrite' => false,
                    'filename_override' => $originalName,
                ]
            );

        return [
            'public_id' => $result['public_id'],
            'asset_id' => $result['asset_id'],
            'resource_type' => 'raw',
            'delivery_type' => 'authenticated',
        ];
    }

    /**
     * Delete image from Cloudinary.
     */
    public function deleteImage(string $publicId): void
    {
        $this->cloudinary
            ->uploadApi()
            ->destroy($publicId, [
                'resource_type' => 'image',
                'invalidate' => true,
            ]);
    }

    public function deleteRawFile(string $publicId): void
    {
        $this->cloudinary
            ->uploadApi()
            ->destroy($publicId, [
                'resource_type' => 'raw',
                'type' => 'authenticated',
                'invalidate' => true,
            ]);
    }

    public function privateDownloadUrl(
        string $publicId,
        string $format
    ): string {
        return $this->cloudinary
            ->uploadApi()
            ->privateDownloadUrl($publicId, $format, [
                'resource_type' => 'raw',
                'type' => 'authenticated',
                'attachment' => true,
            ]);
    }

    public function privatePreviewUrl(
        string $publicId,
        string $format
    ): string {
        if (pathinfo($publicId, PATHINFO_EXTENSION) === '') {
            $publicId .= '.'.$format;
        }

        return (string) File::authenticated(
            $publicId,
            $this->cloudinary->configuration
        )
            ->signUrl()
            ->toUrl();
    }
}
