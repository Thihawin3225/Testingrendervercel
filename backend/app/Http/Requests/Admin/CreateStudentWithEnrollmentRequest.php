<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class CreateStudentWithEnrollmentRequest extends CreateStudentRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
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
                Rule::in([
                    'active',
                    'completed',
                    'withdrawn',
                    'suspended',
                ]),
            ],
        ]);
    }
}
