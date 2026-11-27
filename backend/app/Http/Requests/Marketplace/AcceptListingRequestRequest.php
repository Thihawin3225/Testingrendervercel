<?php

namespace App\Http\Requests\Marketplace;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcceptListingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authentication is handled by auth:sanctum middleware.
        return true;
    }

    public function rules(): array
    {
        return [
            'seller_phone' => [
                'required',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s()]{7,30}$/',
            ],

            'campus_location_id' => [
                'required',
                'integer',
                Rule::exists('campus_locations', 'id')
                    ->where('is_active', true),
            ],

            'seller_message' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'meeting_at' => [
                'required',
                'date',
                'after:now',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'seller_phone.required' =>
            'Phone number is required.',

            'seller_phone.regex' =>
            'Please enter a valid phone number.',

            'campus_location_id.required' =>
            'Please select a campus location.',

            'campus_location_id.exists' =>
            'The selected campus location is unavailable.',

            'meeting_at.required' =>
            'Meeting date and time are required.',

            'meeting_at.after' =>
            'Meeting date and time must be in the future.',
        ];
    }
}
