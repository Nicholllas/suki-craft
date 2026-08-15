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

    public function rules(): array
    {
        return [
            ...$this->productRules($this->route('product')),
            'image_order' => ['nullable', 'array'],
            'image_order.*' => ['integer'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'primary_image_id' => ['nullable', 'integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $product = $this->route('product');
            $remainingImages = $product->images()->count() + count($this->file('images', []));

            if ($remainingImages === 0) {
                $validator->errors()->add('images', 'Produk harus memiliki minimal satu foto.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->prepareProductForValidation();
    }
}
