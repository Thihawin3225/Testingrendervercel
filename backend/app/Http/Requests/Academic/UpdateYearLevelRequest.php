<?php

namespace App\Http\Requests\Academic;

use App\Models\YearLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateYearLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeYearLevel = $this->route('yearLevel')
            ?? $this->route('year_level');

        $yearLevelId = $routeYearLevel instanceof YearLevel
            ? $routeYearLevel->id
            : $routeYearLevel;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:30',
                Rule::unique('year_levels', 'name')->ignore($yearLevelId),
            ],

            'final_year_confirmed' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $routeYearLevel = $this->route('yearLevel')
                ?? $this->route('year_level');

            $yearLevel = $routeYearLevel instanceof YearLevel
                ? $routeYearLevel
                : YearLevel::find($routeYearLevel);

            if (!$yearLevel || !$this->has('name')) {
                return;
            }

            $name = trim((string) $this->input('name'));
            $normalizedName = mb_strtolower($name);

            $standardNames = [
                'first year',
                'second year',
                'third year',
                'fourth year',
                'fifth year',
                'sixth year',
            ];

            // Standard Year names are accepted without confirmation.
            if (in_array($normalizedName, $standardNames, true)) {
                return;
            }

            // Custom names must contain the whole word "Final".
            if (!preg_match('/\bfinal\b/i', $name)) {
                $validator->errors()->add(
                    'name',
                    'Only First Year to Sixth Year or a custom name containing "Final" is allowed.'
                );

                return;
            }

            // If the existing record is already a Final Year,
            // changing its Final name does not require confirmation again.
            $originalName = trim($yearLevel->name);

            if (preg_match('/\bfinal\b/i', $originalName)) {
                return;
            }

            // Changing a Standard Year into a Final Year requires confirmation.
            if (!$this->boolean('final_year_confirmed')) {
                $validator->errors()->add(
                    'final_year_confirmed',
                    'Please confirm that this is the final year of your university.'
                );
            }
        });
    }
}
