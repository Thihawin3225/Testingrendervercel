<?php

namespace App\Services\Marketplace;

use App\Models\MarketplaceListing;
use App\Models\MarketplaceListingImage;
use App\Repositories\Contracts\Marketplace\MarketplaceListingRepositoryInterface;
use App\Services\Storage\CloudinaryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\ListingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class MarketplaceListingService
{
    public function __construct(
        protected MarketplaceListingRepositoryInterface $listingRepository,
        protected CloudinaryService $cloudinaryService
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->listingRepository->paginate($filters);
    }

    public function getById(int $id): ?MarketplaceListing
    {
        return $this->listingRepository->findById($id);
    }

    public function create(array $data, int $userId): MarketplaceListing
    {
        $uploadedPublicIds = [];

        try {
            return DB::transaction(function () use (
                $data,
                $userId,
                &$uploadedPublicIds
            ) {
                $images = $data['images'];

                unset($data['images']);

                $data['user_id'] = $userId;
                $data['status'] = 'pending';

                $this->validatePrice($data['listing_type'], $data['price'] ?? null);

                $listing = $this->listingRepository->create($data);

                foreach ($images as $index => $file) {
                    $upload = $this->cloudinaryService->uploadImage(
                        $file,
                        'campus_bridge/marketplace'
                    );

                    $uploadedPublicIds[] = $upload['public_id'];

                    $listing->images()->create([
                        'cloudinary_public_id' => $upload['public_id'],
                        'cloudinary_asset_id' => $upload['asset_id'],
                        'cloudinary_resource_type' => $upload['resource_type'],
                        'url' => $upload['secure_url'],
                        'cloudinary_delivery_type' => $upload['delivery_type'],
                        'sort_order' => $index + 1,
                    ]);
                }

                return $listing->load([
                    'user:id,name',
                    'category:id,name',
                    'images',
                ]);
            });
        } catch (Throwable $exception) {
            foreach ($uploadedPublicIds as $publicId) {
                try {
                    $this->cloudinaryService->deleteImage($publicId);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }
    }


    public function update(
        MarketplaceListing $listing,
        array $data
    ): MarketplaceListing {

        $uploadedPublicIds = [];
        $removedPublicIds = [];

        try {
            $updatedListing = DB::transaction(function () use (
                $listing,
                $data,
                &$uploadedPublicIds,
                &$removedPublicIds
            ) {

                // 1. Lock listing.
                $lockedListing = MarketplaceListing::query()
                    ->whereKey($listing->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 2. Separate image data.
                $newImages = $data['images'] ?? [];
                $removedImageIds = $data['removed_image_ids'] ?? [];

                unset(
                    $data['images'],
                    $data['removed_image_ids']
                );

                // 3. Validate effective listing type and price.
                $listingType = $data['listing_type']
                    ?? $lockedListing->listing_type;

                $price = array_key_exists('price', $data)
                    ? $data['price']
                    : $lockedListing->price;

                $this->validatePrice($listingType, $price);

                // 4. Get current images.
                $currentImages = $lockedListing->images()->get();

                // 5. Verify removed images belong to this listing.
                $imagesToRemove = $lockedListing->images()
                    ->whereIn('id', $removedImageIds)
                    ->get();

                if ($imagesToRemove->count() !== count($removedImageIds)) {
                    throw ValidationException::withMessages([
                        'removed_image_ids' => [
                            'One or more selected images do not belong to this listing.',
                        ],
                    ]);
                }

                // 6. Check total image limit.
                $remainingImageCount = $currentImages->count()
                    - $imagesToRemove->count();

                $totalImageCount = $remainingImageCount
                    + count($newImages);

                if ($totalImageCount > 5) {
                    throw ValidationException::withMessages([
                        'images' => [
                            'A listing can have a maximum of 5 images.',
                        ],
                    ]);
                }

                // 7. Reset listing requests when listing is updated.
                $hasRequests = ListingRequest::query()
                    ->where('listing_id', $lockedListing->id)
                    ->whereIn('status', ['pending', 'accepted'])
                    ->exists();

                if ($hasRequests) {

                    ListingRequest::query()
                        ->where('listing_id', $lockedListing->id)
                        ->where('status', 'accepted')
                        ->update([
                            'status' => 'pending',
                            'confirmation' => false,
                            'seller_phone' => null,
                            'campus_location_id' => null,
                            'seller_message' => null,
                            'meeting_at' => null,
                        ]);

                    ListingRequest::query()
                        ->where('listing_id', $lockedListing->id)
                        ->where('status', 'pending')
                        ->update([
                            'confirmation' => false,
                        ]);

                    if ($lockedListing->status === 'reserved') {
                        $lockedListing->update([
                            'status' => 'pending',
                        ]);
                    }
                }

                // 8. Update listing details.
                $lockedListing->update($data);

                // 9. Delete only selected image records.
                foreach ($imagesToRemove as $image) {

                    $removedPublicIds[] = $image->cloudinary_public_id;

                    $image->delete();
                }

                // 10. Upload new images without deleting existing images.
                foreach ($newImages as $file) {

                    $uploaded = $this->cloudinaryService->uploadImage(
                        $file,
                        'campus_bridge/marketplace'
                    );

                    $uploadedPublicIds[] = $uploaded['public_id'];

                    $lockedListing->images()->create([
                        'cloudinary_public_id' => $uploaded['public_id'],
                        'cloudinary_asset_id' => $uploaded['asset_id'],
                        'url' => $uploaded['secure_url'],
                        'cloudinary_resource_type' => $uploaded['resource_type'],
                        'cloudinary_delivery_type' => $uploaded['delivery_type'],
                        'sort_order' => 1,
                    ]);
                }

                // 11. Reorder remaining images.
                $remainingImages = $lockedListing->images()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();

                foreach ($remainingImages as $index => $image) {
                    $image->update([
                        'sort_order' => $index + 1,
                    ]);
                }

                // 12. Return updated listing.
                return $lockedListing->fresh([
                    'user',
                    'category',
                    'images',
                ]);
            });

            // 13. Delete removed images from Cloudinary
            // only after the database transaction succeeds.
            foreach ($removedPublicIds as $publicId) {
                try {
                    $this->cloudinaryService->deleteImage($publicId);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }

            return $updatedListing;
        } catch (Throwable $exception) {

            // If database transaction fails,
            // clean up newly uploaded Cloudinary images.
            foreach ($uploadedPublicIds as $publicId) {
                try {
                    $this->cloudinaryService->deleteImage($publicId);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }
    }


    public function delete(MarketplaceListing $listing): bool
    {
        $images = $listing->images()->get();

        $deleted = DB::transaction(function () use ($listing) {
            $listing->images()->delete();

            return $this->listingRepository->delete($listing);
        });

        if ($deleted) {
            foreach ($images as $image) {
                try {
                    $this->cloudinaryService->deleteImage(
                        $image->cloudinary_public_id
                    );
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        }

        return $deleted;
    }

    private function validatePrice(string $listingType, mixed $price): void
    {
        if ($listingType === 'sale' && ($price === null || !is_numeric($price) || $price < 0)) {
            throw ValidationException::withMessages([
                'price' => [
                    'Price is required for sale listings and must be at least 0.',
                ],
            ]);
        }

        if (in_array($listingType, ['donation', 'wanted'], true) && $price !== null) {
            throw ValidationException::withMessages([
                'price' => [
                    'Price must be null for donation and wanted listings.',
                ],
            ]);
        }
    }
}
