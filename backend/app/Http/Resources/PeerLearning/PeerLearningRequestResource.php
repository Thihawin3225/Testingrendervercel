<?php

namespace App\Http\Resources\PeerLearning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeerLearningRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_offering_id' => $this->subject_offering_id,
            'custom_topic_id' => $this->custom_topic_id,
            'title' => $this->title,
            'description' => $this->description,
            'learning_goal' => $this->learning_goal,
            'preferred_mode' => $this->preferred_mode,
            'preferred_availability' => $this->preferred_availability,
            'preferred_start_date' => $this->preferred_start_date
                ? substr((string) $this->preferred_start_date, 0, 10)
                : null,
            'preferred_end_date' => $this->preferred_end_date
                ? substr((string) $this->preferred_end_date, 0, 10)
                : null,
            'preferred_start_time' => $this->preferred_start_time
                ? substr((string) $this->preferred_start_time, 0, 5)
                : null,
            'preferred_end_time' => $this->preferred_end_time
                ? substr((string) $this->preferred_end_time, 0, 5)
                : null,
            'custom_topic' => $this->whenLoaded('customTopic', fn () => $this->customTopic
                ? [
                    'id' => $this->customTopic->id,
                    'title' => $this->customTopic->title,
                ]
                : null),
            'topic_title' => $this->when(
                $this->relationLoaded('customTopic')
                    || $this->relationLoaded('subjectOffering'),
                fn () => $this->customTopic?->title
                    ?? $this->subjectOffering?->subject?->subject_name
            ),
            'learner' => $this->whenLoaded('learner', fn () => $this->learner
                ? [
                    'id' => $this->learner->id,
                    'name' => $this->learner->name,
                ]
                : null),
            'status' => $this->status,
            'matches_count' => $this->whenCounted('peerLearningMatches'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
