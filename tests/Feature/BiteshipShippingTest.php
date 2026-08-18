<?php

use App\Enums\AdminRole;
use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'biteship.api_key' => 'biteship_test.key',
        'biteship.base_url' => 'https://api.biteship.test/v1',
        'biteship.couriers' => ['jne', 'jnt', 'sicepat', 'paxel'],
        'biteship.origin' => [
            'address' => 'Jl. Mawar No. 1, Jakarta Selatan',
            'area_id' => 'IDNP6IDNC148IDND843IDZ12250',
            'contact_name' => 'Suki Craft',
            'contact_phone' => '081234567890',
            'postal_code' => 12250,
        ],
    ]);
});

test('a customer can select a regular Biteship courier and checkout with its server-side rate', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['weight_grams' => 750]);
    $cart = Cart::query()->create(['customer_id' => $customer->id]);
    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 150000,
    ]);

    Http::fake([
        'https://api.biteship.test/v1/maps/areas*' => Http::response([
            'areas' => [[
                'id' => 'IDNP6IDNC148IDND843IDZ12250',
                'name' => 'Pesanggrahan, Jakarta Selatan, DKI Jakarta. 12250',
                'postal_code' => 12250,
            ]],
        ]),
        'https://api.biteship.test/v1/rates/couriers' => Http::response([
            'pricing' => [
                [
                    'courier_code' => 'jne',
                    'courier_name' => 'JNE',
                    'courier_service_code' => 'reg',
                    'courier_service_name' => 'Reguler',
                    'duration' => '2 - 3 days',
                    'price' => 18000,
                    'shipping_type' => 'parcel',
                ],
                [
                    'courier_code' => 'gosend',
                    'courier_name' => 'GoSend',
                    'courier_service_code' => 'instant',
                    'courier_service_name' => 'Instant',
                    'duration' => '1 hour',
                    'price' => 25000,
                    'shipping_type' => 'instant',
                ],
            ],
        ]),
    ]);

    $this->actingAs($customer, 'customer')
        ->getJson(route('checkout.shipping-areas.search', ['input' => 'Pesanggrahan']))
        ->assertSuccessful()
        ->assertJsonPath('areas.0.id', 'IDNP6IDNC148IDND843IDZ12250');

    $this->postJson(route('checkout.shipping-rates.index'), [
        'destination_area_id' => 'IDNP6IDNC148IDND843IDZ12250',
        'destination_postal_code' => 12250,
    ])->assertSuccessful()
        ->assertJsonPath('fallback', false)
        ->assertJsonCount(1, 'rates')
        ->assertJsonPath('rates.0.company', 'jne')
        ->assertJsonPath('rates.0.price', 18000);

    $this->postJson(route('checkout.shipping-option.store'), [
        'courier_company' => 'jne',
        'courier_service' => 'reg',
    ])->assertSuccessful()
        ->assertJsonPath('shipping.delivery_fee', 18000);

    $response = $this->post(route('checkout.store'), biteshipCheckoutData());
    $order = Order::query()->sole();

    $response->assertRedirect(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]));
    expect($order->courier_company)->toBe('jne')
        ->and($order->courier_service)->toBe('reg')
        ->and($order->destination_area_id)->toBe('IDNP6IDNC148IDND843IDZ12250')
        ->and((float) $order->delivery_fee)->toBe(18000.0)
        ->and((float) $order->total)->toBe(318000.0);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.biteship.test/v1/rates/couriers');
});

test('checkout falls back to the configured flat fee when Biteship rates fail', function () {
    config(['delivery.flat_fee' => 15000]);
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();
    $cart = Cart::query()->create(['customer_id' => $customer->id]);
    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 150000,
    ]);
    Http::fake(['https://api.biteship.test/v1/rates/couriers' => Http::failedConnection()]);

    $this->actingAs($customer, 'customer')
        ->postJson(route('checkout.shipping-rates.index'), [
            'destination_area_id' => 'IDNP6IDNC148IDND843IDZ12250',
            'destination_postal_code' => 12250,
        ])->assertSuccessful()
        ->assertJsonPath('fallback', true)
        ->assertJsonPath('rates.0.company', 'flat_rate')
        ->assertJsonPath('rates.0.price', 15000);
});

test('shipping rates are cached and the endpoint limits repeated requests', function () {
    Cache::flush();
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();
    $cart = Cart::query()->create(['customer_id' => $customer->id]);
    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 150000,
    ]);

    Http::fake(['https://api.biteship.test/v1/rates/couriers' => Http::response([
        'pricing' => [[
            'courier_code' => 'jne',
            'courier_name' => 'JNE',
            'courier_service_code' => 'reg',
            'courier_service_name' => 'Reguler',
            'duration' => '2 - 3 days',
            'price' => 18000,
            'shipping_type' => 'parcel',
        ]],
    ])]);

    $this->actingAs($customer, 'customer');

    foreach (range(1, 6) as $attempt) {
        $this->postJson(route('checkout.shipping-rates.index'), [
            'destination_area_id' => 'IDNP6IDNC148IDND843IDZ12250',
            'destination_postal_code' => 12250,
        ])->assertSuccessful();
    }

    $this->postJson(route('checkout.shipping-rates.index'), [
        'destination_area_id' => 'IDNP6IDNC148IDND843IDZ12250',
        'destination_postal_code' => 12250,
    ])->assertStatus(429);

    Http::assertSentCount(1);
});

test('an administrator can book and synchronize a Biteship shipment', function () {
    $admin = Admin::query()->create([
        'email' => 'operasional@example.com',
        'is_active' => true,
        'name' => 'Nadia Operasional',
        'password' => 'password',
        'role' => AdminRole::ADMIN,
    ]);
    $product = Product::factory()->create(['weight_grams' => 900]);
    $order = Order::factory()->create([
        'courier_company' => 'jne',
        'courier_service' => 'reg',
        'destination_area_id' => 'IDNP6IDNC148IDND843IDZ12250',
        'destination_postal_code' => 12250,
        'status' => OrderStatus::PROCESSING,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);

    Http::fake(['https://api.biteship.test/v1/orders' => Http::response([
        'id' => 'biteship-order-1',
        'status' => 'confirmed',
        'courier' => [
            'link' => 'https://tracking.example.test/JNE123',
            'tracking_id' => 'tracking-1',
            'waybill_id' => 'JNE123',
        ],
    ])]);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.deliveries.biteship.book', $order))
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::OUT_FOR_DELIVERY)
        ->and($order->biteship_order_id)->toBe('biteship-order-1')
        ->and($order->biteship_tracking_id)->toBe('tracking-1')
        ->and($order->tracking_number)->toBe('JNE123');

    Http::fake(['https://api.biteship.test/v1/trackings/tracking-1' => Http::response([
        'id' => 'tracking-1',
        'link' => 'https://tracking.example.test/JNE123',
        'status' => 'delivered',
        'waybill_id' => 'JNE123',
    ])]);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.deliveries.biteship.sync', $order))
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::DELIVERED)
        ->and($order->shipmentStatusLogs()->count())->toBe(1)
        ->and($order->shipmentStatusLogs()->value('status'))->toBe('delivered')
        ->and($order->statusHistories()->latest('id')->value('status'))->toBe(OrderStatus::DELIVERED);
});

function biteshipCheckoutData(array $overrides = []): array
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
