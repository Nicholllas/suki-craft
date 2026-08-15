<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['reason' => trim((string) $this->input('reason'))]);
    }
}
