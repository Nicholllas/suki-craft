<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuoteCustomRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quote_expires_at' => ['required', Rule::date()->format('Y-m-d\TH:i')->after(now())],
            'quote_note' => ['nullable', 'string', 'max:1000'],
            'quoted_price' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['quote_note' => is_string($this->input('quote_note')) ? trim($this->input('quote_note')) : $this->input('quote_note')]);
    }
}
