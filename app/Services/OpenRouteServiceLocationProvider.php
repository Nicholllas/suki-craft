<?php

namespace App\Services;

use App\Contracts\LocationProvider;
use App\Exceptions\OpenRouteServiceException;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class OpenRouteServiceLocationProvider implements LocationProvider
{
    public function search(string $query): array
    {
        $response = $this->send(function (PendingRequest $request) use ($query): Response {
            return $request->get('/pelias/v1/autocomplete', [
                'boundary.country' => 'ID',
                'focus.point.lat' => config('delivery.store.latitude'),
                'focus.point.lon' => config('delivery.store.longitude'),
                'size' => 5,
                'text' => $query,
            ]);
        });

        return collect($response->json('features', []))
            ->map(function (array $feature): ?array {
                $coordinates = data_get($feature, 'geometry.coordinates');
                $address = data_get($feature, 'properties.label');

                if (! is_array($coordinates) || count($coordinates) < 2 || ! is_string($address)) {
                    return null;
                }

                return [
                    'address' => $address,
                    'latitude' => (float) $coordinates[1],
                    'longitude' => (float) $coordinates[0],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function reverse(float $latitude, float $longitude): ?string
    {
        $response = $this->send(fn (PendingRequest $request): Response => $request->get('/pelias/v1/reverse', [
            'point.lat' => $latitude,
            'point.lon' => $longitude,
            'size' => 1,
        ]));

        $address = $response->json('features.0.properties.label');

        return is_string($address) ? $address : null;
    }

    public function drivingDistance(
        float $originLatitude,
        float $originLongitude,
        float $destinationLatitude,
        float $destinationLongitude,
    ): int {
        $response = $this->send(fn (PendingRequest $request): Response => $request->post(
            '/openrouteservice/v2/directions/driving-car/json',
            [
                'coordinates' => [
                    [$originLongitude, $originLatitude],
                    [$destinationLongitude, $destinationLatitude],
                ],
            ],
        ));

        $distance = $response->json('routes.0.summary.distance');

        if (! is_numeric($distance)) {
            throw new OpenRouteServiceException('OpenRouteService returned an invalid route response.');
        }

        return (int) ceil((float) $distance);
    }

    private function client(): PendingRequest
    {
        $apiKey = config('services.openrouteservice.key');

        if (blank($apiKey)) {
            throw new OpenRouteServiceException('The OpenRouteService API key is not configured.');
        }

        return Http::baseUrl((string) config('services.openrouteservice.base_url'))
            ->acceptJson()
            ->asJson()
            ->withHeaders(['Authorization' => $apiKey])
            ->connectTimeout(3)
            ->timeout(8)
            ->retry(2, 250, function (Exception $exception): bool {
                if ($exception instanceof ConnectionException) {
                    return true;
                }

                return $exception instanceof RequestException
                    && ($exception->response->status() === 429 || $exception->response->serverError());
            }, throw: false);
    }

    private function send(callable $callback): Response
    {
        try {
            $response = $callback($this->client());
        } catch (Throwable $exception) {
            throw new OpenRouteServiceException('OpenRouteService could not be reached.', previous: $exception);
        }

        if ($response->failed()) {
            throw new OpenRouteServiceException(
                'OpenRouteService request failed with status '.$response->status().'.',
            );
        }

        return $response;
    }
}
