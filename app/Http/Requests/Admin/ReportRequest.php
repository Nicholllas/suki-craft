<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:Y-m-d'],
            'group_by' => ['nullable', Rule::in(['day', 'week', 'month'])],
            'preset' => ['nullable', Rule::in(['today', 'week', 'month', 'last_month', 'custom'])],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $preset = $this->input('preset', 'month');

        if ($preset === 'custom' && filled($this->input('from')) && filled($this->input('to'))) {
            return;
        }

        [$from, $to] = match ($preset) {
            'today' => [today(), today()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $this->merge(['from' => $from->toDateString(), 'preset' => $preset, 'to' => $to->toDateString()]);
    }
}
