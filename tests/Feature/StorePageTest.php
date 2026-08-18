<?php

use App\Models\Category;
use App\Models\Product;

test('guests can view the about page and access storefront navigation', function () {
    $this->get(route('about'))
        ->assertSuccessful()
        ->assertViewIs('store.about')
        ->assertSee('Apa yang kami lakukan')
        ->assertSee('Lihat Koleksi Buket')
        ->assertSee(route('products.index'), false);
});

test('guests can understand the ordering flow from the how to order page', function () {
    $this->get(route('how_to_order'))
        ->assertSuccessful()
        ->assertViewIs('store.how-to-order')
        ->assertSee('Temukan buket yang tepat')
        ->assertSee('Bayar dan unggah bukti')
        ->assertSee(route('products.index'), false)
        ->assertSee(route('tracking.create'), false);
});

test('the storefront features active products and their active categories', function () {
    $activeCategory = Category::factory()->create(['name' => 'Buket Romantis', 'slug' => 'buket-romantis']);
    $inactiveCategory = Category::factory()->create(['is_active' => false, 'name' => 'Kategori Tersembunyi', 'slug' => 'kategori-tersembunyi']);
    $visibleProduct = Product::factory()->for($activeCategory)->create(['is_featured' => true, 'name' => 'Buket Mawar Pilihan', 'slug' => 'buket-mawar-pilihan']);
    $inactiveProduct = Product::factory()->for($activeCategory)->create(['is_active' => false, 'name' => 'Buket Nonaktif', 'slug' => 'buket-nonaktif']);
    $hiddenCategoryProduct = Product::factory()->for($inactiveCategory)->create(['name' => 'Buket Kategori Nonaktif', 'slug' => 'buket-kategori-nonaktif']);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee($activeCategory->name)
        ->assertSee($visibleProduct->name)
        ->assertDontSee($inactiveProduct->name)
        ->assertDontSee($hiddenCategoryProduct->name);
});

test('quantity-based variants use stable named inputs on the product page', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create([
        'allow_multiple_variants' => true,
        'slug' => 'buket-uang-custom',
    ]);
    $variant = $product->variants()->create([
        'is_active' => true,
        'is_quantity_based' => true,
        'label' => 'Pecahan Rp10.000',
        'price_adjustment' => 10000,
        'sku' => 'MONEY-10K',
    ]);

    $this->get(route('products.show', $product))
        ->assertSuccessful()
        ->assertSee("setVariantQuantity({$variant->id}, \$event.target.value)", false)
        ->assertSee("'selected_variants[{$variant->id}]'", false)
        ->assertSee('payload.set(`selected_variants[${variantId}]`, variantQuantity)', false)
        ->assertDontSee(':disabled="!isSelected('.$variant->id.')"', false)
        ->assertSee('Jumlah dalam buket')
        ->assertDontSee('<template x-for="[variantId, variantQuantity]', false);
});
