<?php

namespace App\Http\Requests;

use App\Services\PhoneNumberNormalizer;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:25', PhoneNumberNormalizer::validationRule()],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'delivery_latitude' => ['required', 'numeric', 'between:-90,90'],
            'delivery_longitude' => ['required', 'numeric', 'between:-180,180'],
            'delivery_quote' => ['required', 'string'],
            'delivery_date' => ['required', Rule::date()->format('Y-m-d')->afterOrEqual(Carbon::today('Asia/Jakarta'))],
            'delivery_time_slot' => ['required', 'string', Rule::in($this->deliveryTimeSlots())],
            'idempotency_token' => ['required', 'uuid'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'promotion_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_phone.regex' => __('store.validation.messages.phone_format'),
            'delivery_date.after_or_equal' => __('store.validation.messages.delivery_date_past'),
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->hasInvalidDeliverySelection($validator)) {
                return;
            }

            $deliveryDate = Carbon::createFromFormat('Y-m-d', (string) $this->input('delivery_date'), 'Asia/Jakarta')->startOfDay();
            $slot = config('delivery.time_slots.'.$this->input('delivery_time_slot'));

            if ($deliveryDate->isToday('Asia/Jakarta') && Carbon::now('Asia/Jakarta')->greaterThanOrEqualTo($this->sameDayCutoff($deliveryDate, $slot))) {
                $validator->errors()->add('delivery_time_slot', __('store.validation.messages.delivery_slot_unavailable'));
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_email' => $this->trimmedInput('customer_email'),
            'customer_name' => $this->trimmedInput('customer_name'),
            'customer_phone' => $this->trimmedInput('customer_phone'),
            'delivery_address' => $this->trimmedInput('delivery_address'),
            'delivery_time_slot' => $this->trimmedInput('delivery_time_slot'),
            'notes' => $this->trimmedInput('notes'),
            'promotion_code' => $this->trimmedInput('promotion_code'),
        ]);
    }

    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated();

        if (array_key_exists('customer_phone', $validated)) {
            $validated['customer_phone'] = PhoneNumberNormalizer::normalize($validated['customer_phone']);
        }

        return data_get($validated, $key, $default);
    }

    private function deliveryTimeSlots(): array
    {
        return array_keys(config('delivery.time_slots', []));
    }

    private function hasInvalidDeliverySelection(Validator $validator): bool
    {
        return $validator->errors()->has('delivery_date') || $validator->errors()->has('delivery_time_slot');
    }

    private function sameDayCutoff(Carbon $deliveryDate, array $slot): Carbon
    {
        return Carbon::parse($deliveryDate->toDateString().' '.$slot['start_time'], 'Asia/Jakarta')->subHours((int) config('delivery.same_day_prep_hours', 4));
    }

    private function trimmedInput(string $key): ?string
    {
        $value = $this->input($key);

        return filled($value) ? trim((string) $value) : null;
    }
}
