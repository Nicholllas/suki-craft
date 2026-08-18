<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\InteractsWithProductRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProductUpdateRequest extends FormRequest
{
    use InteractsWithProductRules;

    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function after(): array
    {
        return [
            ...$this->productValidationCallbacks(),
            function (Validator $validator): void {
                $product = $this->route('product');
                $remainingImages = $product->images()->count() + count($this->file('images', []));

                if ($remainingImages === 0) {
                    $validator->errors()->add('images', 'Produk harus memiliki minimal satu foto.');
                }
            },
        ];
    }

    public function rules(): array
    {
        return [
            ...$this->productRules(),
            'image_order' => ['nullable', 'array'],
            'image_order.*' => ['integer'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'primary_image_id' => ['nullable', 'integer'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareProductForValidation();
    }
}
