<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectOfferingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subjectOfferingId = $this->route('subjectOffering');

        return [
            'academic_year_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:academic_years,id',

                Rule::exists('academic_years', 'id')
                    ->where('is_active', true),
            ],

            'year_level_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:year_levels,id',
            ],

            'major_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:majors,id',
            ],

            'semester_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:semesters,id',
            ],

            'subject_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:subjects,id',

                Rule::unique('subject_offerings', 'subject_id')
                    ->where(
                        'academic_year_id',
                        $this->input(
                            'academic_year_id',
                            $this->route('subjectOffering') 
                                ?->academic_year_id
                        )
                    )
                    ->where(
                        'year_level_id',
                        $this->input(
                            'year_level_id',
                            $this->route('subjectOffering')
                                ?->year_level_id
                        )
                    )
                    ->where(
                        'major_id',
                        $this->input(
                            'major_id',
                            $this->route('subjectOffering')
                                ?->major_id
                        )
                    )
                    ->where(
                        'semester_id',
                        $this->input(
                            'semester_id',
                            $this->route('subjectOffering')
                                ?->semester_id
                        )
                    )
                    ->ignore($subjectOfferingId),
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'academic_year_id.exists' =>
                'Selected academic year is inactive or does not exist.',
        ];
    }
}
