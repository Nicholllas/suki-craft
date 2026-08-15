<?php

use App\Enums\AdminRole;
use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentProof;

beforeEach(function () {
    $this->admin = Admin::create([
        'email' => 'pesanan@example.com',
        'is_active' => true,
        'name' => 'Dini Operasional',
        'password' => 'password',
        'role' => AdminRole::ADMIN,
    ]);
});

test('an administrator can filter all orders by status, date, and customer details', function () {
    $matchingOrder = Order::factory()->create([
        'created_at' => today()->subDay(),
        'customer_name' => 'Nadia Puspita',
        'customer_phone' => '081234567890',
        'delivery_date' => today()->addDays(2),
        'status' => OrderStatus::DELIVERED,
    ]);
    $otherOrder = Order::factory()->create([
        'customer_name' => 'Raka Pratama',
        'delivery_date' => today()->addDays(5),
        'status' => OrderStatus::PROCESSING,
    ]);

    $this->actingAs($this->admin, 'admin')
        ->get(route('admin.orders.index', [
            'delivery_date_from' => today()->addDay()->toDateString(),
            'delivery_date_to' => today()->addDays(3)->toDateString(),
            'order_date_from' => today()->subDays(2)->toDateString(),
            'order_date_to' => today()->toDateString(),
            'search' => 'Nadia',
            'statuses' => [OrderStatus::DELIVERED->value],
        ]))
        ->assertSuccessful()
        ->assertSee($matchingOrder->order_number)
        ->assertDontSee($otherOrder->order_number);
});

test('an administrator can view the complete detail of an order', function () {
    $order = Order::factory()->create(['status' => OrderStatus::AWAITING_VERIFICATION]);
    OrderItem::factory()->for($order)->create([
        'card_message' => 'Selamat atas kelulusanmu!',
        'special_note' => 'Gunakan pita warna krem.',
    ]);
    $proof = PaymentProof::factory()->for($order)->create();
    $order->statusHistories()->create([
        'changed_by' => $this->admin->id,
        'note' => 'Pesanan diperiksa oleh tim operasional.',
        'status' => OrderStatus::AWAITING_VERIFICATION,
    ]);

    $this->actingAs($this->admin, 'admin')
        ->get(route('admin.orders.show', $order))
        ->assertSuccessful()
        ->assertSee($order->order_number)
        ->assertSee($proof->uploaded_at->locale('id')->translatedFormat('d M Y, H.i'))
        ->assertSee('Selamat atas kelulusanmu!')
        ->assertSee('Pesanan diperiksa oleh tim operasional.');
});

test('an administrator can manually override an order status with a required reason', function () {
    $order = Order::factory()->create(['status' => OrderStatus::PROCESSING]);

    $this->actingAs($this->admin, 'admin')
        ->patch(route('admin.orders.update-status', $order), [
            'reason' => 'Stok bunga utama habis setelah pesanan mulai dirangkai.',
            'status' => OrderStatus::CANCELLED->value,
        ])
        ->assertRedirect(route('admin.orders.show', $order));

    $history = $order->statusHistories()->latest('id')->first();

    expect($order->refresh()->status)->toBe(OrderStatus::CANCELLED)
        ->and($history->changed_by)->toBe($this->admin->id)
        ->and($history->status)->toBe(OrderStatus::CANCELLED)
        ->and($history->note)->toContain('Stok bunga utama habis');
});

test('a manual status override requires a valid status and reason', function () {
    $order = Order::factory()->create(['status' => OrderStatus::PROCESSING]);

    $this->actingAs($this->admin, 'admin')
        ->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.update-status', $order), [
            'reason' => '',
            'status' => 'invalid-status',
        ])
        ->assertRedirect(route('admin.orders.show', $order))
        ->assertSessionHasErrors(['reason', 'status']);

    expect($order->refresh()->status)->toBe(OrderStatus::PROCESSING);
});
