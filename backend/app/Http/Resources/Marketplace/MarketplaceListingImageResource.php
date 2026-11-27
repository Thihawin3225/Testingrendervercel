<?php

namespace App\Http\Resources\Marketplace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketplaceListingImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'cloudinary_public_id' => $this->cloudinary_public_id,
            'cloudinary_asset_id' => $this->cloudinary_asset_id,
            'resource_type' => $this->cloudinary_resource_type,
            'delivery_type' => $this->cloudinary_delivery_type,
            'sort_order' => $this->sort_order,
        ];
    }
}
