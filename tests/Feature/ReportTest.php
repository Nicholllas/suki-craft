<?php

use App\Enums\AdminRole;
use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\ReportService;

test('reports include only orders with confirmed payment or later', function () {
    $category = Category::create(['name' => 'Buket Romantis', 'slug' => 'buket-romantis', 'is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Buket Mawar']);
    $paidOrder = Order::factory()->create(['created_at' => now(), 'status' => OrderStatus::PAYMENT_CONFIRMED, 'total' => 200000]);
    OrderItem::create(['order_id' => $paidOrder->id, 'product_id' => $product->id, 'product_name' => $product->name, 'quantity' => 2, 'unit_price' => 100000, 'subtotal' => 200000]);
    Order::factory()->create(['created_at' => now(), 'status' => OrderStatus::PENDING_PAYMENT, 'total' => 900000]);

    $reports = app(ReportService::class);
    $summary = $reports->getSalesSummary(now()->startOfMonth(), now());

    expect($summary)->toMatchArray(['average_order' => 200000.0, 'orders' => 1, 'revenue' => 200000.0])
        ->and($reports->getTopProducts(now()->startOfMonth(), now())->first()->quantity_sold)->toBe(2)
        ->and($reports->getRevenueByCategory(now()->startOfMonth(), now())->first()->category_name)->toBe('Buket Romantis');
});

test('an administrator can view the sales report', function () {
    $admin = Admin::create(['email' => 'reports@example.com', 'is_active' => true, 'name' => 'Admin Laporan', 'password' => 'password', 'role' => AdminRole::ADMIN]);

    $this->actingAs($admin, 'admin')->get(route('admin.reports.index', ['preset' => 'today']))->assertSuccessful()->assertSee('Laporan penjualan')->assertSee('Total pendapatan');
});
