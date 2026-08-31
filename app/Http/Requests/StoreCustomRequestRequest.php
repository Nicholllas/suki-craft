<?php

namespace App\Http\Requests;

use App\Services\CustomRequestService;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('customer')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'additional_notes' => ['nullable', 'string', 'max:1000'],
            'budget_range' => ['required', Rule::in(array_keys(CustomRequestService::budgetRanges()))],
            'custom_bouquet_category_id' => ['required', 'integer', Rule::exists('custom_bouquet_categories', 'id')->where('is_active', true)],
            'item_source' => ['required', Rule::in(['sukicraft_purchases', 'customer_provides'])],
            'items' => ['required', 'array', 'min:1', 'max:10'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'needed_date' => ['required', Rule::date()->format('Y-m-d')->afterOrEqual(Carbon::today('Asia/Jakarta'))],
            'reference_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'wrapping_preference' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'additional_notes' => 'catatan tambahan',
            'budget_range' => 'rentang anggaran',
            'custom_bouquet_category_id' => 'kategori buket',
            'item_source' => 'sumber isi buket',
            'items' => 'daftar isi buket',
            'items.*.name' => 'nama isi buket',
            'items.*.notes' => 'catatan isi buket',
            'items.*.quantity' => 'jumlah isi buket',
            'needed_date' => 'tanggal dibutuhkan',
            'reference_image' => 'foto referensi',
            'wrapping_preference' => 'preferensi wrapping',
        ];
    }

    protected function prepareForValidation(): void
    {
        $trim = fn (mixed $value): mixed => is_string($value) ? trim($value) : $value;
        $items = $this->input('items');

        $this->merge([
            'additional_notes' => $trim($this->input('additional_notes')),
            'items' => is_array($items) ? array_map(fn (mixed $item): mixed => is_array($item) ? [
                'name' => $trim($item['name'] ?? null),
                'notes' => $trim($item['notes'] ?? null),
                'quantity' => $item['quantity'] ?? null,
            ] : $item, $items) : $items,
            'wrapping_preference' => $trim($this->input('wrapping_preference')),
        ]);
    }
}
