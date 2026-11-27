<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'learner_id',
    'subject_offering_id',
    'title',
    'description',
    'learning_goal',
    'preferred_mode',
    'preferred_availability',
    'status',
    'availability_id',
    'campus_location_id',
    'custom_topic_id',
])]
class PeerLearningRequest extends Model
{
    /**
     * The student who created the peer learning request.
     */
    public function learner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'learner_id');
    }

    public function subjectOffering(): BelongsTo
    {
        return $this->belongsTo(SubjectOffering::class);
    }

    public function peerLearningMatches(): HasMany
    {
        return $this->hasMany(PeerLearningMatch::class);
    }

    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class, 'availability_id');
    }

    public function campusLocation(): BelongsTo
    {
        return $this->belongsTo(CampusLocation::class, 'campus_location_id');
    }

    public function customTopic(): BelongsTo
    {
        return $this->belongsTo(CustomTopic::class, 'custom_topic_id');
    }
}
