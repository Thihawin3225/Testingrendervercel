<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'peer_learning_match_id',
    'scheduled_at',
    'duration_minutes',
    'mode',
    'location_or_link',
    'notes',
    'status',
])]
class MentoringBooking extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function peerLearningMatch(): BelongsTo
    {
        return $this->belongsTo(PeerLearningMatch::class);
    }
}
