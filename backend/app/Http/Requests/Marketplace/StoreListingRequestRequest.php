<?php

namespace App\Http\Requests\Marketplace;


use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
