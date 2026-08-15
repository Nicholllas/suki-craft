<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\InteractsWithProductRules;
use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    use InteractsWithProductRules;

    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [...$this->productRules(), 'images' => ['required', 'array', 'min:1'], 'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048']];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareProductForValidation();
    }
}
