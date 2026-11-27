<?php

namespace App\Http\Requests\Academic;

use App\Models\Semester;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSemesterRequest extends FormRequest
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
        $routeSemester = $this->route('semester');

        $semesterId = $routeSemester instanceof Semester
            ? $routeSemester->id
            : $routeSemester;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:30',
                Rule::in([
                    'First',
                    'First Semester',
                    'Second',
                    'Second Semester',
                ]),
                Rule::unique('semesters', 'name')->ignore($semesterId),
            ],
        ];
    }
}
