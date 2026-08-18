<?php

namespace App\Http\Requests\Concerns;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait InteractsWithProductRules
{
    protected function productRules(): array
    {
        return [
            'base_price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'allow_multiple_variants' => ['required', 'boolean'],
            'ingredients' => ['nullable', 'array'],
            'ingredients.*.ingredient_id' => ['required', 'integer', Rule::exists('ingredients', 'id')],
            'ingredients.*.product_variant_id' => ['nullable', 'integer', Rule::exists('product_variants', 'id')],
            'ingredients.*.quantity_needed' => ['nullable', 'numeric', 'gt:0'],
            'ingredients.*.ratio_per_unit' => ['nullable', 'numeric', 'gt:0'],
            'name' => ['required', 'string', 'max:150'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.is_active' => ['required', 'boolean'],
            'variants.*.is_quantity_based' => ['required', 'boolean'],
            'variants.*.label' => ['required', 'string', 'max:100'],
            'variants.*.price_adjustment' => ['required', 'numeric'],
        ];
    }

    protected function productValidationCallbacks(): array
    {
        return [
            function (Validator $validator): void {
                $ingredients = $this->input('ingredients', []);
                $submittedVariants = collect($this->input('variants', []))->filter(fn (mixed $variant): bool => is_array($variant) && filled($variant['id'] ?? null))->keyBy(fn (array $variant): string => (string) $variant['id']);
                $variantIds = collect(is_array($ingredients) ? $ingredients : [])->pluck('product_variant_id')->filter()->map(fn (mixed $variantId): int => (int) $variantId)->unique();
                $productVariants = ProductVariant::query()->whereKey($variantIds)->get(['id', 'is_quantity_based', 'product_id'])->keyBy('id');
                $product = $this->route('product');

                foreach (is_array($ingredients) ? $ingredients : [] as $index => $ingredient) {
                    if (! is_array($ingredient)) {
                        continue;
                    }

                    $variantId = filled($ingredient['product_variant_id'] ?? null) ? (int) $ingredient['product_variant_id'] : null;

                    if (! $variantId) {
                        if (blank($ingredient['quantity_needed'] ?? null)) {
                            $validator->errors()->add("ingredients.{$index}.quantity_needed", 'Qty per buket wajib diisi.');
                        }

                        continue;
                    }

                    $productVariant = $productVariants->get($variantId);
                    $submittedVariant = $submittedVariants->get((string) $variantId);

                    if (! ($product instanceof Product) || ! $productVariant || $productVariant->product_id !== $product->id || ! $submittedVariant) {
                        $validator->errors()->add("ingredients.{$index}.product_variant_id", 'Varian resep harus berasal dari produk ini dan tetap ada pada daftar varian.');

                        continue;
                    }

                    $isQuantityBased = filter_var($submittedVariant['is_quantity_based'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $requiredField = $isQuantityBased ? 'ratio_per_unit' : 'quantity_needed';

                    if (blank($ingredient[$requiredField] ?? null)) {
                        $message = $isQuantityBased ? 'Rasio wajib diisi untuk varian berbasis qty.' : 'Qty per buket wajib diisi.';
                        $validator->errors()->add("ingredients.{$index}.{$requiredField}", $message);
                    }
                }
            },
        ];
    }

    protected function prepareProductForValidation(): void
    {
        $this->merge([
            'description' => filled($this->description) ? trim($this->description) : null,
            'is_active' => $this->boolean('is_active'),
            'allow_multiple_variants' => $this->boolean('allow_multiple_variants'),
            'is_featured' => $this->boolean('is_featured'),
            'name' => trim((string) $this->name),
        ]);

        $this->merge(['variants' => collect($this->input('variants', []))->map(fn (array $variant): array => [...$variant, 'is_quantity_based' => filter_var($variant['is_quantity_based'] ?? false, FILTER_VALIDATE_BOOLEAN), 'is_active' => filter_var($variant['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN)])->all()]);
    }
}
