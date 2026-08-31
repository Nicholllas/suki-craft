<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\PromotionUsage;

test('it previews order and promotion usage phone number normalization', function () {
    $order = Order::factory()->create(['customer_phone' => '081234567890']);
    $promotion = Promotion::query()->create([
        'code' => 'NORMALIZE',
        'expires_at' => now()->addDay(),
        'starts_at' => now()->subDay(),
        'type' => 'fixed',
        'value' => 10000,
    ]);
    $promotionUsage = PromotionUsage::query()->create([
        'customer_phone' => '+62 812-3456 7890',
        'order_id' => $order->id,
        'promotion_id' => $promotion->id,
    ]);
    $customer = Customer::factory()->create(['phone' => '081234567891']);

    $this->artisan('orders:normalize-phone-numbers --dry-run')
        ->expectsOutput('1 nomor order akan dinormalisasi.')
        ->expectsOutput('1 nomor penggunaan promo akan dinormalisasi.')
        ->expectsOutput('1 nomor pelanggan akan dinormalisasi.')
        ->assertSuccessful();

    expect($order->refresh()->customer_phone)->toBe('081234567890')
        ->and($promotionUsage->refresh()->customer_phone)->toBe('+62 812-3456 7890')
        ->and($customer->refresh()->phone)->toBe('081234567891');
});

test('it normalizes valid records and skips invalid phone numbers', function () {
    $order = Order::factory()->create(['customer_phone' => '081234567890']);
    $invalidOrder = Order::factory()->create(['customer_phone' => 'not-a-phone']);
    $customer = Customer::factory()->create(['phone' => '081234567891']);

    $this->artisan('orders:normalize-phone-numbers')
        ->expectsOutput('1 nomor order dinormalisasi.')
        ->expectsOutput('0 nomor penggunaan promo dinormalisasi.')
        ->expectsOutput('1 nomor pelanggan dinormalisasi.')
        ->expectsOutput('1 nomor tidak valid dilewati.')
        ->assertSuccessful();

    expect($order->refresh()->customer_phone)->toBe('6281234567890')
        ->and($invalidOrder->refresh()->customer_phone)->toBe('not-a-phone')
        ->and($customer->refresh()->phone)->toBe('6281234567891');
});

test('it skips customer phone numbers that would conflict after normalization', function () {
    $legacyCustomer = Customer::factory()->create(['phone' => '081234567890']);
    Customer::factory()->create(['phone' => '6281234567890']);

    $this->artisan('orders:normalize-phone-numbers')
        ->expectsOutput('0 nomor order dinormalisasi.')
        ->expectsOutput('0 nomor penggunaan promo dinormalisasi.')
        ->expectsOutput('0 nomor pelanggan dinormalisasi.')
        ->expectsOutput('1 nomor pelanggan konflik dilewati.')
        ->assertSuccessful();

    expect($legacyCustomer->refresh()->phone)->toBe('081234567890');
});
