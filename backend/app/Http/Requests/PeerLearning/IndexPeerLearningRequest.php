<?php

namespace App\Http\Requests\PeerLearning;

use App\Models\PeerLearningRequest;
use Illuminate\Foundation\Http\FormRequest;

class IndexPeerLearningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', PeerLearningRequest::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => [
                'sometimes',
                'nullable',
                'in:open,matched,closed,cancelled',
            ],
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
        ];
    }
}
