<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\CheckoutShippingAreaRequest;
use App\Http\Requests\CheckoutShippingOptionRequest;
use App\Http\Requests\CheckoutShippingRateRequest;
use App\Services\BiteshipService;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PromotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private BiteshipService $biteshipService,
        private OrderService $orderService,
        private PromotionService $promotionService,
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
            'deliveryFee' => (float) ($request->session()->get('checkout.shipping_selection.delivery_fee') ?? 0),
            'minimumDeliveryDate' => today()->addDay()->toDateString(),
            'shippingSelection' => $request->session()->get('checkout.shipping_selection'),
            'subtotal' => $this->cartService->getTotal(),
            'timeSlots' => config('delivery.time_slots', []),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $order = $this->orderService->createFromCart(
            $request->validated(),
            $request->validated('promotion_code') ?: $request->session()->get('checkout.promotion_code'),
            $request->session()->get('checkout.shipping_selection'),
        );
        $request->session()->forget('checkout.promotion_code');
        $request->session()->forget(['checkout.shipping_rates', 'checkout.shipping_selection']);

        return redirect()
            ->route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token])
            ->with('success', 'Pesanan berhasil dibuat. Silakan lanjutkan pembayaran.');
    }

    public function validatePromotion(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:50'], 'customer_phone' => ['nullable', 'string', 'max:25']]);
        $cart = $this->cartService->getCurrentCart();

        if (! $cart || $cart->items()->doesntExist()) {
            return response()->json(['message' => 'Keranjang belanja Anda masih kosong.'], 422);
        }

        $subtotal = $this->cartService->getTotal();
        $promotion = $this->promotionService->validate($data['code'], $subtotal, $data['customer_phone'] ?? null, $request->user('customer')?->id);
        $discountAmount = $this->promotionService->calculateDiscount($promotion, $subtotal);
        $request->session()->put('checkout.promotion_code', $promotion->code);

        $deliveryFee = (float) ($request->session()->get('checkout.shipping_selection.delivery_fee') ?? 0);

        return response()->json(['code' => $promotion->code, 'discount_amount' => $discountAmount, 'total' => $subtotal + $deliveryFee - $discountAmount]);
    }

    public function searchShippingAreas(CheckoutShippingAreaRequest $request): JsonResponse
    {
        return response()->json(['areas' => $this->biteshipService->searchAreas($request->validated('input'))]);
    }

    public function shippingRates(CheckoutShippingRateRequest $request): JsonResponse
    {
        $cart = $this->cartService->getCurrentCart();

        if (! $cart || $cart->items()->doesntExist()) {
            return response()->json(['message' => 'Keranjang belanja Anda masih kosong.'], 422);
        }

        $data = $request->validated();

        try {
            $rates = $this->biteshipService->getRates($data['destination_area_id'], $this->cartService->getShipmentWeightGrams());
            $isFallback = $rates === [];
        } catch (\Throwable $exception) {
            report($exception);
            $rates = [];
            $isFallback = true;
        }

        if ($isFallback) {
            $rates = [$this->fallbackShippingRate()];
        }

        $request->session()->put('checkout.shipping_rates', [
            'destination_area_id' => $data['destination_area_id'],
            'destination_postal_code' => $data['destination_postal_code'] ?? null,
            'rates' => $rates,
        ]);

        return response()->json([
            'fallback' => $isFallback,
            'rates' => $rates,
        ]);
    }

    public function selectShippingOption(CheckoutShippingOptionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $shippingRates = $request->session()->get('checkout.shipping_rates');
        $rate = collect($shippingRates['rates'] ?? [])->first(fn (array $rate): bool => $rate['company'] === $data['courier_company'] && $rate['service'] === $data['courier_service']);

        if (! $shippingRates || ! $rate) {
            throw ValidationException::withMessages(['shipping' => 'Pilihan ongkir sudah tidak tersedia. Silakan cek ongkir kembali.']);
        }

        $selection = [
            'courier_company' => $rate['company'],
            'courier_name' => $rate['courier'],
            'courier_service' => $rate['service'],
            'courier_service_name' => $rate['service_name'],
            'delivery_fee' => $rate['price'],
            'destination_area_id' => $shippingRates['destination_area_id'],
            'destination_postal_code' => $shippingRates['destination_postal_code'],
            'estimated_days' => $rate['estimated_days'],
        ];
        $request->session()->put('checkout.shipping_selection', $selection);

        return response()->json(['shipping' => $selection]);
    }

    private function fallbackShippingRate(): array
    {
        return [
            'company' => 'flat_rate',
            'courier' => 'Pengiriman Suki Craft',
            'service' => 'flat',
            'service_name' => 'Tarif pengiriman standar',
            'price' => (int) config('delivery.flat_fee', 0),
            'estimated_days' => 'Akan dikonfirmasi',
        ];
    }
}
