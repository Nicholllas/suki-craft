<?php

return [
    'flat_fee' => (int) env('DELIVERY_FLAT_FEE', 15000),
    'time_slots' => [
        '09:00-12:00' => ['key' => '09:00-12:00', 'label' => 'Pagi, 09.00–12.00', 'start_time' => '09:00', 'end_time' => '12:00'],
        '12:00-15:00' => ['key' => '12:00-15:00', 'label' => 'Siang, 12.00–15.00', 'start_time' => '12:00', 'end_time' => '15:00'],
        '15:00-18:00' => ['key' => '15:00-18:00', 'label' => 'Sore, 15.00–18.00', 'start_time' => '15:00', 'end_time' => '18:00'],
    ],
];
