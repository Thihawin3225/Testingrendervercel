<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description'])]
class MarketplaceCategory extends Model
{
    public function marketplaceListings(): HasMany
    {
        return $this->hasMany(MarketplaceListing::class, 'category_id');
    }
}
