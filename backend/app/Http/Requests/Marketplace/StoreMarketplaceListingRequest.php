<?php

namespace App\Http\Requests\Marketplace;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMarketplaceListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:marketplace_categories,id',
            ],

            'title' => [
                'sometimes',
                'required',
                'string',
                'max:180',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'listing_type' => [
                'sometimes',
                'required',
                Rule::in(['sale', 'donation', 'wanted']),
            ],

            'price' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],

            'condition' => [
                'sometimes',
                'nullable',
                Rule::in(['new', 'like_new', 'good', 'fair', 'poor']),
            ],

            'images' => [
                'required',
                'array',
                'min:1',
                'max:5',
            ],

            'images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('listing_type');

            if ($type === 'sale') {
                $price = $this->input('price');

                if ($price === null || !is_numeric($price) || $price < 0) {
                    $validator->errors()->add(
                        'price',
                        'Price is required for sale listings and must be at least 0.'
                    );
                }
            }

            if (
                in_array($type, ['donation', 'wanted'], true)
                && $this->exists('price')
            ) {
                $validator->errors()->add(
                    'price',
                    'Price must not be provided for donation or wanted listings.'
                );
            }
        });
    }
}
