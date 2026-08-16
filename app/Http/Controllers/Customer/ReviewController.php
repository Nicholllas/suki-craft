<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviewService) {}

    public function storeForCustomer(StoreReviewRequest $request, Order $order, OrderItem $orderItem): RedirectResponse
    {
        $customer = $request->user('customer');

        abort_unless($customer instanceof Customer, 403);
        $this->ensureOrderItemBelongsToOrder($order, $orderItem);
        abort_unless($order->customer_id === $customer->id, 404);

        $this->reviewService->submit($orderItem, $request->validated(), $customer);

        return redirect()->route('customer.orders.show', $order)->with('success', 'Ulasan kamu sedang menunggu moderasi.');
    }

    public function storeForTracking(StoreReviewRequest $request, Order $order, OrderItem $orderItem): RedirectResponse
    {
        abort_unless(session('tracked_order_id') === $order->id, 404);
        $this->ensureOrderItemBelongsToOrder($order, $orderItem);

        $this->reviewService->submit($orderItem, $request->validated());

        return redirect()->route('tracking.show', $order)->with('success', 'Ulasan kamu sedang menunggu moderasi.');
    }

    private function ensureOrderItemBelongsToOrder(Order $order, OrderItem $orderItem): void
    {
        abort_unless($orderItem->order_id === $order->id, 404);
    }
}
