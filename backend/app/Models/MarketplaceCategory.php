<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceCategory extends Model
{
    protected $table = 'marketplace_categories';

    protected $fillable = [
        'name',
        'description',
    ];

    public function listings(): HasMany
    {
        return $this->hasMany(
            MarketplaceListing::class,
            'category_id'
        );
    }
}
