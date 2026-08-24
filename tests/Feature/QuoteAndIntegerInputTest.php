<?php

use App\Enums\AdminRole;
use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Promotion;

test('an administrator can quote a custom order with a whole rupiah amount and contact the customer by WhatsApp', function () {
    $admin = Admin::create([
        'email' => 'quote@example.com',
        'is_active' => true,
        'name' => 'Admin Penawaran',
        'password' => 'password',
        'role' => AdminRole::ADMIN,
    ]);
    $order = Order::factory()->create([
        'customer_name' => 'Nadia',
        'customer_phone' => '081234567890',
        'delivery_fee' => 15000,
        'discount_amount' => 0,
        'status' => OrderStatus::AWAITING_QUOTE,
    ]);

    $this->actingAs($admin, 'admin')
        ->patch(route('admin.orders.quote', $order), [
            'quote_expires_at' => now()->addDay()->toDateTimeString(),
            'quote_note' => 'Harga sudah termasuk rangkaian khusus.',
            'quote_subtotal' => 5000000,
        ])
        ->assertRedirect(route('admin.orders.show', $order));

    expect($order->refresh()->status)->toBe(OrderStatus::AWAITING_APPROVAL)
        ->and($order->subtotal)->toBe('5000000.00')
        ->and($order->total)->toBe('5015000.00');

    $this->actingAs($admin, 'admin')
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('name="quote_subtotal" type="number" min="1" step="1" inputmode="numeric"', false)
        ->assertSee('Kirim penawaran via WhatsApp')
        ->assertSee('wa.me/6281234567890', false);
});

test('custom quote and stock forms reject decimal values', function () {
    $admin = Admin::create([
        'email' => 'integer@example.com',
        'is_active' => true,
        'name' => 'Admin Bilangan Bulat',
        'password' => 'password',
        'role' => AdminRole::ADMIN,
    ]);
    $order = Order::factory()->create(['status' => OrderStatus::AWAITING_QUOTE]);
    $ingredient = Ingredient::create([
        'current_stock' => 10,
        'is_active' => true,
        'minimum_stock' => 2,
        'name' => 'Pita satin',
        'unit' => 'meter',
    ]);

    $this->actingAs($admin, 'admin')
        ->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.quote', $order), [
            'quote_expires_at' => now()->addDay()->toDateTimeString(),
            'quote_subtotal' => 5000000.5,
        ])
        ->assertRedirect(route('admin.orders.show', $order))
        ->assertSessionHasErrors('quote_subtotal');

    $this->actingAs($admin, 'admin')
        ->from(route('admin.ingredients.show', $ingredient))
        ->post(route('admin.ingredients.stock-in', $ingredient), [
            'quantity' => 1.5,
            'reason' => 'Pembelian pemasok',
        ])
        ->assertRedirect(route('admin.ingredients.show', $ingredient))
        ->assertSessionHasErrors('quantity');

    $this->actingAs($admin, 'admin')
        ->from(route('admin.ingredients.show', $ingredient))
        ->post(route('admin.ingredients.adjustment', $ingredient), [
            'quantity' => -1.5,
            'reason' => 'Stok opname',
        ])
        ->assertRedirect(route('admin.ingredients.show', $ingredient))
        ->assertSessionHasErrors('quantity');
});
test('fixed promotion amounts require whole rupiah while percentages may still use decimals', function () {
    $admin = Admin::create([
        'email' => 'promotion-integer@example.com',
        'is_active' => true,
        'name' => 'Admin Promo',
        'password' => 'password',
        'role' => AdminRole::ADMIN,
    ]);
    $dates = [
        'expires_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
        'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
    ];

    $this->actingAs($admin, 'admin')
        ->from(route('admin.promotions.create'))
        ->post(route('admin.promotions.store'), [
            ...$dates,
            'code' => 'HEMAT5000',
            'is_active' => true,
            'max_discount' => 50000,
            'min_purchase' => 100000,
            'type' => 'fixed',
            'usage_limit_per_customer' => 1,
            'value' => 5000.5,
        ])
        ->assertRedirect(route('admin.promotions.create'))
        ->assertSessionHasErrors('value');

    $this->actingAs($admin, 'admin')
        ->post(route('admin.promotions.store'), [
            ...$dates,
            'code' => 'HEMATPERSEN',
            'is_active' => true,
            'max_discount' => 50000,
            'min_purchase' => 100000,
            'type' => 'percentage',
            'usage_limit_per_customer' => 1,
            'value' => 12.5,
        ])
        ->assertRedirect(route('admin.promotions.index'));

    expect(Promotion::query()->where('code', 'HEMATPERSEN')->value('value'))->toBe('12.50');
});
