<?php

namespace App\Http\Resources\Academic;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectOfferingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'academic_year' => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
            ],

            'year_level' => [
                'id' => $this->yearLevel?->id,
                'name' => $this->yearLevel?->name,
            ],

            'major' => [
                'id' => $this->major?->id,
                'name' => $this->major?->name,
            ],

            'semester' => [
                'id' => $this->semester?->id,
                'name' => $this->semester?->name,
            ],

            'subject' => [
                'id' => $this->subject?->id,
                'subject_name' => $this->subject?->subject_name,
                'subject_code' => $this->subject?->subject_code,
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
