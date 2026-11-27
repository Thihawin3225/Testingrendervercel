<?php

namespace App\Services\StudyMaterial;

use App\Models\StudyMaterial;
use App\Models\User;
use App\Repositories\Contracts\StudyMaterial\StudyMaterialRepositoryInterface;
use App\Services\Storage\CloudinaryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class StudyMaterialService
{
    public function __construct(
        protected StudyMaterialRepositoryInterface $repository,
        protected CloudinaryService $cloudinary
    ) {}

    public function upload(User $uploader, array $data): StudyMaterial
    {
        /** @var UploadedFile $file */
        $file = $data['file'];
        $fileHash = (string) hash_file('sha256', $file->getRealPath());

        $this->ensureFileHashIsUnique($fileHash);

        $asset = $this->cloudinary->uploadRawFile($file);

        try {
            return DB::transaction(fn () => $this->repository->create([
                'user_id' => $uploader->getAuthIdentifier(),
                'subject_offering_id' => $data['subject_offering_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'original_filename' => $this->safeOriginalFilename($file),
                'cloudinary_public_id' => $asset['public_id'],
                'cloudinary_asset_id' => $asset['asset_id'],
                'cloudinary_resource_type' => $asset['resource_type'],
                'cloudinary_delivery_type' => $asset['delivery_type'],
                'file_type' => strtolower($file->getClientOriginalExtension()),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'file_size' => $file->getSize(),
                'file_hash' => $fileHash,
                'status' => 'active',
            ]));
        } catch (Throwable $exception) {
            if ($this->isDuplicateEntry($exception)) {
                throw $this->duplicateFileException();
            }

            try {
                $this->cloudinary->deleteRawFile($asset['public_id']);
            } catch (Throwable $cleanupException) {
                Log::error('Could not remove a Cloudinary study material after database persistence failed.', [
                    'cloudinary_public_id' => $asset['public_id'],
                    'exception' => $cleanupException->getMessage(),
                ]);
            }
            throw $exception;
        }
    }

    public function update(
        StudyMaterial $material,
        array $data
    ): StudyMaterial {
        /** @var UploadedFile|null $file */
        $file = $data['file'] ?? null;
        unset($data['file']);

        $newAsset = null;
        $fileHash = null;
        if ($file !== null) {
            $fileHash = (string) hash_file('sha256', $file->getRealPath());

            if ($fileHash === $material->file_hash) {
                $file = null;
            } elseif ($this->repository->existsByFileHash($fileHash)) {
                throw $this->duplicateFileException();
            }
        }

        if ($file !== null) {
            $newAsset = $this->cloudinary->uploadRawFile($file);
            $data = array_merge($data, [
                'original_filename' => $this->safeOriginalFilename($file),
                'cloudinary_public_id' => $newAsset['public_id'],
                'cloudinary_asset_id' => $newAsset['asset_id'],
                'cloudinary_resource_type' => $newAsset['resource_type'],
                'cloudinary_delivery_type' => $newAsset['delivery_type'],
                'file_type' => strtolower($file->getClientOriginalExtension()),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'file_size' => $file->getSize(),
                'file_hash' => $fileHash,
            ]);
        }

        try {
            $updated = DB::transaction(
                fn () => $this->repository->update($material, $data)
            );
        } catch (Throwable $exception) {
            if ($this->isDuplicateEntry($exception)) {
                throw $this->duplicateFileException();
            }

            if ($newAsset !== null) {
                $this->deleteUploadedAssetAfterFailure($newAsset['public_id']);
            }

            throw $exception;
        }

        if ($newAsset !== null && $material->cloudinary_public_id !== $newAsset['public_id']) {
            $this->deleteReplacedAsset($material->cloudinary_public_id);
        }

        return $updated;
    }

    public function delete(StudyMaterial $material): bool
    {
        $deleted = DB::transaction(
            fn () => $this->repository->delete($material)
        );

        if ($deleted) {
            $this->deleteRemovedAsset($material->cloudinary_public_id);
        }

        return $deleted;
    }

    public function bookmark(int $userId, int $materialId): void
    {
        $this->repository->bookmark($userId, $materialId);
    }

    public function removeBookmark(int $userId, int $materialId): void
    {
        $this->repository->removeBookmark($userId, $materialId);
    }

    public function downloadUrl(StudyMaterial $material): string
    {
        return $this->cloudinary->privateDownloadUrl(
            $material->cloudinary_public_id,
            $material->file_type
        );
    }

    public function previewUrl(StudyMaterial $material): string
    {
        return $this->cloudinary->privatePreviewUrl(
            $material->cloudinary_public_id,
            $material->file_type
        );
    }

    public function browse(
        array $filters = [],
        ?int $perPage = null,
        ?int $viewerId = null
    ): LengthAwarePaginator {
        return $this->repository->paginateActive($filters, $perPage, $viewerId);
    }

    public function getUploads(
        int $userId,
        array $filters = [],
        ?int $perPage = null
    ): LengthAwarePaginator {
        return $this->repository->paginateForUser($userId, $filters, $perPage);
    }

    public function getBookmarkedMaterials(
        int $userId,
        array $filters = [],
        ?int $perPage = null
    ): LengthAwarePaginator {
        return $this->repository->paginateBookmarkedByUser(
            $userId,
            $filters,
            $perPage
        );
    }

    private function ensureFileHashIsUnique(string $fileHash): void
    {
        if ($this->repository->existsByFileHash($fileHash)) {
            throw $this->duplicateFileException();
        }
    }

    private function duplicateFileException(): ValidationException
    {
        return ValidationException::withMessages([
            'file' => 'This study material has already been uploaded.',
        ]);
    }

    private function isDuplicateEntry(Throwable $exception): bool
    {
        return $exception instanceof QueryException
            && (int) ($exception->errorInfo[1] ?? 0) === 1062;
    }

    private function safeOriginalFilename(UploadedFile $file): string
    {
        $filename = basename(str_replace('\\', '/', $file->getClientOriginalName()));

        return mb_substr($filename, 0, 255);
    }

    private function deleteUploadedAssetAfterFailure(string $publicId): void
    {
        try {
            $this->cloudinary->deleteRawFile($publicId);
        } catch (Throwable $cleanupException) {
            Log::error('Could not remove a replacement study material after database persistence failed.', [
                'cloudinary_public_id' => $publicId,
                'exception' => $cleanupException->getMessage(),
            ]);
        }
    }

    private function deleteReplacedAsset(?string $publicId): void
    {
        if ($publicId === null) {
            return;
        }

        try {
            $this->cloudinary->deleteRawFile($publicId);
        } catch (Throwable $cleanupException) {
            Log::error('Could not remove the replaced study material asset.', [
                'cloudinary_public_id' => $publicId,
                'exception' => $cleanupException->getMessage(),
            ]);
        }
    }

    private function deleteRemovedAsset(?string $publicId): void
    {
        if ($publicId === null) {
            return;
        }

        try {
            $this->cloudinary->deleteRawFile($publicId);
        } catch (Throwable $cleanupException) {
            Log::error('Could not remove the deleted study material asset.', [
                'cloudinary_public_id' => $publicId,
                'exception' => $cleanupException->getMessage(),
            ]);
        }
    }
}
