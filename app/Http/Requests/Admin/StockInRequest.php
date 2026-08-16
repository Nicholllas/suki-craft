<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return ['quantity' => ['required', 'numeric', 'gt:0'], 'reason' => ['required', 'string', 'max:255']];
    }
}
