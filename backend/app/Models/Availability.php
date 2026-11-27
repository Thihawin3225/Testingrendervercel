<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Availability extends Model
{
    use HasFactory;

    protected $table = 'availability';

    protected $fillable = [
        'mentor_profile_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_default',
        'day_of_week',
    ];

    public function mentorProfile(): BelongsTo
    {
        return $this->belongsTo(MentorProfile::class, 'mentor_profile_id');
    }

    public function learningRequests(): HasMany
    {
        return $this->hasMany(PeerLearningRequest::class, 'availability_id');
    }
}
