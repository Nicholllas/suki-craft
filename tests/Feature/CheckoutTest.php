<?php

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Services\OrderService;
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

test('a customer can checkout with server-calculated snapshots and view the confirmation', function () {
    config(['delivery.flat_fee' => 20000]);

    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'card_message' => 'Selamat ulang tahun!',
        'product_id' => $this->product->id,
        'quantity' => 2,
        'special_note' => 'Dominan warna putih.',
        'unit_price' => 1,
        'variant_id' => $this->variant->id,
    ])->assertRedirect();

    $response = $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData());
    $order = Order::query()->with(['items', 'statusHistories'])->sole();

    $response->assertRedirect(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]));
    expect($order->status)->toBe(OrderStatus::PENDING_PAYMENT)
        ->and($order->customer_id)->toBe($this->customer->id)
        ->and((float) $order->subtotal)->toBe(350000.0)
        ->and((float) $order->delivery_fee)->toBe(20000.0)
        ->and((float) $order->total)->toBe(370000.0)
        ->and($order->items)->toHaveCount(1)
        ->and($order->statusHistories)->toHaveCount(1);

    $orderItem = $order->items->sole();
    expect($orderItem->product_name)->toBe('Buket Mawar')
        ->and($orderItem->variant_label)->toBe('Large')
        ->and($orderItem->quantity)->toBe(2)
        ->and((float) $orderItem->unit_price)->toBe(175000.0)
        ->and((float) $orderItem->subtotal)->toBe(350000.0);

    $cart = Cart::query()->where('customer_id', $this->customer->id)->sole();
    expect($cart->items()->count())->toBe(0);

    $this->get(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]))
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Menunggu pembayaran');
    $this->get(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => fake()->uuid()]))->assertNotFound();
});

test('checkout requires a delivery date from tomorrow onward', function () {
    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'quantity' => 1,
        'variant_id' => $this->variant->id,
    ])->assertRedirect();

    $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData(['delivery_date' => today()->toDateString()]))
        ->assertSessionHasErrors('delivery_date');
    expect(Order::query()->doesntExist())->toBeTrue();
});

test('a guest can checkout without attaching the order to a customer account', function () {
    $session = app('session')->driver();
    $session->start();
    $request = Request::create('/');
    $request->setLaravelSession($session);
    app()->instance('request', $request);
    $cart = Cart::query()->create(['session_id' => $session->getId()]);
    $cart->items()->create([
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 1,
        'unit_price' => 175000,
    ]);

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
    $orderItem = OrderItem::factory()->create();
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
