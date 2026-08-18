<?php

return [
    'api_key' => env('BITESHIP_API_KEY'),
    'base_url' => env('BITESHIP_BASE_URL', 'https://api.biteship.com/v1'),
    'ca_bundle' => env('BITESHIP_CA_BUNDLE'),
    'couriers' => array_values(array_filter(explode(',', env('BITESHIP_COURIERS', 'jne,jnt,sicepat,paxel')))),
    'courier_names' => [
        'jne' => 'JNE',
        'jnt' => 'J&T Express',
        'paxel' => 'Paxel',
        'sicepat' => 'SiCepat',
    ],
    'origin' => [
        'area_id' => env('BITESHIP_ORIGIN_AREA_ID'),
        'contact_name' => env('BITESHIP_ORIGIN_CONTACT_NAME'),
        'contact_phone' => env('BITESHIP_ORIGIN_CONTACT_PHONE'),
        'address' => env('BITESHIP_ORIGIN_ADDRESS'),
        'postal_code' => env('BITESHIP_ORIGIN_POSTAL_CODE'),
    ],
];
