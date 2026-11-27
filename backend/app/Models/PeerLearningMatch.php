<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'peer_learning_request_id',
    'mentor_profile_id',
    'message',
    'status',
])]
class PeerLearningMatch extends Model
{
    public function peerLearningRequest(): BelongsTo
    {
        return $this->belongsTo(PeerLearningRequest::class);
    }

    public function mentorProfile(): BelongsTo
    {
        return $this->belongsTo(MentorProfile::class);
    }

    public function mentoringBookings(): HasMany
    {
        return $this->hasMany(MentoringBooking::class);
    }
}
