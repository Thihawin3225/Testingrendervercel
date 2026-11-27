<?php

namespace App\Http\Requests\StudyMaterial;

use App\Models\StudyMaterial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudyMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', StudyMaterial::class) ?? false;
    }

    public function rules(): array
    {
        $allowedExtensions = ['pdf', 'ppt', 'pptx', 'doc', 'docx'];

        return [
            'subject_offering_id' => [
                'nullable',
                'integer',
                Rule::exists('subject_offerings', 'id'),
            ],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'file' => [
                'required',
                'file',
                'max:15360',
                'mimes:'.implode(',', $allowedExtensions),
                'extensions:'.implode(',', $allowedExtensions),
            ],
        ];
    }
}
