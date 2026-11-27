<?php

namespace App\Http\Requests\Admin;

use App\Support\CampusConstants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $prefix = CampusConstants::ROLL_NUMBER_PREFIX.'\d+';

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'regex:/^[^@\s]+'.preg_quote(CampusConstants::UNIVERSITY_EMAIL_DOMAIN, '/').'$/i',
                Rule::unique('users', 'email'),
            ],
            'student_number' => [
                'required',
                'string',
                'max:50',
                "regex:/^{$prefix}$/",
                Rule::unique('student_profiles', 'student_number'),
            ],
            // Optional: when omitted, the service assigns the campus default.
            'password' => ['sometimes', 'nullable', 'string', Password::min(8), 'confirmed'],
        ];
    }

    /**
     * Guarantee a password even when the client does not send one.
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data['password'] ??= CampusConstants::DEFAULT_STUDENT_PASSWORD;
        $data['password_confirmation'] ??= $data['password'];

        return $data;
    }
}
