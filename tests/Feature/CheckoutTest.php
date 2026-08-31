<?php

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItemGroup;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Promotion;
use Carbon\Carbon;

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
    config(['delivery.flat_fee' => 20000, 'payment.whatsapp_number' => '6281234567890']);

    $this->customer->update(['address' => 'Jl. Melati No. 12, Jakarta Selatan 12110']);
    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'card_message' => 'Selamat ulang tahun!',
        'product_id' => $this->product->id,
        'bundle_quantity' => 2,
        'selected_variants' => [$this->variant->id => 1],
        'special_note' => 'Dominan warna putih.',
        'unit_price' => 1,
    ])->assertRedirect();

    $this->actingAs($this->customer, 'customer')->get(route('checkout.index'))
        ->assertOk()
        ->assertSee('data-checkout-summary', false)
        ->assertSee('Jl. Melati No. 12, Jakarta Selatan 12110')
        ->assertSee('lg:sticky', false)
        ->assertDontSee('sticky bottom-3', false)
        ->assertSee('Biaya jasa merangkai')
        ->assertSee('Varian terpilih')
        ->assertSee('Total = subtotal buket + biaya pengiriman − potongan promo.')
        ->assertSee('name="idempotency_token"', false)
        ->assertSee('submitCheckout', false);

    $response = $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData());
    $order = Order::query()->with(['itemGroups.variants', 'statusHistories'])->sole();

    $response->assertRedirect(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]));
    expect($order->status)->toBe(OrderStatus::PENDING_PAYMENT)
        ->and($order->customer_id)->toBe($this->customer->id)
        ->and($order->customer_phone)->toBe('6281234567890')
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
        ->assertSee('Selesaikan pembayaran sebelum')
        ->assertSee('Detail buket')
        ->assertSee('Biaya jasa merangkai')
        ->assertSee('Total pembayaran')
        ->assertSee('Konfirmasi via WhatsApp');
    $this->get(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => fake()->uuid()]))->assertNotFound();
});

test('checkout stores accepted phone number variants in the canonical format', function (string $phone): void {
    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'bundle_quantity' => 1,
        'selected_variants' => [$this->variant->id => 1],
    ])->assertRedirect();

    $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData(['customer_phone' => $phone]))->assertRedirect();

    expect(Order::query()->sole()->customer_phone)->toBe('6281234567890');
})->with([
    'local format' => '081234567890',
    'international format' => '+6281234567890',
    'international format without plus' => '6281234567890',
    'separators' => '+62 812-3456 7890',
]);

test('checkout recomputes and displays the session promotion with server pricing', function () {
    config(['delivery.flat_fee' => 20000]);
    $promotion = Promotion::query()->create([
        'code' => 'HEMAT10',
        'expires_at' => now()->addDay(),
        'starts_at' => now()->subDay(),
        'type' => 'percentage',
        'value' => 10,
    ]);

    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'bundle_quantity' => 1,
        'selected_variants' => [$this->variant->id => 1],
    ])->assertRedirect();

    $this->actingAs($this->customer, 'customer')->withSession(['checkout.promotion_code' => $promotion->code])
        ->get(route('checkout.index'))
        ->assertOk()
        ->assertSee('Promo diterapkan:')
        ->assertSee('HEMAT10')
        ->assertSee('10%')
        ->assertSee('Total sebelum diskon')
        ->assertSee('Rp195.000')
        ->assertSee('177.500');

    $this->actingAs($this->customer, 'customer')->postJson(route('checkout.promotions.validate'), [
        'code' => $promotion->code,
        'customer_phone' => '6281234567890',
    ])->assertSuccessful()
        ->assertJsonPath('discount_amount', 17500)
        ->assertJsonPath('total', 177500)
        ->assertJsonPath('total_before_discount', 195000);

    $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData(['promotion_code' => $promotion->code]))->assertRedirect();

    expect((float) Order::query()->sole()->discount_amount)->toBe(17500.0)
        ->and((float) Order::query()->sole()->total)->toBe(177500.0);
});

test('checkout removes an invalid promotion from the session on reload', function () {
    $promotion = Promotion::query()->create([
        'code' => 'EXPIRED',
        'expires_at' => now()->subMinute(),
        'starts_at' => now()->subDay(),
        'type' => 'fixed',
        'value' => 10000,
    ]);

    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'bundle_quantity' => 1,
        'selected_variants' => [$this->variant->id => 1],
    ])->assertRedirect();

    $this->actingAs($this->customer, 'customer')->withSession(['checkout.promotion_code' => $promotion->code])
        ->get(route('checkout.index'))
        ->assertOk()
        ->assertSee('Promo tidak lagi berlaku.')
        ->assertSessionMissing('checkout.promotion_code');
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

test('repeated checkout submissions with the same idempotency token return the existing order', function () {
    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'bundle_quantity' => 1,
        'selected_variants' => [$this->variant->id => 1],
    ])->assertRedirect();

    $checkoutData = checkoutData();
    $firstResponse = $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), $checkoutData);
    $order = Order::query()->sole();

    $firstResponse->assertRedirect(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]));

    $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), $checkoutData)
        ->assertRedirect(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]));

    expect(Order::query()->count())->toBe(1);
});

test('checkout rejects a processed cart when a different submission token is used', function () {
    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'bundle_quantity' => 1,
        'selected_variants' => [$this->variant->id => 1],
    ])->assertRedirect();

    $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData())->assertRedirect();

    $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData(['idempotency_token' => fake()->uuid()]))
        ->assertSessionHasErrors(['cart' => 'Keranjang checkout ini sudah diproses menjadi pesanan. Silakan lanjutkan dari halaman konfirmasi pesanan.']);

    expect(Order::query()->count())->toBe(1);
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

test('checkout rejects a delivery slot after its same-day preparation cutoff in Jakarta time', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-18 13:00', 'Asia/Jakarta'));

    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'bundle_quantity' => 1,
        'selected_variants' => [$this->variant->id => 1],
    ])->assertRedirect();

    $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData([
        'delivery_date' => '2026-08-18',
        'delivery_time_slot' => '12:00-15:00',
    ]))->assertSessionHasErrors(['delivery_time_slot' => 'Slot waktu ini sudah tidak tersedia untuk hari ini, silakan pilih slot lain.']);

    expect(Order::query()->doesntExist())->toBeTrue();
});

test('checkout rejects a same-day slot at its preparation cutoff', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-18 09:00', 'Asia/Jakarta'));

    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'bundle_quantity' => 1,
        'selected_variants' => [$this->variant->id => 1],
    ])->assertRedirect();

    $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData([
        'delivery_date' => '2026-08-18',
        'delivery_time_slot' => '12:00-15:00',
    ]))->assertSessionHasErrors('delivery_time_slot');

    expect(Order::query()->doesntExist())->toBeTrue();
});

test('checkout accepts a same-day slot before its preparation cutoff', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-18 08:59', 'Asia/Jakarta'));

    $this->actingAs($this->customer, 'customer')->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'bundle_quantity' => 1,
        'selected_variants' => [$this->variant->id => 1],
    ])->assertRedirect();

    $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), checkoutData([
        'delivery_date' => '2026-08-18',
        'delivery_time_slot' => '12:00-15:00',
    ]))->assertRedirect();

    expect(Order::query()->exists())->toBeTrue();
});

test('guests are directed to create or sign in before checkout', function () {

    $this->get(route('checkout.index'))
        ->assertRedirect(route('checkout.require-account', absolute: false))
        ->assertSessionHas('url.intended', route('checkout.index'));

    $this->get(route('checkout.require-account'))
        ->assertSuccessful()
        ->assertSee('Cart kamu aman kok')
        ->assertSee('0 buket')
        ->assertSee('Rp0')
        ->assertSee('Daftar Akun Baru')
        ->assertSee('Sudah Punya Akun? Masuk');

    $this->post(route('checkout.store'), checkoutData())
        ->assertRedirect(route('checkout.require-account', absolute: false))
        ->assertSessionHas('url.intended', route('checkout.index'));

    expect(Order::query()->doesntExist())->toBeTrue();
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
        'idempotency_token' => session('checkout.idempotency_token', fake()->uuid()),
        'notes' => 'Hubungi sebelum tiba.',
        ...$overrides,
    ];
}
