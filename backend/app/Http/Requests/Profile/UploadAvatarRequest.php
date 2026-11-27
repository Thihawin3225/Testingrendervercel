<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
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
            'avatar' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Please select an image.',
            'avatar.image' => 'The uploaded file must be an image.',
            'avatar.mimes' => 'Only JPG, JPEG, PNG and WEBP images are allowed.',
            'avatar.max' => 'Image size must not exceed 2MB.',
        ];
    }
}
