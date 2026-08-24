<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class OrderWhatsAppService
{
    public function followUpUrl(Order $order): ?string
    {
        $number = Str::of((string) config('payment.whatsapp_number'))
            ->replaceMatches('/\D+/', '')
            ->toString();

        if (blank($number)) {
            return null;
        }

        $message = 'Halo Suki Craft, saya ingin menindaklanjuti pesanan '.$order->order_number
            .' dengan total Rp'.number_format((float) $order->total, 0, ',', '.').'.';

        return 'https://wa.me/'.$number.'?text='.urlencode($message);
    }
}
