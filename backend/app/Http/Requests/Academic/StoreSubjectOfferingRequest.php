<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectOfferingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // New Subject (optional for existing subject flow)
            'subject' => [
                'required_without:subject_id',
                'array',
            ],

            'subject.subject_code' => [
                'required_with:subject',
                'string',
                'max:30',
                'unique:subjects,subject_code',
            ],

            'subject.subject_name' => [
                'required_with:subject',
                'string',
                'max:150',
            ],

            'subject.description' => [
                'nullable',
                'string',
            ],

            // New Subject Offering (nested)
            'offering' => [
                'required_without:subject_id',
                'array',
            ],

            'offering.academic_year_id' => [
                'required_with:offering',
                'integer',
                'exists:academic_years,id',
                Rule::exists('academic_years', 'id')
                    ->where('is_active', true),
            ],

            'offering.year_level_id' => [
                'required_with:offering',
                'integer',
                'exists:year_levels,id',
            ],

            'offering.major_id' => [
                'required_with:offering',
                'integer',
                'exists:majors,id',
            ],

            'offering.semester_id' => [
                'required_with:offering',
                'integer',
                'exists:semesters,id',
            ],

            // Existing Subject flow
            'subject_id' => [
                'required_without:subject',
                'integer',
                'exists:subjects,id',
            ],

            'academic_year_id' => [
                'required_without:offering',
                'integer',
                'exists:academic_years,id',
                Rule::exists('academic_years', 'id')
                    ->where('is_active', true),
            ],

            'year_level_id' => [
                'required_without:offering',
                'integer',
                'exists:year_levels,id',
            ],

            'major_id' => [
                'required_without:offering',
                'integer',
                'exists:majors,id',
            ],

            'semester_id' => [
                'required_without:offering',
                'integer',
                'exists:semesters,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.subject_code.required' =>
                'Subject code is required.',

            'subject.subject_code.unique' =>
                'This subject code already exists.',

            'subject.subject_name.required' =>
                'Subject name is required.',

            'offering.academic_year_id.exists' =>
                'Selected academic year is inactive or does not exist.',

            'offering.year_level_id.required' =>
                'Year level is required.',

            'offering.major_id.required' =>
                'Major is required.',

            'offering.semester_id.required' =>
                'Semester is required.',
        ];
    }
}
