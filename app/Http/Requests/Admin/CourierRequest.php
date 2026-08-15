<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'is_active' => ['nullable', 'boolean'],
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:25'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'name' => trim((string) $this->input('name')),
            'phone' => trim((string) $this->input('phone')),
        ]);
    }
}
