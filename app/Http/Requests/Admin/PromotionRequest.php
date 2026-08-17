<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return ['code' => ['required', 'string', 'max:50', Rule::unique('promotions', 'code')->ignore($this->route('promotion'))], 'type' => ['required', Rule::in(['percentage', 'fixed'])], 'value' => ['required', 'numeric', 'gt:0'], 'min_purchase' => ['nullable', 'numeric', 'min:0'], 'max_discount' => ['nullable', 'numeric', 'gt:0'], 'usage_limit' => ['nullable', 'integer', 'min:1'], 'usage_limit_per_customer' => ['nullable', 'integer', 'min:1'], 'starts_at' => ['required', Rule::date()->format('Y-m-d\\TH:i')], 'expires_at' => ['required', Rule::date()->format('Y-m-d\\TH:i')->after('starts_at')], 'is_active' => ['nullable', 'boolean']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper(trim((string) $this->input('code'))), 'is_active' => $this->boolean('is_active')]);
    }
}
