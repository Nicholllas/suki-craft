<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PromotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
        if (! auth('customer')->check()) {
            return $this->redirectToAccountRequirement();
        }

        $cart = $this->cartService->getCurrentCart();

        if (! $cart || $cart->itemGroups()->doesntExist()) {
            return redirect()->route('cart.index')->with('error', 'Tambahkan buket ke keranjang sebelum checkout.');
        }

        if (! $request->session()->has('checkout.idempotency_token')) {
            $request->session()->put('checkout.idempotency_token', (string) Str::uuid());
        }
        $cart->load(['itemGroups.bouquetSize', 'itemGroups.customRequest', 'itemGroups.product.category', 'itemGroups.product.images', 'itemGroups.variants.productVariant']);
        $subtotal = $this->cartService->getTotal();
        $deliveryFee = (float) config('delivery.flat_fee', 0);

        return view('checkout.index', [
            'cart' => $cart,
            'checkoutPricing' => $this->sessionPromotionPricing($request, $subtotal, $deliveryFee),
            'customer' => $request->user('customer'),
            'deliveryFee' => $deliveryFee,
            'minimumDeliveryDate' => now('Asia/Jakarta')->toDateString(),
            'subtotal' => $subtotal,
            'timeSlots' => config('delivery.time_slots', []),
        ]);
    }

    public function requireAccount(): View
    {
        $cart = $this->cartService->getCurrentCart();

        return view('checkout.require-account', [
            'itemCount' => (int) ($cart?->itemGroups()->sum('bundle_quantity') ?? 0),
            'subtotal' => $this->cartService->getTotal(),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        if (! auth('customer')->check()) {
            return $this->redirectToAccountRequirement();
        }

        $order = $this->orderService->createFromCart($request->validated(), $request->validated('promotion_code') ?: $request->session()->get('checkout.promotion_code'));
        $request->session()->forget(['checkout.idempotency_token', 'checkout.promotion_code']);

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
        $request->session()->put('checkout.promotion_code', $promotion->code);

        return response()->json($this->promotionService->checkoutPricing($promotion, $subtotal, (float) config('delivery.flat_fee', 0)));
    }

    private function redirectToAccountRequirement(): RedirectResponse
    {
        redirect()->setIntendedUrl(route('checkout.index'));

        return redirect()->route('checkout.require-account');
    }

    private function sessionPromotionPricing(Request $request, float $subtotal, float $deliveryFee): array
    {
        $pricing = $this->promotionService->checkoutPricing(null, $subtotal, $deliveryFee);
        $promotionCode = $request->session()->get('checkout.promotion_code');

        if (blank($promotionCode)) {
            return $pricing;
        }

        try {
            $customer = $request->user('customer');
            $promotion = $this->promotionService->validate($promotionCode, $subtotal, $customer?->phone, $customer?->id);

            return $this->promotionService->checkoutPricing($promotion, $subtotal, $deliveryFee);
        } catch (ValidationException) {
            $request->session()->forget('checkout.promotion_code');

            return [...$pricing, 'message' => __('storefront.checkout.promo_no_longer_valid')];
        }
    }
}
