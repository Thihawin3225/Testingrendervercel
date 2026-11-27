<?php

namespace App\Http\Resources\Marketplace;



use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'requester' => [
                'id' => $this->requester?->id,
                'name' => $this->requester?->name,
            ],
            'message' => $this->message,
            'status' => $this->status,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'listing' => [
                'id' => $this->listing?->id,
                'title' => $this->listing?->title,
                'price' => $this->listing?->price,
                'listing_type' => $this->listing?->listing_type,
                'status' => $this->listing?->status,
            ],

            'seller_phone' => $this->seller_phone,
            'seller_message' => $this->seller_message,
            'meeting_at' => $this->meeting_at?->toISOString(),
            'confirmation' => (bool) $this->confirmation,

            'campus_location' => $this->campusLocation ? [
                'id' => $this->campusLocation->id,
                'name' => $this->campusLocation->name,
                'building' => $this->campusLocation->building,
                'display_name' => $this->campusLocation->display_name,
            ] : null,
        ];
    }
}
