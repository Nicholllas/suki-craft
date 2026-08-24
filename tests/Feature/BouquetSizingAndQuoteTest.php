<?php

use App\Models\CartItemGroup;
use App\Models\Category;
use App\Models\Product;

beforeEach(function () {
    $category = Category::create(['is_active' => true, 'name' => 'Buket Uang', 'slug' => 'buket-uang-test']);
    $this->product = Product::create(['allow_multiple_variants' => true, 'base_price' => 0, 'category_id' => $category->id, 'cost_price' => 0, 'is_active' => true, 'is_featured' => false, 'name' => 'Buket Uang Test', 'price' => 0, 'slug' => 'buket-uang-test', 'stock' => 1]);
    $this->variant = $this->product->variants()->create(['is_active' => true, 'is_quantity_based' => true, 'label' => 'Pecahan Rp10.000', 'price_adjustment' => 10000, 'sku' => 'TEST-10K']);
    $this->product->bouquetSizes()->createMany([
        ['code' => 'M', 'is_active' => true, 'is_custom' => false, 'label' => 'Medium', 'max_sheets' => 15, 'min_sheets' => 7, 'service_price' => 100000],
        ['code' => 'CUSTOM', 'is_active' => true, 'is_custom' => true, 'label' => 'Custom', 'max_sheets' => null, 'min_sheets' => 46, 'service_price' => 0],
    ]);
});

test('cart derives a configured bouquet size and its service price from selected money sheets', function () {
    $this->postJson(route('cart.add'), ['bundle_quantity' => 1, 'product_id' => $this->product->id, 'selected_variants' => [$this->variant->id => 7]])->assertOk();

    $group = CartItemGroup::query()->sole();

    expect($group->bouquet_size_label)->toBe('Medium')
        ->and($group->requires_quote)->toBeFalse()
        ->and((float) $group->service_price)->toBe(100000.0)
        ->and($group->subtotal)->toBe(170000.0);
});

test('cart marks custom bouquet quantities as requiring a quote', function () {
    $this->postJson(route('cart.add'), ['bundle_quantity' => 1, 'product_id' => $this->product->id, 'selected_variants' => [$this->variant->id => 46]])->assertOk();

    $group = CartItemGroup::query()->sole();

    expect($group->bouquet_size_label)->toBe('Custom')
        ->and($group->requires_quote)->toBeTrue()
        ->and((float) $group->service_price)->toBe(0.0);
});
test('product page renders one automatic bouquet size panel with its configured pricing data', function () {
    $response = $this->get(route('products.show', $this->product));

    $response->assertOk()
        ->assertSee('Medium')
        ->assertSee('servicePrice');

    expect(substr_count($response->getContent(), 'Ukuran buket otomatis'))->toBe(1);
});
