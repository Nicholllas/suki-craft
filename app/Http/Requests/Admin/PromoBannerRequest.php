<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PromoBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255'], 'image' => [$this->isMethod('post') ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'link_url' => ['nullable', 'url', 'max:255'], 'is_active' => ['nullable', 'boolean'], 'sort_order' => ['nullable', 'integer', 'min:0']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active'), 'sort_order' => $this->input('sort_order') ?: 0]);
    }
}
