<?php

namespace App\Http\Resources\StudyMaterial;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudyMaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_offering_id' => $this->subject_offering_id,
            'subject_offering' => $this->whenLoaded('subjectOffering', fn () => $this->subjectOffering
                ? [
                    'id' => $this->subjectOffering->id,
                    'subject' => [
                        'id' => $this->subjectOffering->subject?->id,
                        'subject_name' => $this->subjectOffering->subject?->subject_name,
                        'subject_code' => $this->subjectOffering->subject?->subject_code,
                    ],
                ]
                : null),
            'uploader' => $this->whenLoaded('uploader', fn () => $this->uploader
                ? [
                    'id' => $this->uploader->id,
                    'name' => $this->uploader->name,
                ]
                : null),
            'title' => $this->title,
            'description' => $this->description,
            'original_filename' => $this->original_filename,
            'file_type' => $this->file_type,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'status' => $this->status,
            'is_bookmarked' => $this->when(
                isset($this->is_bookmarked),
                fn () => (bool) $this->is_bookmarked
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'bookmarked_at' => $this->when(
                isset($this->bookmarked_at),
                fn () => $this->bookmarked_at
                    ? date(DATE_ATOM, strtotime((string) $this->bookmarked_at))
                    : null
            ),
        ];
    }
}
