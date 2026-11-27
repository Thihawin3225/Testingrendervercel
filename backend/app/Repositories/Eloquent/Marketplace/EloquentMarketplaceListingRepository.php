<?php

namespace App\Repositories\Eloquent\Marketplace;

use App\Models\MarketplaceListing;
use App\Repositories\Contracts\Marketplace\MarketplaceListingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentMarketplaceListingRepository implements MarketplaceListingRepositoryInterface
{
    public function paginate(
        array $filters = [],
        int $perPage = 12
    ): LengthAwarePaginator {
        $query = MarketplaceListing::query()
            ->with([
                'user:id,name',
                'category:id,name',
                'images',
            ])
            ->latest();

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['listing_type'])) {
            $query->where('listing_type', $filters['listing_type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?MarketplaceListing
    {
        return MarketplaceListing::with([
            'user:id,name',
            'category:id,name',
            'images',
        ])->find($id);
    }

    public function create(array $data): MarketplaceListing
    {
        return MarketplaceListing::create($data);
    }

    public function update(
        MarketplaceListing $listing,
        array $data
    ): MarketplaceListing {
        $listing->update($data);

        return $listing->fresh([
            'user:id,name',
            'category:id,name',
            'images',
        ]);
    }

    public function delete(MarketplaceListing $listing): bool
    {
        return (bool) $listing->delete();
    }
}
