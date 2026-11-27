<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMajorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $majorId = $this->route('major');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:10',
                Rule::unique('majors', 'code')->ignore($majorId),
            ],

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('majors', 'name')->ignore($majorId),
            ],
        ];
    }
}
