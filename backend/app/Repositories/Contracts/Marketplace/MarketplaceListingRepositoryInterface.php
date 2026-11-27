<?php

namespace App\Repositories\Contracts\Marketplace;

use App\Models\MarketplaceListing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MarketplaceListingRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function findById(int $id): ?MarketplaceListing;
    public function create(array $data): MarketplaceListing;


    public function update(
        MarketplaceListing $listing,
        array $data
    ): MarketplaceListing;

    public function delete(MarketplaceListing $listing): bool;
}
