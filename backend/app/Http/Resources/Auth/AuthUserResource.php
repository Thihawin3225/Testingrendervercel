<?php

namespace App\Http\Resources\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'student_number' => $this->studentProfile?->student_number ?? null,
            'role' => $this->role,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'avatar_url' => $this->whenLoaded(
                'studentProfile',
                fn () => $this->studentProfile?->avatar_url,
            ),
            'bio' => $this->whenLoaded(
                'studentProfile',
                fn () => $this->studentProfile?->bio,
            ),
        ];
    }
}
