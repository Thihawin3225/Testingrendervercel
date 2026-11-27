<?php

namespace App\Http\Requests\StudyMaterial;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudyMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('studyMaterial')) ?? false;
    }

    public function rules(): array
    {
        $allowedExtensions = ['pdf', 'ppt', 'pptx', 'doc', 'docx'];

        return [
            'subject_offering_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('subject_offerings', 'id'),
            ],
            'title' => ['sometimes', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'file' => [
                'sometimes',
                'file',
                'max:15360',
                'mimes:'.implode(',', $allowedExtensions),
                'extensions:'.implode(',', $allowedExtensions),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny([
                'subject_offering_id',
                'title',
                'description',
                'file',
            ])) {
                $validator->errors()->add(
                    'material',
                    'At least one material field must be provided.'
                );
            }
        });
    }
}
