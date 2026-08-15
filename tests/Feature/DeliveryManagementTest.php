<?php

use App\Enums\AdminRole;
use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\Courier;
use App\Models\Order;
use App\Services\DeliveryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->admin = Admin::create([
        'email' => 'operasional@example.com',
        'is_active' => true,
        'name' => 'Nadia Operasional',
        'password' => 'password',
        'role' => AdminRole::ADMIN,
    ]);
});

test('an administrator can complete the delivery lifecycle with a proof photo', function () {
    Storage::fake('local');
    $courier = Courier::factory()->create();
    $order = Order::factory()->create(['status' => OrderStatus::PAYMENT_CONFIRMED]);
    $deliveryService = app(DeliveryService::class);

    $deliveryService->markProcessing($order, $this->admin);
    $deliveryService->assignCourier($order, $courier, $this->admin);
    $deliveryService->markOutForDelivery($order, $this->admin);
    $deliveryService->markDelivered($order, UploadedFile::fake()->image('bukti-kirim.jpg'), $this->admin);

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::DELIVERED)
        ->and($order->courier_id)->toBe($courier->id)
        ->and($order->delivered_at)->not->toBeNull()
        ->and($order->delivery_proof_path)->not->toBeNull()
        ->and($order->statusHistories()->count())->toBe(4)
        ->and($order->statusHistories()->latest('id')->value('status'))->toBe(OrderStatus::DELIVERED);
    Storage::disk('local')->assertExists($order->delivery_proof_path);
});

test('a courier must be assigned before an order can be dispatched', function () {
    $order = Order::factory()->create(['status' => OrderStatus::PROCESSING]);

    expect(fn () => app(DeliveryService::class)->markOutForDelivery($order, $this->admin))
        ->toThrow(ValidationException::class);
    expect($order->refresh()->status)->toBe(OrderStatus::PROCESSING);
});

test('an administrator can cancel an order that has not been delivered', function () {
    $order = Order::factory()->create(['status' => OrderStatus::PAYMENT_CONFIRMED]);

    app(DeliveryService::class)->markCancelled($order, 'Penerima tidak dapat dihubungi.', $this->admin);

    expect($order->refresh()->status)->toBe(OrderStatus::CANCELLED)
        ->and($order->cancellation_reason)->toBe('Penerima tidak dapat dihubungi.')
        ->and($order->statusHistories()->latest('id')->value('status'))->toBe(OrderStatus::CANCELLED);
});

test('the delivery schedule lists confirmed orders for the selected date', function () {
    $order = Order::factory()->create([
        'delivery_date' => today(),
        'status' => OrderStatus::PAYMENT_CONFIRMED,
    ]);
    Order::factory()->create([
        'delivery_date' => today()->addDay(),
        'status' => OrderStatus::PAYMENT_CONFIRMED,
    ]);

    $this->actingAs($this->admin, 'admin')
        ->get(route('admin.deliveries.index', ['date' => today()->toDateString()]))
        ->assertSuccessful()
        ->assertSee($order->order_number);
});

test('tracking requires the order number and checkout phone number to match', function () {
    $order = Order::factory()->create([
        'cancellation_reason' => 'Penerima tidak dapat dihubungi.',
        'status' => OrderStatus::CANCELLED,
    ]);
    $order->statusHistories()->create([
        'note' => 'Pesanan dibatalkan: Penerima tidak dapat dihubungi.',
        'status' => OrderStatus::CANCELLED,
    ]);

    $this->post(route('tracking.store'), [
        'order_number' => $order->order_number,
        'phone' => $order->customer_phone,
    ])->assertRedirect(route('tracking.show', $order));

    $this->get(route('tracking.show', $order))
        ->assertSuccessful()
        ->assertSee('Pesanan dibatalkan')
        ->assertSee($order->cancellation_reason);

    $this->post(route('tracking.store'), [
        'order_number' => $order->order_number,
        'phone' => '089999999999',
    ])->assertSessionHasErrors('order_number');
});
