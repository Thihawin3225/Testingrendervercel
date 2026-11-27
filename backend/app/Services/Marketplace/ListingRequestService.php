<?php

namespace App\Services\Marketplace;




use App\Models\ListingRequest;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Repositories\Contracts\Marketplace\ListingRequestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class ListingRequestService
{
    public function __construct(
        protected ListingRequestRepositoryInterface $listingRequestRepository
    ) {}

    /**
     * Buyer sends a request for a listing.
     */
    public function createRequest(
        User $requester,
        MarketplaceListing $listing,
        array $data
    ): ListingRequest {
        // Prevent users from requesting their own listing.
        if ($listing->user_id === $requester->id) {
            throw ValidationException::withMessages([
                'listing' => 'You cannot send a request for your own listing.',
            ]);
        }
        if (! in_array($listing->status, ['pending', 'active'], true)) {
            throw ValidationException::withMessages([
                'listing' => 'This listing is no longer available for requests.',
            ]);
        }

        $existingRequest = $this->listingRequestRepository
            ->findByListingAndRequester(
                $listing->id,
                $requester->id
            );

        // Prevent duplicate pending or accepted requests.
        if (
            $existingRequest &&
            in_array($existingRequest->status, ['pending', 'accepted'], true)
        ) {
            throw ValidationException::withMessages([
                'request' => 'You have already submitted a request for this listing.',
            ]);
        }

        // Reuse rejected or cancelled request.
        if ($existingRequest) {
            return $this->listingRequestRepository->update(
                $existingRequest,
                [
                    'message' => $data['message'] ?? null,
                    'status' => 'pending',
                ]
            );
        }
        if (! in_array($listing->status, ['pending', 'active'], true)) {
            throw ValidationException::withMessages([
                'listing' => 'This listing is not available for scheduling.',
            ]);
        }

        return $this->listingRequestRepository->create([
            'listing_id' => $listing->id,
            'requester_id' => $requester->id,
            'message' => $data['message'] ?? null,
            'status' => 'pending',
        ]);
    }

    /**
     * Get requests submitted by the authenticated buyer.
     */
    public function getMyRequests(User $requester): Collection
    {
        return $this->listingRequestRepository
            ->getMyRequests($requester->id);
    }


    public function releaseSchedule(
        User $seller,
        MarketplaceListing $listing
    ): ListingRequest {
        return DB::transaction(function () use ($seller, $listing) {

            // Lock listing to prevent concurrent operations.
            $lockedListing = MarketplaceListing::query()
                ->lockForUpdate()
                ->findOrFail($listing->id);

            // Check listing owner.
            if ($lockedListing->user_id !== $seller->id) {
                throw ValidationException::withMessages([
                    'listing' => 'You are not allowed to release this schedule.',
                ]);
            }

            // Listing must be reserved.
            if ($lockedListing->status !== 'reserved') {
                throw ValidationException::withMessages([
                    'listing' => 'Only reserved listings can release a schedule.',
                ]);
            }

            // Find the accepted request.
            $acceptedRequest = ListingRequest::query()
                ->where('listing_id', $lockedListing->id)
                ->where('status', 'accepted')
                ->lockForUpdate()
                ->first();

            if (! $acceptedRequest) {
                throw ValidationException::withMessages([
                    'request' => 'No accepted request was found for this listing.',
                ]);
            }

            // Reject the previously accepted request.
            $acceptedRequest->update([
                'status' => 'rejected',
            ]);

            // Reopen listing for other buyers.
            $lockedListing->update([
                'status' => 'pending',
            ]);

            return $acceptedRequest->fresh([
                'listing',
                'requester',
                'campusLocation',
            ]);
        });
    }
    public function completeListing(
        User $seller,
        MarketplaceListing $listing
    ): MarketplaceListing {
        return DB::transaction(function () use ($seller, $listing) {

            // Lock listing.
            $lockedListing = MarketplaceListing::query()
                ->lockForUpdate()
                ->findOrFail($listing->id);

            // Check ownership.
            if ($lockedListing->user_id !== $seller->id) {
                throw ValidationException::withMessages([
                    'listing' => 'You are not allowed to complete this listing.',
                ]);
            }

            // Listing must be reserved.
            if ($lockedListing->status !== 'reserved') {
                throw ValidationException::withMessages([
                    'listing' => 'Only reserved listings can be completed.',
                ]);
            }

            // Ensure an accepted buyer exists.
            $acceptedRequest = ListingRequest::query()
                ->where('listing_id', $lockedListing->id)
                ->where('status', 'accepted')
                ->lockForUpdate()
                ->first();

            if (! $acceptedRequest) {
                throw ValidationException::withMessages([
                    'request' => 'No accepted buyer was found for this listing.',
                ]);
            }

            // Mark listing as sold.
            $lockedListing->update([
                'status' => 'sold',
            ]);

            // Reject all other pending requests.
            ListingRequest::query()
                ->where('listing_id', $lockedListing->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                ]);

            return $lockedListing->fresh([
                'user',
                'category',
                'listingRequests.requester',
            ]);
        });
    }



    /**
     * Buyer confirms that they accept the updated listing.
     */
    public function confirmRequest(
        User $buyer,
        ListingRequest $listingRequest
    ): ListingRequest {
        return DB::transaction(function () use ($buyer, $listingRequest) {

            $listing = MarketplaceListing::query()
                ->whereKey($listingRequest->listing_id)
                ->lockForUpdate()
                ->firstOrFail();

            $request = ListingRequest::query()
                ->whereKey($listingRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->requester_id !== $buyer->id) {
                throw ValidationException::withMessages([
                    'request' => 'You are not allowed to confirm this request.',
                ]);
            }

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Only pending requests can be confirmed.',
                ]);
            }
            // Prevent unnecessary repeated confirmation.
            if ($request->confirmation) {
                return $request->fresh([
                    'listing',
                    'requester',
                    'campusLocation',
                ]);
            }
            $request->update([
                'confirmation' => true,
            ]);

            return $request->fresh([
                'listing',
                'requester',
                'campusLocation',
            ]);
        });
    }

    /**
     * Get requests received for a listing.
     * Only the listing owner can view these requests.
     */
    public function getListingRequests(
        User $seller,
        MarketplaceListing $listing
    ): Collection {
        if ($listing->user_id !== $seller->id) {
            throw ValidationException::withMessages([
                'listing' => 'You are not allowed to view these requests.',
            ]);
        }

        return $this->listingRequestRepository
            ->getListingRequests($listing->id);
    }

    /**
     * Seller accepts or rejects a request.
     */
    public function updateRequestStatus(
        User $seller,
        ListingRequest $listingRequest,
        string $status
    ): ListingRequest {
        $listingRequest->loadMissing('listing');

        // Only the listing owner can update the request.
        if ($listingRequest->listing->user_id !== $seller->id) {
            throw ValidationException::withMessages([
                'request' => 'You are not allowed to update this request.',
            ]);
        }

        // Only pending requests can be updated.
        if ($listingRequest->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'Only pending requests can be updated.',
            ]);
        }
        if ($status !== 'rejected') {
            throw ValidationException::withMessages([
                'status' => 'Use the schedule form to accept a request.',
            ]);
        }
        return $this->listingRequestRepository->update(
            $listingRequest,
            ['status' => $status]
        );
    }

    /**
     * Buyer cancels their own pending request.
     */
    public function cancelRequest(
        User $requester,
        ListingRequest $listingRequest
    ): ListingRequest {
        if ($listingRequest->requester_id !== $requester->id) {
            throw ValidationException::withMessages([
                'request' => 'You are not allowed to cancel this request.',
            ]);
        }

        if ($listingRequest->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'Only pending requests can be cancelled.',
            ]);
        }

        return $this->listingRequestRepository->update(
            $listingRequest,
            ['status' => 'cancelled']
        );
    }

    public function acceptRequest(
        User $seller,
        ListingRequest $listingRequest,
        array $data
    ): ListingRequest {
        return DB::transaction(function () use ($seller, $listingRequest, $data) {

            // 1. Lock the listing row first.
            $listing = MarketplaceListing::query()
                ->whereKey($listingRequest->listing_id)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Check listing ownership.
            if ($listing->user_id !== $seller->id) {
                throw ValidationException::withMessages([
                    'listing' => 'You are not allowed to accept requests for this listing.',
                ]);
            }

            // 3. Only active listings can be scheduled.
            if (! in_array($listing->status, ['pending', 'active'], true)) {
                throw ValidationException::withMessages([
                    'listing' => 'This listing is not available for scheduling.',
                ]);
            }

            // 4. Lock and retrieve the selected request.
            $request = ListingRequest::query()
                ->whereKey($listingRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 5. Only pending requests can be accepted.
            if ($request->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Only pending requests can be accepted.',
                ]);
            }

            // 6. Buyer must confirm the updated listing.
            if (! $request->confirmation) {
                throw ValidationException::withMessages([
                    'confirmation' => [
                        'Buyer must confirm the updated listing before scheduling.',
                    ],
                ]);
            }

            // 7. Save schedule details and accept the request.
            $request->update([
                'seller_phone' => $data['seller_phone'],
                'campus_location_id' => $data['campus_location_id'],
                'seller_message' => $data['seller_message'] ?? null,
                'meeting_at' => $data['meeting_at'],
                'status' => 'accepted',
            ]);

            // 8. Mark the listing as reserved.
            $listing->update([
                'status' => 'reserved',
            ]);

            // 9. Return the request with required relationships.
            return $request->fresh([
                'listing',
                'requester',
                'campusLocation',
            ]);
        });
    }
}
