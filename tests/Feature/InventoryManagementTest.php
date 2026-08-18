<?php

use App\Enums\AdminRole;
use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItemGroup;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Services\DeliveryService;

test('processing an order deducts its ingredients and records stock movement', function () {
    $ingredient = Ingredient::create(['name' => 'Mawar merah', 'unit' => 'tangkai', 'current_stock' => 10, 'minimum_stock' => 3, 'is_active' => true]);
    $product = Product::factory()->create();
    ProductIngredient::create(['ingredient_id' => $ingredient->id, 'product_id' => $product->id, 'quantity_needed' => 3]);
    $order = Order::factory()->create(['status' => OrderStatus::PAYMENT_CONFIRMED]);
    OrderItemGroup::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => $product->name, 'bundle_quantity' => 2, 'subtotal' => $product->base_price * 2]);

    app(DeliveryService::class)->markProcessing($order);

    expect($order->refresh()->status)->toBe(OrderStatus::PROCESSING)
        ->and($ingredient->refresh()->current_stock)->toBe('4.000')
        ->and($ingredient->stockMovements()->where('type', 'out')->value('quantity'))->toBe('-6.000')
        ->and($ingredient->stockMovements()->first()->related_order_id)->toBe($order->id);
});

test('processing an order through the admin route deducts variant recipe quantities', function () {
    $admin = Admin::create(['email' => 'processing@example.com', 'is_active' => true, 'name' => 'Admin Produksi', 'password' => 'password', 'role' => AdminRole::ADMIN]);
    $ingredient = Ingredient::create(['name' => 'Kertas uang', 'unit' => 'lembar', 'current_stock' => 30, 'minimum_stock' => 3, 'is_active' => true]);
    $product = Product::factory()->create();
    $productVariant = $product->variants()->create([
        'is_active' => true,
        'is_quantity_based' => true,
        'label' => 'Pecahan Rp20.000',
        'price_adjustment' => 20000,
    ]);
    ProductIngredient::create([
        'ingredient_id' => $ingredient->id,
        'product_id' => $product->id,
        'product_variant_id' => $productVariant->id,
        'quantity_needed' => null,
        'ratio_per_unit' => 2,
    ]);
    $order = Order::factory()->create(['status' => OrderStatus::PAYMENT_CONFIRMED]);
    $itemGroup = OrderItemGroup::create([
        'bundle_quantity' => 2,
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'subtotal' => 120000,
    ]);
    $itemGroup->variants()->create([
        'line_subtotal' => 120000,
        'product_variant_id' => $productVariant->id,
        'quantity_in_bundle' => 3,
        'unit_price' => 20000,
        'variant_label' => $productVariant->label,
    ]);

    $this->actingAs($admin, 'admin')
        ->from(route('admin.deliveries.index'))
        ->patch(route('admin.deliveries.processing', $order))
        ->assertRedirect(route('admin.deliveries.index'));

    expect($order->refresh()->status)->toBe(OrderStatus::PROCESSING)
        ->and($ingredient->refresh()->current_stock)->toBe('18.000')
        ->and($ingredient->stockMovements()->where('type', 'out')->value('quantity'))->toBe('-12.000');
});

test('processing combines default fixed variant and quantity based ratio recipes', function () {
    $defaultIngredient = Ingredient::create(['name' => 'Kertas pembungkus', 'unit' => 'lembar', 'current_stock' => 100, 'minimum_stock' => 3, 'is_active' => true]);
    $fixedVariantIngredient = Ingredient::create(['name' => 'Pita ukuran besar', 'unit' => 'meter', 'current_stock' => 100, 'minimum_stock' => 3, 'is_active' => true]);
    $ratioIngredient = Ingredient::create(['name' => 'Lembar uang', 'unit' => 'lembar', 'current_stock' => 100, 'minimum_stock' => 3, 'is_active' => true]);
    $product = Product::factory()->create();
    $fixedVariant = $product->variants()->create(['is_active' => true, 'is_quantity_based' => false, 'label' => 'Ukuran Besar', 'price_adjustment' => 50000]);
    $quantityBasedVariant = $product->variants()->create(['is_active' => true, 'is_quantity_based' => true, 'label' => 'Pecahan Rp10.000', 'price_adjustment' => 10000]);
    $unlinkedQuantityBasedVariant = $product->variants()->create(['is_active' => true, 'is_quantity_based' => true, 'label' => 'Pecahan Rp20.000', 'price_adjustment' => 20000]);

    ProductIngredient::create(['ingredient_id' => $defaultIngredient->id, 'product_id' => $product->id, 'quantity_needed' => 2, 'ratio_per_unit' => null]);
    ProductIngredient::create(['ingredient_id' => $fixedVariantIngredient->id, 'product_id' => $product->id, 'product_variant_id' => $fixedVariant->id, 'quantity_needed' => 3, 'ratio_per_unit' => null]);
    ProductIngredient::create(['ingredient_id' => $ratioIngredient->id, 'product_id' => $product->id, 'product_variant_id' => $quantityBasedVariant->id, 'quantity_needed' => null, 'ratio_per_unit' => 0.5]);

    $order = Order::factory()->create(['status' => OrderStatus::PAYMENT_CONFIRMED]);
    $itemGroup = OrderItemGroup::create(['bundle_quantity' => 2, 'order_id' => $order->id, 'product_id' => $product->id, 'product_name' => $product->name, 'subtotal' => 500000]);
    $itemGroup->variants()->create(['line_subtotal' => 100000, 'product_variant_id' => $fixedVariant->id, 'quantity_in_bundle' => 1, 'unit_price' => 50000, 'variant_label' => $fixedVariant->label]);
    $itemGroup->variants()->create(['line_subtotal' => 100000, 'product_variant_id' => $quantityBasedVariant->id, 'quantity_in_bundle' => 10, 'unit_price' => 10000, 'variant_label' => $quantityBasedVariant->label]);
    $itemGroup->variants()->create(['line_subtotal' => 140000, 'product_variant_id' => $unlinkedQuantityBasedVariant->id, 'quantity_in_bundle' => 7, 'unit_price' => 20000, 'variant_label' => $unlinkedQuantityBasedVariant->label]);

    app(DeliveryService::class)->markProcessing($order);

    expect($order->refresh()->status)->toBe(OrderStatus::PROCESSING)
        ->and($defaultIngredient->refresh()->current_stock)->toBe('96.000')
        ->and($fixedVariantIngredient->refresh()->current_stock)->toBe('94.000')
        ->and($ratioIngredient->refresh()->current_stock)->toBe('90.000')
        ->and($order->ingredientStockMovements()->where('type', 'out')->count())->toBe(3);
});

test('an administrator can record stock changes and view stock history', function () {
    $admin = Admin::create(['email' => 'inventory@example.com', 'is_active' => true, 'name' => 'Admin Inventori', 'password' => 'password', 'role' => AdminRole::ADMIN]);
    $ingredient = Ingredient::create(['name' => 'Pita satin', 'unit' => 'meter', 'current_stock' => 2, 'minimum_stock' => 3, 'is_active' => true]);

    $this->actingAs($admin, 'admin')->get(route('admin.ingredients.index'))->assertSuccessful()->assertSee('Pita satin')->assertSee('Stok menipis');
    $this->actingAs($admin, 'admin')->post(route('admin.ingredients.stock-in', $ingredient), ['quantity' => 5, 'reason' => 'Pembelian pemasok'])->assertRedirect();
    $this->actingAs($admin, 'admin')->post(route('admin.ingredients.adjustment', $ingredient), ['quantity' => 4, 'reason' => 'Stok opname'])->assertRedirect();
    $this->actingAs($admin, 'admin')->get(route('admin.ingredients.show', $ingredient))->assertSuccessful()->assertSee('Pembelian pemasok')->assertSee('Stok opname');

    expect($ingredient->refresh()->current_stock)->toBe('4.000')->and($ingredient->stockMovements()->count())->toBe(2);
});
