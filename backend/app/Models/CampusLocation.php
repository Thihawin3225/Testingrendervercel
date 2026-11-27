<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CampusLocation extends Model
{
    use HasFactory;

    protected $table = 'campus_locations';

    protected $fillable = [
        'name',
        'building',
        'description',
        'is_active',
    ];

    public function mentorProfiles(): HasMany
    {
        return $this->hasMany(MentorProfile::class, 'campus_location_id');
    }

    public function learningRequests(): HasMany
    {
        return $this->hasMany(PeerLearningRequest::class, 'campus_location_id');
    }
    public function listingRequests(): HasMany
    {
        return $this->hasMany(
            ListingRequest::class,
            'campus_location_id'
        );
    }
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->building
                ? "{$this->name} - {$this->building}"
                : $this->name
        );
    }
}
