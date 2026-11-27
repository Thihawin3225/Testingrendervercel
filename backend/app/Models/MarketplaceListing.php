<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'user_id',
    'category_id',
    'title',
    'description',
    'listing_type',
    'price',
    'condition',
    'status',
])]
class MarketplaceListing extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MarketplaceCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(MarketplaceListingImage::class, 'listing_id');
    }

    public function listingRequests(): HasMany
    {
        return $this->hasMany(ListingRequest::class, 'listing_id');
    }
    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'marketplace_listing_saves',
            'marketplace_listing_id',
            'user_id'
        )->withTimestamps();
    }
}
