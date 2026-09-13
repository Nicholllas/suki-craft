<?php

namespace App\Services;

use App\Contracts\LocationProvider;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use JsonException;

class DeliveryPricingService
{
    public function __construct(private LocationProvider $locationProvider) {}

    /**
     * @return array{delivery_fee: int, distance_km: float, distance_meters: int, latitude: float, longitude: float, provider: string, quote: string}
     */
    public function quote(float $latitude, float $longitude, int $customerId): array
    {
        $distanceMeters = $this->locationProvider->drivingDistance(
            (float) config('delivery.store.latitude'),
            (float) config('delivery.store.longitude'),
            $latitude,
            $longitude,
        );

        $this->ensureWithinDeliveryArea($distanceMeters);

        $deliveryFee = $this->calculateFee($distanceMeters);
        $payload = [
            'calculated_at' => now()->toIso8601String(),
            'customer_id' => $customerId,
            'delivery_fee' => $deliveryFee,
            'distance_meters' => $distanceMeters,
            'expires_at' => now()->addMinutes((int) config('delivery.quote_ttl_minutes'))->timestamp,
            'latitude' => round($latitude, 7),
            'longitude' => round($longitude, 7),
            'provider' => 'openrouteservice',
        ];

        return [
            'delivery_fee' => $deliveryFee,
            'distance_km' => round($distanceMeters / 1000, 1),
            'distance_meters' => $distanceMeters,
            'latitude' => $payload['latitude'],
            'longitude' => $payload['longitude'],
            'provider' => $payload['provider'],
            'quote' => Crypt::encryptString(json_encode($payload, JSON_THROW_ON_ERROR)),
        ];
    }

    /**
     * @return array{calculated_at: string, customer_id: int, delivery_fee: int, distance_meters: int, expires_at: int, latitude: float, longitude: float, provider: string}
     */
    public function resolveQuote(string $token, int $customerId, float $latitude, float $longitude): array
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true, flags: JSON_THROW_ON_ERROR);
        } catch (DecryptException|JsonException) {
            throw $this->invalidQuoteException();
        }

        if (
            ! is_array($payload)
            || (int) data_get($payload, 'customer_id') !== $customerId
            || (int) data_get($payload, 'expires_at') < now()->timestamp
            || round((float) data_get($payload, 'latitude'), 7) !== round($latitude, 7)
            || round((float) data_get($payload, 'longitude'), 7) !== round($longitude, 7)
            || data_get($payload, 'provider') !== 'openrouteservice'
        ) {
            throw $this->invalidQuoteException();
        }

        $distanceMeters = (int) data_get($payload, 'distance_meters');
        $this->ensureWithinDeliveryArea($distanceMeters);

        if ((int) data_get($payload, 'delivery_fee') !== $this->calculateFee($distanceMeters)) {
            throw $this->invalidQuoteException();
        }

        return [
            'calculated_at' => (string) $payload['calculated_at'],
            'customer_id' => (int) $payload['customer_id'],
            'delivery_fee' => (int) $payload['delivery_fee'],
            'distance_meters' => $distanceMeters,
            'expires_at' => (int) $payload['expires_at'],
            'latitude' => (float) $payload['latitude'],
            'longitude' => (float) $payload['longitude'],
            'provider' => (string) $payload['provider'],
        ];
    }

    public function calculateFee(int $distanceMeters): int
    {
        $includedMeters = (int) config('delivery.included_distance_km') * 1000;
        $additionalKilometers = max(0, (int) ceil(($distanceMeters - $includedMeters) / 1000));

        return (int) config('delivery.flat_fee') + ($additionalKilometers * (int) config('delivery.per_km_fee'));
    }

    private function ensureWithinDeliveryArea(int $distanceMeters): void
    {
        if ($distanceMeters <= 0) {
            throw $this->invalidQuoteException();
        }

        if ($distanceMeters > (int) config('delivery.max_distance_km') * 1000) {
            throw ValidationException::withMessages([
                'delivery_location' => __('store.validation.messages.delivery_out_of_range', [
                    'distance' => round($distanceMeters / 1000, 1),
                    'maximum' => config('delivery.max_distance_km'),
                ]),
            ]);
        }
    }

    private function invalidQuoteException(): ValidationException
    {
        return ValidationException::withMessages([
            'delivery_quote' => __('store.validation.messages.delivery_quote_invalid'),
        ]);
    }
}
