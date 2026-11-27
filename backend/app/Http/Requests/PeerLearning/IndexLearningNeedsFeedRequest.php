<?php

namespace App\Http\Requests\PeerLearning;

use App\Models\PeerLearningRequest;
use Illuminate\Foundation\Http\FormRequest;

class IndexLearningNeedsFeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewFeed', PeerLearningRequest::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'academic_year_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:academic_years,id',
            ],
            'year_level_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:year_levels,id',
            ],
            'topic_type' => [
                'sometimes',
                'nullable',
                'in:custom,official',
            ],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
