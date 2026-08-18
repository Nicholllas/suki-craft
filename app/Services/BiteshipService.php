<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BiteshipService
{
    public function __construct(private CartService $cartService) {}

    public function searchAreas(string $input): array
    {
        $cacheKey = 'biteship:areas:'.md5(Str::lower(Str::squish($input)));

        return Cache::remember($cacheKey, now()->addDay(), function () use ($input): array {
            $response = $this->client()->get('maps/areas', [
                'countries' => 'ID',
                'input' => $input,
                'type' => 'single',
            ])->throw()->json();

            return collect(Arr::get($response, 'areas', []))
                ->map(fn (array $area): array => [
                    'id' => (string) Arr::get($area, 'id'),
                    'name' => (string) Arr::get($area, 'name'),
                    'postal_code' => Arr::get($area, 'postal_code'),
                ])
                ->filter(fn (array $area): bool => filled($area['id']) && filled($area['name']))
                ->values()
                ->all();
        });
    }

    public function getRates(string $destinationAreaId, float $weightGrams): array
    {
        $originAreaId = $this->originAreaId();
        $itemValue = max(1, (int) round($this->cartService->getTotal()));
        $weight = max(1, (int) ceil($weightGrams));
        $couriers = $this->courierCodes();
        $cacheKey = 'biteship:rates:'.md5(implode('|', [
            $originAreaId,
            $destinationAreaId,
            $itemValue,
            $weight,
            implode(',', $couriers),
        ]));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($couriers, $destinationAreaId, $itemValue, $originAreaId, $weight): array {
            $response = $this->client()->post('rates/couriers', [
                'origin_area_id' => $originAreaId,
                'destination_area_id' => $destinationAreaId,
                'couriers' => implode(',', $couriers),
                'items' => [[
                    'name' => 'Pesanan Suki Craft',
                    'description' => 'Buket bunga',
                    'value' => $itemValue,
                    'quantity' => 1,
                    'weight' => $weight,
                ]],
            ])->throw()->json();

            return collect(Arr::get($response, 'pricing', []))
                ->filter(fn (array $rate): bool => $this->isRegularCourierRate($rate))
                ->map(fn (array $rate): array => [
                    'company' => Str::lower((string) Arr::get($rate, 'courier_code', Arr::get($rate, 'company'))),
                    'courier' => (string) Arr::get($rate, 'courier_name'),
                    'service' => Str::lower((string) Arr::get($rate, 'courier_service_code', Arr::get($rate, 'type'))),
                    'service_name' => (string) Arr::get($rate, 'courier_service_name'),
                    'price' => (int) Arr::get($rate, 'price', 0),
                    'estimated_days' => (string) Arr::get($rate, 'duration'),
                ])
                ->filter(fn (array $rate): bool => filled($rate['company']) && filled($rate['service']))
                ->sortBy('price')
                ->values()
                ->all();
        });
    }

    public function createOrder(Order $order, string $courierCompany, string $courierType): array
    {
        $courierCompany = Str::lower($courierCompany);
        $courierType = Str::lower($courierType);

        if (! in_array($courierCompany, $this->courierCodes(), true)) {
            throw ValidationException::withMessages(['courier_company' => 'Kurir ekspedisi yang dipilih tidak tersedia.']);
        }

        if ($order->biteship_order_id) {
            throw ValidationException::withMessages(['delivery' => 'Pengiriman Biteship untuk pesanan ini sudah dibuat.']);
        }

        if (blank($order->destination_area_id)) {
            throw ValidationException::withMessages(['delivery' => 'Area tujuan pesanan belum tersedia untuk booking pengiriman.']);
        }

        $order->loadMissing('items.product');
        $response = $this->client()->post('orders', [
            'origin_contact_name' => config('biteship.origin.contact_name'),
            'origin_contact_phone' => config('biteship.origin.contact_phone'),
            'origin_address' => config('biteship.origin.address'),
            'origin_area_id' => $this->originAreaId(),
            'origin_postal_code' => (int) config('biteship.origin.postal_code'),
            'destination_contact_name' => $order->customer_name,
            'destination_contact_phone' => $order->customer_phone,
            'destination_contact_email' => $order->customer_email,
            'destination_address' => $order->delivery_address,
            'destination_area_id' => $order->destination_area_id,
            'destination_postal_code' => $order->destination_postal_code,
            'destination_note' => $order->notes,
            'courier_company' => $courierCompany,
            'courier_type' => $courierType,
            'delivery_type' => 'scheduled',
            'delivery_date' => $order->delivery_date->toDateString(),
            'delivery_time' => Str::before($order->delivery_time_slot, '-'),
            'items' => $order->items->map(fn ($item): array => [
                'name' => $item->product_name,
                'description' => $item->variant_label,
                'quantity' => $item->quantity,
                'value' => (int) round($item->unit_price),
                'weight' => max(1, (int) ($item->product?->weight_grams ?? 1000)),
            ])->all(),
            'metadata' => ['order_number' => $order->order_number],
            'order_note' => $order->notes,
            'reference_id' => $order->order_number,
        ])->throw()->json();
        $courier = Arr::get($response, 'courier', []);
        $trackingId = Arr::get($courier, 'tracking_id');
        $trackingNumber = Arr::get($courier, 'waybill_id');
        $trackingUrl = Arr::get($courier, 'link');

        $order->update([
            'biteship_order_id' => Arr::get($response, 'id'),
            'biteship_tracking_id' => $trackingId,
            'courier_company' => $courierCompany,
            'courier_service' => $courierType,
            'tracking_number' => $trackingNumber,
            'tracking_url' => $trackingUrl,
        ]);
        $order->statusHistories()->create([
            'note' => 'Pengiriman '.$this->courierName($courierCompany).' '.$courierType.' berhasil dibooking melalui Biteship.',
            'status' => $order->status,
        ]);

        return [
            'biteship_order_id' => Arr::get($response, 'id'),
            'status' => $this->normalizeStatus((string) Arr::get($response, 'status')),
            'tracking_id' => $trackingId,
            'tracking_number' => $trackingNumber,
            'tracking_url' => $trackingUrl,
        ];
    }

    public function trackOrder(string $trackingId): array
    {
        $response = $this->client()->get('trackings/'.urlencode($trackingId))->throw()->json();
        $order = Order::query()->where('biteship_tracking_id', $trackingId)->firstOrFail();
        $status = $this->normalizeStatus((string) Arr::get($response, 'status'));
        $trackingNumber = Arr::get($response, 'waybill_id');
        $trackingUrl = Arr::get($response, 'link');

        $order->shipmentStatusLogs()->create([
            'raw_response' => $response,
            'status' => $status,
        ]);
        $order->update([
            'tracking_number' => $trackingNumber ?: $order->tracking_number,
            'tracking_url' => $trackingUrl ?: $order->tracking_url,
        ]);

        return [
            'raw_response' => $response,
            'status' => $status,
            'tracking_number' => $trackingNumber ?: $order->tracking_number,
            'tracking_url' => $trackingUrl ?: $order->tracking_url,
        ];
    }

    private function client(): PendingRequest
    {
        $apiKey = config('biteship.api_key');

        if (blank($apiKey)) {
            throw ValidationException::withMessages(['shipping' => 'Integrasi ongkir belum dikonfigurasi.']);
        }

        $client = Http::baseUrl((string) config('biteship.base_url'))
            ->acceptJson()
            ->withHeaders(['Authorization' => $apiKey])
            ->connectTimeout(3)
            ->timeout(8);

        $caBundle = config('biteship.ca_bundle');

        return filled($caBundle) ? $client->withOptions(['verify' => $caBundle]) : $client;
    }

    private function courierCodes(): array
    {
        return collect(config('biteship.couriers', []))
            ->map(fn (string $courier): string => Str::lower(trim($courier)))
            ->filter()
            ->values()
            ->all();
    }

    private function courierName(string $courierCode): string
    {
        return (string) config('biteship.courier_names.'.$courierCode, Str::upper($courierCode));
    }

    private function isRegularCourierRate(array $rate): bool
    {
        $courierCode = Str::lower((string) Arr::get($rate, 'courier_code', Arr::get($rate, 'company')));

        return in_array($courierCode, $this->courierCodes(), true)
            && Arr::get($rate, 'shipping_type') === 'parcel';
    }

    private function normalizeStatus(string $status): string
    {
        return match (Str::lower($status)) {
            'confirmed', 'scheduled', 'allocated' => 'confirmed',
            'picking_up', 'picked' => 'picked_up',
            'in_transit', 'dropping_off', 'on_hold' => 'in_transit',
            'delivered' => 'delivered',
            'return_in_transit', 'returned', 'rejected', 'disposed' => 'returned',
            default => Str::lower($status),
        };
    }

    private function originAreaId(): string
    {
        $origin = config('biteship.origin', []);
        $requiredFields = ['area_id', 'contact_name', 'contact_phone', 'address', 'postal_code'];

        if (collect($requiredFields)->contains(fn (string $field): bool => blank($origin[$field] ?? null))) {
            throw ValidationException::withMessages(['shipping' => 'Data asal pengiriman Biteship belum lengkap.']);
        }

        return (string) $origin['area_id'];
    }
}
