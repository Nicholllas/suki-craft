<?php

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItemGroup;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->category = Category::create([
        'is_active' => true,
        'name' => 'Buket Bunga',
        'slug' => 'buket-bunga',
    ]);
    $this->product = Product::create([
        'base_price' => 150000,
        'category_id' => $this->category->id,
        'cost_price' => 0,
        'description' => 'Buket untuk momen spesial.',
        'is_active' => true,
        'is_featured' => false,
        'name' => 'Buket Mawar',
        'price' => 150000,
        'slug' => 'buket-mawar',
        'stock' => 10,
    ]);
    $this->variant = $this->product->variants()->create([
        'is_active' => true,
        'label' => 'Large',
        'price_adjustment' => 25000,
        'sku' => 'MAWAR-L',
    ]);
    $this->customer = Customer::factory()->create();
});

afterEach(function () {
    Carbon::setTestNow();
});

test('a customer can checkout with server-calculated snapshots and view the confirmation', function () {
    config(['delivery.flat_fee' => 20000]);

    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'card_message' => 'Selamat ulang tahun!',
        'product_id' => $this->product->id,
        'bundle_quantity' => 2,
        'selected_variants' => [$this->variant->id => 1],
        'special_note' => 'Dominan warna putih.',
        'unit_price' => 1,
    ])->assertRedirect();

    $response = $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData());
    $order = Order::query()->with(['itemGroups.variants', 'statusHistories'])->sole();

    $response->assertRedirect(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]));
    expect($order->status)->toBe(OrderStatus::PENDING_PAYMENT)
        ->and($order->customer_id)->toBe($this->customer->id)
        ->and((float) $order->subtotal)->toBe(350000.0)
        ->and((float) $order->delivery_fee)->toBe(20000.0)
        ->and((float) $order->total)->toBe(370000.0)
        ->and($order->itemGroups)->toHaveCount(1)
        ->and($order->statusHistories)->toHaveCount(1);

    $orderItemGroup = $order->itemGroups->sole();
    expect($orderItemGroup->product_name)->toBe('Buket Mawar')
        ->and($orderItemGroup->bundle_quantity)->toBe(2)
        ->and((float) $orderItemGroup->subtotal)->toBe(350000.0)
        ->and($orderItemGroup->variants->sole()->variant_label)->toBe('Large')
        ->and($orderItemGroup->variants->sole()->product_variant_id)->toBe($this->variant->id)
        ->and($orderItemGroup->variants->sole()->quantity_in_bundle)->toBe(1)
        ->and((float) $orderItemGroup->variants->sole()->unit_price)->toBe(25000.0);

    $cart = Cart::query()->where('customer_id', $this->customer->id)->sole();
    expect($cart->itemGroups()->count())->toBe(0);

    $this->get(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]))
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Menunggu pembayaran')
        ->assertSee('Selesaikan pembayaran sebelum');
    $this->get(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => fake()->uuid()]))->assertNotFound();
});

test('checkout snapshots every selected quantity-based variant and keeps the full subtotal', function () {
    config(['delivery.flat_fee' => 0]);
    $this->product->update(['allow_multiple_variants' => true, 'base_price' => 100000]);
    $this->product->variants()->delete();

    $selectedVariants = collect([1000, 2000, 5000, 10000, 20000, 50000, 100000])->mapWithKeys(function (int $priceAdjustment): array {
        $variant = $this->product->variants()->create([
            'is_active' => true,
            'is_quantity_based' => true,
            'label' => 'Pecahan Rp'.number_format($priceAdjustment, 0, ',', '.'),
            'price_adjustment' => $priceAdjustment,
            'sku' => 'MONEY-'.$priceAdjustment,
        ]);

        return [$variant->id => 1];
    })->all();

    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'bundle_quantity' => 1,
        'product_id' => $this->product->id,
        'selected_variants' => $selectedVariants,
    ])->assertRedirect();

    $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData())->assertRedirect();

    $order = Order::query()->with('itemGroups.variants')->sole();

    expect((float) $order->subtotal)->toBe(288000.0)
        ->and((float) $order->total)->toBe(288000.0)
        ->and($order->itemGroups->sole()->variants)->toHaveCount(7)
        ->and((float) $order->itemGroups->sole()->variants->sum('line_subtotal'))->toBe(188000.0);
});

test('checkout rejects past delivery dates', function () {
    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'bundle_quantity' => 1,
        'selected_variants' => [$this->variant->id => 1],
    ])->assertRedirect();

    $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData(['delivery_date' => now('Asia/Jakarta')->subDay()->toDateString()]))
        ->assertSessionHasErrors('delivery_date');
    expect(Order::query()->doesntExist())->toBeTrue();
});

test('checkout rejects a delivery slot that has already ended today in Jakarta time', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-18 14:00', 'Asia/Jakarta'));

    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'bundle_quantity' => 1,
        'selected_variants' => [$this->variant->id => 1],
    ])->assertRedirect();

    $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData([
        'delivery_date' => '2026-08-18',
        'delivery_time_slot' => '09:00-12:00',
    ]))->assertSessionHasErrors(['delivery_time_slot' => 'Slot waktu ini sudah tidak tersedia untuk hari ini, silakan pilih slot lain.']);

    expect(Order::query()->doesntExist())->toBeTrue();
});

test('a guest can checkout without attaching the order to a customer account', function () {
    $session = app('session')->driver();
    $session->start();
    $request = Request::create('/');
    $request->setLaravelSession($session);
    app()->instance('request', $request);
    $cart = Cart::query()->create(['session_id' => $session->getId()]);
    $group = $cart->itemGroups()->create([
        'product_id' => $this->product->id,
        'bundle_quantity' => 1,
    ]);
    $group->variants()->create(['product_variant_id' => $this->variant->id, 'quantity_in_bundle' => 1, 'unit_price' => 25000]);

    $order = app(OrderService::class)->createFromCart(checkoutData());

    expect($order->customer_id)->toBeNull();
});

test('an empty cart redirects customers back to their cart', function () {
    $this->actingAs($this->customer, 'customer')->get(route('checkout.index'))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('error', 'Tambahkan buket ke keranjang sebelum checkout.');

    $this->withSession(['error' => 'Tambahkan buket ke keranjang sebelum checkout.'])
        ->get(route('cart.index'))
        ->assertSee('Tambahkan buket ke keranjang sebelum checkout.');
});

test('order factories create relational snapshots', function () {
    $orderItem = OrderItemGroup::factory()->create();
    $history = OrderStatusHistory::factory()->create();

    expect($orderItem->order)->not->toBeNull()
        ->and($orderItem->product)->not->toBeNull()
        ->and($history->order)->not->toBeNull()
        ->and($history->status)->toBe(OrderStatus::PENDING_PAYMENT);
});

function checkoutData(array $overrides = []): array
{
    return [
        'customer_email' => 'penerima@example.com',
        'customer_name' => 'Nadia Putri',
        'customer_phone' => '081234567890',
        'delivery_address' => 'Jl. Mawar No. 10, Jakarta Selatan',
        'delivery_date' => today()->addDay()->toDateString(),
        'delivery_time_slot' => '12:00-15:00',
        'notes' => 'Hubungi sebelum tiba.',
        ...$overrides,
    ];
}
