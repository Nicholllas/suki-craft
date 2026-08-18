<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function confirmation(string $orderNumber, string $token): View
    {
        $order = Order::query()
            ->with('itemGroups.variants')
            ->where('order_number', $orderNumber)
            ->where('public_token', $token)
            ->firstOrFail();

        return view('orders.confirmation', ['order' => $order]);
    }
}
