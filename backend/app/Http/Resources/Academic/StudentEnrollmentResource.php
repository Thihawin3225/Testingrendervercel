<?php

namespace App\Http\Resources\Academic;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentEnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'user' => [
                'id' => $this->whenLoaded('user', fn () => $this->user?->id),
                'name' => $this->whenLoaded('user', fn () => $this->user?->name),
                'email' => $this->whenLoaded('user', fn () => $this->user?->email),
            ],

            'academic_year' => [
                'id' => $this->whenLoaded('academicYear', fn () => $this->academicYear?->id),
                'name' => $this->whenLoaded('academicYear', fn () => $this->academicYear?->name),
            ],

            'year_level' => [
                'id' => $this->whenLoaded('yearLevel', fn () => $this->yearLevel?->id),
                'name' => $this->whenLoaded('yearLevel', fn () => $this->yearLevel?->name),
            ],

            'major' => [
                'id' => $this->whenLoaded('major', fn () => $this->major?->id),
                'name' => $this->whenLoaded('major', fn () => $this->major?->name),
            ],

            'semester' => [
                'id' => $this->whenLoaded('semester', fn () => $this->semester?->id),
                'name' => $this->whenLoaded('semester', fn () => $this->semester?->name),
            ],

            'status' => $this->status,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
