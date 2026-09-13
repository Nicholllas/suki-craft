<?php

return [
    'flat_fee' => (int) env('DELIVERY_BASE_FEE', 17000),
    'included_distance_km' => (int) env('DELIVERY_INCLUDED_DISTANCE_KM', 5),
    'max_distance_km' => (int) env('DELIVERY_MAX_DISTANCE_KM', 20),
    'per_km_fee' => (int) env('DELIVERY_PER_KM_FEE', 3000),
    'quote_ttl_minutes' => (int) env('DELIVERY_QUOTE_TTL_MINUTES', 15),
    'store' => [
        'latitude' => (float) env('STORE_LATITUDE', -6.331293710176894),
        'longitude' => (float) env('STORE_LONGITUDE', 107.01874632883566),
    ],
    'same_day_prep_hours' => (int) env('DELIVERY_SAME_DAY_PREP_HOURS', 4),
    'time_slots' => [
        '09:00-12:00' => ['key' => '09:00-12:00', 'label' => 'Pagi, 09.00-12.00', 'start_time' => '09:00', 'end_time' => '12:00'],
        '12:00-15:00' => ['key' => '12:00-15:00', 'label' => 'Siang, 12.00-15.00', 'start_time' => '12:00', 'end_time' => '15:00'],
        '15:00-18:00' => ['key' => '15:00-18:00', 'label' => 'Sore, 15.00-18.00', 'start_time' => '15:00', 'end_time' => '18:00'],
    ],
];
