<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class QuoteOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'quote_expires_at' => ['required', 'date', 'after:now'],
            'quote_note' => ['nullable', 'string', 'max:1000'],
            'quote_subtotal' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'quote_note' => filled($this->quote_note) ? trim((string) $this->quote_note) : null,
        ]);
    }
}
