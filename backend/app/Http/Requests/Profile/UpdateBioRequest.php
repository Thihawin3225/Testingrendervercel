<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->role === 'student'
            && $this->user()->status === 'active';
    }

    public function rules(): array
    {
        return [
            'bio' => [
                'present',
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
