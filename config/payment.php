<?php

return [
    'bank_account_holder' => env('PAYMENT_BANK_ACCOUNT_HOLDER', ''),
    'bank_account_number' => env('PAYMENT_BANK_ACCOUNT_NUMBER', ''),
    'bank_name' => env('PAYMENT_BANK_NAME', ''),
    'qris_payload' => env('PAYMENT_QRIS_PAYLOAD', ''),
    'qris_path' => env('PAYMENT_QRIS_PATH', 'images/qris-suki-craft.jpeg'),
    'whatsapp_number' => env('PAYMENT_WHATSAPP_NUMBER', ''),
];
