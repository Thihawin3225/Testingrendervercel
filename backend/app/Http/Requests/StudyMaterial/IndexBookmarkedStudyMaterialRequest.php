<?php

namespace App\Http\Requests\StudyMaterial;

use App\Models\StudyMaterial;
use Illuminate\Foundation\Http\FormRequest;

class IndexBookmarkedStudyMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', StudyMaterial::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'material_type' => ['sometimes', 'nullable', 'in:custom,official'],
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
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
