<?php

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMarketplaceListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string'],
            'category_id' => [
                'sometimes',
                'integer',
                'exists:marketplace_categories,id',
            ],
            'listing_type' => ['sometimes', 'in:sale,donation,wanted'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'condition' => [
                'sometimes',
                'nullable',
                'in:new,like_new,good,fair,poor',
            ],

            // New images
            'images' => ['sometimes', 'array', 'max:5'],
            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            // Existing images to remove
            'removed_image_ids' => ['sometimes', 'array', 'max:5'],
            'removed_image_ids.*' => [
                'integer',
                'distinct',
                'exists:marketplace_listing_images,id',
            ],
        ];
    }
}
