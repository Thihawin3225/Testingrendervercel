<?php

namespace App\Http\Resources\Marketplace;



use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketplaceListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'listing_type' => $this->listing_type,
            'price' => $this->price,
            'condition' => $this->condition,
            'status' => $this->status,

            'user' => [
                'id' => $this->whenLoaded('user', fn() => $this->user?->id),
                'name' => $this->whenLoaded('user', fn() => $this->user?->name),
            ],

            'category' => [
                'id' => $this->whenLoaded('category', fn() => $this->category?->id),
                'name' => $this->whenLoaded('category', fn() => $this->category?->name),
            ],

            'images' => MarketplaceListingImageResource::collection(
                $this->whenLoaded('images')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
