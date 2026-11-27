<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'listing_id',
    'requester_id',
    'message',
    'status',
    'seller_phone',
    'campus_location_id',
    'seller_message',
    'meeting_at',
    'confirmation',
])]
class ListingRequest extends Model
{
    protected function casts(): array
    {
        return [
            'meeting_at' => 'datetime',
            'confirmation' => 'boolean',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(
            MarketplaceListing::class,
            'listing_id'
        );
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'requester_id'
        );
    }

    public function campusLocation(): BelongsTo
    {
        return $this->belongsTo(
            CampusLocation::class,
            'campus_location_id'
        );
    }
}
