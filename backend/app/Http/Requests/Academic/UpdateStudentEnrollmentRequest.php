<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],

            'academic_year_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('academic_years', 'id'),
            ],

            'year_level_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('year_levels', 'id'),
            ],

            'major_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('majors', 'id'),
            ],

            'semester_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('semesters', 'id'),
            ],

            'status' => [
                'sometimes',
                'required',
                Rule::in([
                    'active',
                    'completed',
                    'withdrawn',
                    'suspended',
                ]),
            ],
        ];
    }
}
