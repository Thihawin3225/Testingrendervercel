<?php

namespace App\Repositories\Contracts\Marketplace;


use App\Models\ListingRequest;
use Illuminate\Database\Eloquent\Collection;

interface ListingRequestRepositoryInterface
{
    public function findByListingAndRequester(
        int $listingId,
        int $requesterId
    ): ?ListingRequest;

    public function create(array $data): ListingRequest;

    public function update(
        ListingRequest $listingRequest,
        array $data
    ): ListingRequest;

    public function getMyRequests(int $requesterId): Collection;

    public function getListingRequests(int $listingId): Collection;

    public function findById(int $id): ?ListingRequest;
}
