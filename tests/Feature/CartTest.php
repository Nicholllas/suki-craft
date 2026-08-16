<?php

use App\Models\CartItem;
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
        'quantity' => 2,
        'special_note' => 'Dominan warna putih.',
        'variant_id' => $this->variant->id,
    ]);

    $response->assertOk()->assertJsonPath('count', 2);
    $this->assertDatabaseHas('cart_items', [
        'card_message' => 'Selamat ulang tahun!',
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 2,
        'special_note' => 'Dominan warna putih.',
        'unit_price' => 175000,
    ]);
});

test('a customer can update and remove only items in their current cart', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customer')->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'quantity' => 1,
        'variant_id' => $this->variant->id,
    ])->assertRedirect();

    $item = CartItem::query()->firstOrFail();

    $this->actingAs($customer, 'customer')->patch(route('cart.update', $item), ['quantity' => 3])->assertRedirect();
    $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 3]);

    $this->actingAs($customer, 'customer')->get(route('cart.index'))->assertOk()->assertSee('Buket Mawar');
    $this->actingAs($customer, 'customer')->delete(route('cart.remove', $item))->assertRedirect();
    $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
});
