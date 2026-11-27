<?php

namespace App\Repositories\Eloquent\Marketplace;

use App\Models\ListingRequest;
use App\Repositories\Contracts\Marketplace\ListingRequestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ListingRequestRepository implements ListingRequestRepositoryInterface
{
    public function findByListingAndRequester(
        int $listingId,
        int $requesterId
    ): ?ListingRequest {
        return ListingRequest::query()
            ->where('listing_id', $listingId)
            ->where('requester_id', $requesterId)
            ->first();
    }

    public function create(array $data): ListingRequest
    {
        return ListingRequest::create($data);
    }

    public function update(
        ListingRequest $listingRequest,
        array $data
    ): ListingRequest {
        $listingRequest->update($data);

        return $listingRequest->refresh();
    }

    public function getMyRequests(int $requesterId): Collection
    {
        return ListingRequest::query()
            ->with([
                'listing.user',
                'listing.category',
                'requester',
            ])
            ->where('requester_id', $requesterId)
            ->latest()
            ->get();
    }

    public function getListingRequests(int $listingId): Collection
    {
        return ListingRequest::query()
            ->with([
                'listing',
                'requester',
            ])
            ->where('listing_id', $listingId)
            ->latest()
            ->get();
    }

    public function findById(int $id): ?ListingRequest
    {
        return ListingRequest::query()
            ->with([
                'listing',
                'requester',
            ])
            ->find($id);
    }
}
