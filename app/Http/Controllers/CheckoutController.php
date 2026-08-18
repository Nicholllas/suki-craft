<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PromotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
        private PromotionService $promotionService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $cart = $this->cartService->getCurrentCart();

        if (! $cart || $cart->itemGroups()->doesntExist()) {
            return redirect()->route('cart.index')->with('error', 'Tambahkan buket ke keranjang sebelum checkout.');
        }

        $cart->load(['itemGroups.product.category', 'itemGroups.product.images', 'itemGroups.variants.productVariant']);

        return view('checkout.index', [
            'cart' => $cart,
            'customer' => $request->user('customer'),
            'deliveryFee' => (float) config('delivery.flat_fee', 0),
            'minimumDeliveryDate' => now('Asia/Jakarta')->toDateString(),
            'subtotal' => $this->cartService->getTotal(),
            'timeSlots' => config('delivery.time_slots', []),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $order = $this->orderService->createFromCart($request->validated(), $request->validated('promotion_code') ?: $request->session()->get('checkout.promotion_code'));
        $request->session()->forget('checkout.promotion_code');

        return redirect()
            ->route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token])
            ->with('success', 'Pesanan berhasil dibuat. Silakan lanjutkan pembayaran.');
    }

    public function validatePromotion(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:50'], 'customer_phone' => ['nullable', 'string', 'max:25']]);
        $cart = $this->cartService->getCurrentCart();

        if (! $cart || $cart->itemGroups()->doesntExist()) {
            return response()->json(['message' => 'Keranjang belanja Anda masih kosong.'], 422);
        }

        $subtotal = $this->cartService->getTotal();
        $promotion = $this->promotionService->validate($data['code'], $subtotal, $data['customer_phone'] ?? null, $request->user('customer')?->id);
        $discountAmount = $this->promotionService->calculateDiscount($promotion, $subtotal);
        $request->session()->put('checkout.promotion_code', $promotion->code);

        return response()->json(['code' => $promotion->code, 'discount_amount' => $discountAmount, 'total' => $subtotal + (float) config('delivery.flat_fee', 0) - $discountAmount]);
    }
}
