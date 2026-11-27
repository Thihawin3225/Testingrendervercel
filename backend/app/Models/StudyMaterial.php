<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'subject_offering_id',
    'title',
    'description',
    'original_filename',
    'cloudinary_public_id',
    'cloudinary_asset_id',
    'cloudinary_resource_type',
    'cloudinary_delivery_type',
    'file_type',
    'mime_type',
    'file_size',
    'file_hash',
    'status',
])]
class StudyMaterial extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    /**
     * The user who uploaded the material.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subjectOffering(): BelongsTo
    {
        return $this->belongsTo(SubjectOffering::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(MaterialBookmark::class);
    }
}
