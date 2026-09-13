<?php

namespace App\Contracts;

interface LocationProvider
{
    /**
     * @return list<array{address: string, latitude: float, longitude: float}>
     */
    public function search(string $query): array;

    public function reverse(float $latitude, float $longitude): ?string;

    public function drivingDistance(
        float $originLatitude,
        float $originLongitude,
        float $destinationLatitude,
        float $destinationLongitude,
    ): int;
}
