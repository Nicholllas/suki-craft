<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IngredientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'current_stock' => ['required', 'numeric'],
            'is_active' => ['required', 'boolean'],
            'minimum_stock' => ['required', 'numeric'],
            'name' => ['required', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:30'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active'), 'name' => trim((string) $this->name), 'unit' => trim((string) $this->unit)]);
    }
}
