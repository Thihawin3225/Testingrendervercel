<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreYearLevelRequest extends FormRequest
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
                'max:30',
                Rule::unique('year_levels', 'name'),
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

            $name = trim((string) $this->input('name'));

            $normalizedName = mb_strtolower($name);

            // Only these exact standard names are allowed.
            $standardNames = [
                'first year',
                'second year',
                'third year',
                'fourth year',
                'fifth year',
                'sixth year',
            ];

            // Standard names do not need confirmation.
            if (in_array($normalizedName, $standardNames, true)) {
                return;
            }

            // Other custom names must contain the word "Final".
            if (!preg_match('/\bfinal\b/i', $name)) {
                $validator->errors()->add(
                    'name',
                    'Only First Year to Sixth Year or a custom name containing "Final" is allowed.'
                );

                return;
            }

            // Final custom names require explicit confirmation.
            if (!$this->boolean('final_year_confirmed')) {
                $validator->errors()->add(
                    'final_year_confirmed',
                    'Please confirm that this is the final year of your university.'
                );
            }
        });
    }
}
