<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $cart = $this->cartService->getCurrentCart();

        if (! $cart || $cart->items()->doesntExist()) {
            return redirect()->route('cart.index')->with('error', 'Tambahkan buket ke keranjang sebelum checkout.');
        }

        $cart->load(['items.product.category', 'items.product.images', 'items.variant']);

        return view('checkout.index', [
            'cart' => $cart,
            'customer' => $request->user('customer'),
            'deliveryFee' => (float) config('delivery.flat_fee', 0),
            'minimumDeliveryDate' => today()->addDay()->toDateString(),
            'subtotal' => $this->cartService->getTotal(),
            'timeSlots' => config('delivery.time_slots', []),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $order = $this->orderService->createFromCart($request->validated());

        return redirect()
            ->route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token])
            ->with('success', 'Pesanan berhasil dibuat. Silakan lanjutkan pembayaran.');
    }
}
