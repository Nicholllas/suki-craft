<?php

use App\Models\CartItemGroup;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;

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
});

test('a guest can add a customized product to the cart with a server calculated price', function () {
    $response = $this->postJson(route('cart.add'), [
        'card_message' => 'Selamat ulang tahun!',
        'product_id' => $this->product->id,
        'bundle_quantity' => 2,
        'selected_variants' => [$this->variant->id => 1],
        'special_note' => 'Dominan warna putih.',
    ]);

    $response->assertOk()->assertJsonPath('count', 2);
    $this->assertDatabaseHas('cart_item_groups', [
        'card_message' => 'Selamat ulang tahun!',
        'product_id' => $this->product->id,
        'bundle_quantity' => 2,
        'special_note' => 'Dominan warna putih.',
    ]);
    $this->assertDatabaseHas('cart_item_variants', ['product_variant_id' => $this->variant->id, 'quantity_in_bundle' => 1, 'unit_price' => 25000]);
});

test('a customer can update and remove only items in their current cart', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customer')->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'bundle_quantity' => 1,
        'selected_variants' => [$this->variant->id => 1],
    ])->assertRedirect();

    $item = CartItemGroup::query()->firstOrFail();

    $this->actingAs($customer, 'customer')->patch(route('cart.update', $item), ['quantity' => 3])->assertRedirect();
    $this->assertDatabaseHas('cart_item_groups', ['id' => $item->id, 'bundle_quantity' => 3]);

    $this->actingAs($customer, 'customer')->get(route('cart.index'))->assertOk()->assertSee('Buket Mawar');
    $this->actingAs($customer, 'customer')->delete(route('cart.remove', $item))->assertRedirect();
    $this->assertDatabaseMissing('cart_item_groups', ['id' => $item->id]);
});

test('a quantity-based multi-variant bouquet calculates its group total on the server', function () {
    $this->product->update(['allow_multiple_variants' => true]);
    $moneyVariant = $this->product->variants()->create([
        'is_active' => true,
        'is_quantity_based' => true,
        'label' => 'Pecahan Rp50.000',
        'price_adjustment' => 50000,
        'sku' => 'MAWAR-50K',
    ]);
    $this->variant->update(['is_quantity_based' => true]);

    $this->postJson(route('cart.add'), [
        'bundle_quantity' => 2,
        'product_id' => $this->product->id,
        'selected_variants' => [$this->variant->id => 10, $moneyVariant->id => 2],
    ])->assertOk()->assertJsonPath('count', 2);

    $group = CartItemGroup::query()->with(['product', 'variants'])->sole();

    expect($group->bundle_subtotal)->toBe(500000.0)
        ->and($group->subtotal)->toBe(1000000.0)
        ->and($group->variants)->toHaveCount(2);
});

test('a single-variant product rejects a forged multi-variant selection', function () {
    $secondVariant = $this->product->variants()->create([
        'is_active' => true,
        'label' => 'Medium',
        'price_adjustment' => 10000,
        'sku' => 'MAWAR-M',
    ]);

    $this->postJson(route('cart.add'), [
        'bundle_quantity' => 1,
        'product_id' => $this->product->id,
        'selected_variants' => [$this->variant->id => 1, $secondVariant->id => 1],
    ])->assertUnprocessable()->assertJsonValidationErrors('selected_variants');
});
