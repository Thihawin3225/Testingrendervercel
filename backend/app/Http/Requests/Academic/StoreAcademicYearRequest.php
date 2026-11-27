<?php

namespace App\Http\Requests\Academic;

use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}-\d{4}$/',
                Rule::unique('academic_years', 'name'),
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
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

            $name = $this->input('name');

            if (!preg_match('/^(\d{4})-(\d{4})$/', $name, $matches)) {
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

            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');

            // Start date must belong to the start year
            if (
                $startDate &&
                date('Y', strtotime($startDate)) !== (string) $startYear
            ) {
                $validator->errors()->add(
                    'start_date',
                    'Start date must be within the academic year start year.'
                );
            }

            // End date must belong to the end year
            if (
                $endDate &&
                date('Y', strtotime($endDate)) !== (string) $endYear
            ) {
                $validator->errors()->add(
                    'end_date',
                    'End date must be within the academic year end year.'
                );
            }

            // Get the latest academic year start date
            $latestStartDate = AcademicYear::query()
                ->orderByDesc('start_date')
                ->value('start_date');

            // New academic year must be later than the latest existing year
            if ($latestStartDate) {
                $latestStartYear = (int) date(
                    'Y',
                    strtotime($latestStartDate)
                );

                if ($startYear <= $latestStartYear) {
                    $validator->errors()->add(
                        'name',
                        'The new academic year must be later than the latest existing academic year.'
                    );
                }
            }
        });
    }
}
