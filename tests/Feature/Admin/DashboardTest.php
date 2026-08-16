<?php

use App\Enums\AdminRole;
use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\Ingredient;
use App\Models\Order;

beforeEach(function () {
    $this->admin = Admin::create([
        'email' => 'dashboard@example.com',
        'is_active' => true,
        'name' => 'Dini Operasional',
        'password' => 'password',
        'role' => AdminRole::ADMIN,
    ]);
});

test('an administrator sees dynamic dashboard metrics and low-stock ingredients', function () {
    $todayPaidOrder = Order::factory()->create([
        'created_at' => now(),
        'status' => OrderStatus::PAYMENT_CONFIRMED,
        'total' => 200000,
    ]);
    $monthPaidOrder = Order::factory()->create([
        'created_at' => now()->startOfMonth()->addDay(),
        'status' => OrderStatus::DELIVERED,
        'total' => 300000,
    ]);
    $pendingOrder = Order::factory()->create([
        'created_at' => now(),
        'status' => OrderStatus::PENDING_PAYMENT,
        'total' => 150000,
    ]);
    Order::factory()->create([
        'created_at' => now()->subMonth(),
        'status' => OrderStatus::DELIVERED,
        'total' => 400000,
    ]);
    $lowStockIngredient = Ingredient::create(['current_stock' => 2, 'is_active' => true, 'minimum_stock' => 3, 'name' => 'Mawar merah', 'unit' => 'tangkai']);
    Ingredient::create(['current_stock' => 0, 'is_active' => false, 'minimum_stock' => 3, 'name' => 'Pita merah', 'unit' => 'meter']);

    $this->actingAs($this->admin, 'admin')->get(route('admin.dashboard', ['period' => 'week']))
        ->assertSuccessful()
        ->assertSee('Rp200.000')
        ->assertSee('Rp500.000')
        ->assertSee($todayPaidOrder->order_number)
        ->assertSee($monthPaidOrder->order_number)
        ->assertSee($pendingOrder->order_number)
        ->assertSee('Stok bahan menipis')
        ->assertSee($lowStockIngredient->name)
        ->assertViewHas('awaitingVerificationCount', 0)
        ->assertViewHas('lowStockIngredientCount', 1)
        ->assertViewHas('newOrderCount', 2)
        ->assertViewHas('revenueMonth', 500000.0)
        ->assertViewHas('revenueToday', 200000.0);
});

test('an administrator can choose a dashboard period', function () {
    $this->actingAs($this->admin, 'admin')->get(route('admin.dashboard', ['period' => 'today']))
        ->assertSuccessful()
        ->assertViewHas('period', 'today')
        ->assertViewHas('periodLabel', 'Hari ini');
});

test('an invalid dashboard period is rejected', function () {
    $this->actingAs($this->admin, 'admin')->get(route('admin.dashboard', ['period' => 'year']))
        ->assertSessionHasErrors('period');
});
