<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class OrderIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'delivery_date_from' => ['nullable', 'date_format:Y-m-d'],
            'delivery_date_to' => ['nullable', 'date_format:Y-m-d'],
            'order_date_from' => ['nullable', 'date_format:Y-m-d'],
            'order_date_to' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:100'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => [Rule::enum(OrderStatus::class)],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->validateDateRange($validator, 'order_date_from', 'order_date_to', 'Rentang tanggal pesanan tidak valid.');
                $this->validateDateRange($validator, 'delivery_date_from', 'delivery_date_to', 'Rentang tanggal pengiriman tidak valid.');
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $search = trim((string) $this->input('search'));

        $this->merge(['search' => $search ?: null]);
    }

    private function validateDateRange(Validator $validator, string $from, string $to, string $message): void
    {
        if ($this->filled($from) && $this->filled($to) && $this->date($from)->isAfter($this->date($to))) {
            $validator->errors()->add($to, $message);
        }
    }
}
