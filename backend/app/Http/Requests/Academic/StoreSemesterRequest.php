<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $name = strtolower(trim((string) $this->input('name')));

            $names = [
                'first' => 'First',
                'first semester' => 'First Semester',
                'second' => 'Second',
                'second semester' => 'Second Semester',
            ];

            $this->merge([
                'name' => $names[$name] ?? $this->input('name'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:30',
                Rule::in([
                    'First',
                    'First Semester',
                    'Second',
                    'Second Semester',
                ]),
                Rule::unique('semesters', 'name'),
            ],
        ];
    }
}
