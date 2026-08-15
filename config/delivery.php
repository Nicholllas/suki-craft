<?php

return [
    'flat_fee' => (int) env('DELIVERY_FLAT_FEE', 15000),
    'time_slots' => [
        '09:00-12:00' => 'Pagi, 09.00–12.00',
        '12:00-15:00' => 'Siang, 12.00–15.00',
        '15:00-18:00' => 'Sore, 15.00–18.00',
    ],
];
