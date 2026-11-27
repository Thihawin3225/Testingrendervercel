<?php

namespace App\Http\Resources\PeerLearning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomTopicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'is_mine' => $request->user() !== null
                && (int) $this->user_id === (int) $request->user()->getAuthIdentifier(),
        ];
    }
}
