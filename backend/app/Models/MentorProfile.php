<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'introduction',
    'experience',
    'availability',
    'is_active',
    'campus_location_id',
])]
class MentorProfile extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjectOfferings(): BelongsToMany
    {
        return $this->belongsToMany(
            SubjectOffering::class,
            'mentor_profile_subjects',
            'mentor_profile_id',
            'subject_offering_id'
        )->withPivot('proficiency_level')->withTimestamps();
    }

    public function peerLearningMatches(): HasMany
    {
        return $this->hasMany(PeerLearningMatch::class);
    }

    public function availability(): HasMany
    {
        return $this->hasMany(Availability::class, 'mentor_profile_id');
    }

    public function campusLocation(): BelongsTo
    {
        return $this->belongsTo(CampusLocation::class, 'campus_location_id');
    }
}
