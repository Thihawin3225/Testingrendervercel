<?php

namespace App\Services\Marketplace;


use App\Models\MarketplaceListing;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MarketplaceListingSaveService
{
    /**
     * Save a marketplace listing for the authenticated user.
     */
    public function save(User $user, MarketplaceListing $listing): bool
    {
        $alreadySaved = $user->savedMarketplaceListings()
            ->whereKey($listing->id)
            ->exists();

        if ($alreadySaved) {
            return false;
        }

        $user->savedMarketplaceListings()->attach($listing->id);

        return true;
    }

    /**
     * Remove a saved marketplace listing.
     */
    public function unsave(User $user, MarketplaceListing $listing): bool
    {
        $deleted = $user->savedMarketplaceListings()
            ->detach($listing->id);

        return $deleted > 0;
    }

    /**
     * Get listings saved by the authenticated user.
     */
    public function getSavedListings(User $user): LengthAwarePaginator
    {
        return $user->savedMarketplaceListings()
            ->with([
                'user',
                'category',
                'images',
            ])
            ->orderByPivot('created_at', 'desc')
            ->paginate(12);
    }
}
