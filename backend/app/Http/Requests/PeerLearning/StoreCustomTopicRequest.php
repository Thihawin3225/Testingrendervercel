<?php

namespace App\Http\Requests\PeerLearning;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'student'
            && $this->user()?->status === 'active';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
