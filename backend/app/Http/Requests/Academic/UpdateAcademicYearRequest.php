<?php

namespace App\Http\Requests\Academic;

use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $academicYearId = $this->route('academic_year');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}-\d{4}$/',
                Rule::unique('academic_years', 'name')
                    ->ignore($academicYearId),
            ],

            'start_date' => [
                'sometimes',
                'required',
                'date',
            ],

            'end_date' => [
                'sometimes',
                'required',
                'date',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $academicYearId = $this->route('academic_year');

            $academicYear = AcademicYear::find($academicYearId);

            if (!$academicYear) {
                return;
            }

            // Get updated values or use existing database values
            $name = $this->input(
                'name',
                $academicYear->name
            );

            $startDate = $this->input(
                'start_date',
                $academicYear->start_date
            );

            $endDate = $this->input(
                'end_date',
                $academicYear->end_date
            );

            // Validate academic year name format
            if (
                !is_string($name) ||
                !preg_match('/^(\d{4})-(\d{4})$/', $name, $matches)
            ) {
                return;
            }

            $startYear = (int) $matches[1];
            $endYear = (int) $matches[2];

            // End year must be exactly one year after start year
            if ($endYear !== $startYear + 1) {
                $validator->errors()->add(
                    'name',
                    'The academic year must use consecutive years, for example 2016-2017.'
                );
            }

            // Validate start date year
            if (
                !$validator->errors()->has('start_date') &&
                $startDate &&
                date('Y', strtotime((string) $startDate)) !== (string) $startYear
            ) {
                $validator->errors()->add(
                    'start_date',
                    'Start date must be within the academic year start year.'
                );
            }

            // Validate end date year
            if (
                !$validator->errors()->has('end_date') &&
                $endDate &&
                date('Y', strtotime((string) $endDate)) !== (string) $endYear
            ) {
                $validator->errors()->add(
                    'end_date',
                    'End date must be within the academic year end year.'
                );
            }

            // Validate end date is not earlier than start date
            if (
                !$validator->errors()->has('start_date') &&
                !$validator->errors()->has('end_date') &&
                strtotime((string) $endDate) < strtotime((string) $startDate)
            ) {
                $validator->errors()->add(
                    'end_date',
                    'End date must be after or equal to start date.'
                );
            }

            // Prevent changing to a previous academic year
            $originalStartYear = (int) date(
                'Y',
                strtotime((string) $academicYear->start_date)
            );

            if ($startYear < $originalStartYear) {
                $validator->errors()->add(
                    'name',
                    'You cannot change the academic year to a previous year.'
                );
            }
        });
    }
}
