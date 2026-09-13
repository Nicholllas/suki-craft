<?php

use App\Contracts\LocationProvider;
use App\Models\Customer;
use App\Services\DeliveryPricingService;
use App\Services\OpenRouteServiceLocationProvider;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    config([
        'delivery.flat_fee' => 17000,
        'delivery.included_distance_km' => 5,
        'delivery.max_distance_km' => 20,
        'delivery.per_km_fee' => 3000,
        'delivery.quote_ttl_minutes' => 15,
        'delivery.store.latitude' => -6.331293710176894,
        'delivery.store.longitude' => 107.01874632883566,
        'services.openrouteservice.base_url' => 'https://api.heigit.org',
        'services.openrouteservice.key' => 'test-api-key',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

test('delivery fee uses the base rate through five kilometers and rounds each additional kilometer up', function (int $distanceMeters, int $expectedFee) {
    $service = new DeliveryPricingService(locationProviderReturning($distanceMeters));

    expect($service->calculateFee($distanceMeters))->toBe($expectedFee);
})->with([
    'one kilometer' => [1000, 17000],
    'exactly five kilometers' => [5000, 17000],
    'one meter over five kilometers' => [5001, 20000],
    'exactly six kilometers' => [6000, 20000],
    'seven point two kilometers' => [7200, 26000],
]);

test('delivery quote rejects a route beyond twenty kilometers', function () {
    $service = new DeliveryPricingService(locationProviderReturning(20001));

    expect(fn () => $service->quote(-6.2, 107.1, 10))
        ->toThrow(ValidationException::class, 'melebihi batas maksimal 20 km');
});

test('delivery quote is bound to its customer coordinates and expiry', function () {
    Carbon::setTestNow('2026-09-13 10:00:00');
    $service = new DeliveryPricingService(locationProviderReturning(7200));
    $quote = $service->quote(-6.2500001, 107.0500001, 10);

    $resolved = $service->resolveQuote($quote['quote'], 10, -6.2500001, 107.0500001);

    expect($resolved['delivery_fee'])->toBe(26000)
        ->and($resolved['distance_meters'])->toBe(7200);

    expect(fn () => $service->resolveQuote($quote['quote'], 11, -6.2500001, 107.0500001))
        ->toThrow(ValidationException::class);
    expect(fn () => $service->resolveQuote($quote['quote'], 10, -6.2500001, 107.0600001))
        ->toThrow(ValidationException::class);

    Carbon::setTestNow(now()->addMinutes(16));

    expect(fn () => $service->resolveQuote($quote['quote'], 10, -6.2500001, 107.0500001))
        ->toThrow(ValidationException::class);
});

test('authenticated customers can request a delivery quote', function () {
    $customer = Customer::factory()->create();
    $this->app->instance(LocationProvider::class, locationProviderReturning(4999));

    $this->actingAs($customer, 'customer')->postJson(route('checkout.location.quote'), [
        'latitude' => -6.25,
        'longitude' => 107.05,
    ])->assertOk()
        ->assertJsonPath('delivery_fee', 17000)
        ->assertJsonPath('distance_km', 5)
        ->assertJsonPath('distance_meters', 4999)
        ->assertJsonStructure(['quote']);
});

test('openrouteservice requests driving distance with longitude before latitude', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.heigit.org/openrouteservice/v2/directions/driving-car/json' => Http::response([
            'routes' => [['summary' => ['distance' => 6432.1]]],
        ]),
    ]);

    $distance = app(OpenRouteServiceLocationProvider::class)->drivingDistance(
        -6.331293710176894,
        107.01874632883566,
        -6.25,
        107.05,
    );

    expect($distance)->toBe(6433);
    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://api.heigit.org/openrouteservice/v2/directions/driving-car/json'
            && $request->hasHeader('Authorization', 'test-api-key')
            && $request->data()['coordinates'] === [
                [107.01874632883566, -6.331293710176894],
                [107.05, -6.25],
            ];
    });
});

test('openrouteservice maps autocomplete results into delivery locations', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.heigit.org/pelias/v1/autocomplete*' => Http::response([
            'features' => [[
                'geometry' => ['coordinates' => [107.1, -6.2]],
                'properties' => ['label' => 'Jalan Mawar, Bekasi'],
            ]],
        ]),
    ]);

    $locations = app(OpenRouteServiceLocationProvider::class)->search('Jalan Mawar');

    expect($locations)->toBe([[
        'address' => 'Jalan Mawar, Bekasi',
        'latitude' => -6.2,
        'longitude' => 107.1,
    ]]);
});

function locationProviderReturning(int $distanceMeters): LocationProvider
{
    return new class($distanceMeters) implements LocationProvider
    {
        public function __construct(private int $distanceMeters) {}

        public function search(string $query): array
        {
            return [];
        }

        public function reverse(float $latitude, float $longitude): ?string
        {
            return null;
        }

        public function drivingDistance(
            float $originLatitude,
            float $originLongitude,
            float $destinationLatitude,
            float $destinationLongitude,
        ): int {
            return $this->distanceMeters;
        }
    };
}
