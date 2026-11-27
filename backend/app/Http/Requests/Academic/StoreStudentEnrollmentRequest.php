<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],

            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists('academic_years', 'id'),
            ],

            'year_level_id' => [
                'required',
                'integer',
                Rule::exists('year_levels', 'id'),
            ],

            'major_id' => [
                'required',
                'integer',
                Rule::exists('majors', 'id'),
            ],

            'semester_id' => [
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
