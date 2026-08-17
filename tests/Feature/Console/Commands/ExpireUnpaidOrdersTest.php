<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

test('it cancels unpaid orders after their delivery slot begins', function () {
    Carbon::setTestNow('2026-08-18 12:00:00');
    $order = Order::factory()->create([
        'delivery_date' => '2026-08-18',
        'delivery_time_slot' => '12:00-15:00',
        'status' => OrderStatus::PENDING_PAYMENT,
    ]);

    $this->artisan('orders:expire-unpaid')
        ->expectsOutput('1 pesanan belum dibayar dibatalkan.')
        ->assertSuccessful();

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::CANCELLED)
        ->and($order->cancellation_reason)->toBe('Batas waktu pembayaran telah berakhir.')
        ->and($order->statusHistories()->latest()->value('status'))->toBe(OrderStatus::CANCELLED);
});

test('it keeps unpaid orders open before their delivery slot begins', function () {
    Carbon::setTestNow('2026-08-18 11:59:00');
    $order = Order::factory()->create([
        'delivery_date' => '2026-08-18',
        'delivery_time_slot' => '12:00-15:00',
        'status' => OrderStatus::PENDING_PAYMENT,
    ]);

    $this->artisan('orders:expire-unpaid')
        ->expectsOutput('0 pesanan belum dibayar dibatalkan.')
        ->assertSuccessful();

    expect($order->refresh()->status)->toBe(OrderStatus::PENDING_PAYMENT);
});
