<?php

namespace App\Http\Requests\Concerns;

use App\Models\Product;
use Illuminate\Validation\Rule;

trait InteractsWithProductRules
{
    protected function productRules(?Product $product = null): array
    {
        return [
            'base_price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('products')->ignore($product?->id)],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.is_active' => ['required', 'boolean'],
            'variants.*.label' => ['required', 'string', 'max:100'],
            'variants.*.price_adjustment' => ['required', 'numeric'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareProductForValidation(): void
    {
        $this->merge([
            'description' => filled($this->description) ? trim($this->description) : null,
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'name' => trim((string) $this->name),
            'slug' => filled($this->slug) ? trim($this->slug) : null,
        ]);
    }
}
