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
