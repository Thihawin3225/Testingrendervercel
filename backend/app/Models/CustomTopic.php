<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomTopic extends Model
{
    use HasFactory;

    protected $table = 'custom_topics';

    protected $fillable = [
        'user_id',
        'title',
        'subtitle',
        'description',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function learningRequests(): HasMany
    {
        return $this->hasMany(PeerLearningRequest::class, 'custom_topic_id');
    }
}
