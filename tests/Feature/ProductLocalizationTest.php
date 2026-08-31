<?php

use App\Models\Category;
use App\Models\Product;

test('English locale translates the product-detail interface', function () {
    $product = Product::factory()
        ->for(Category::factory()->create(['is_active' => true]))
        ->create([
            'description' => null,
            'name' => 'Money Bouquet',
            'slug' => 'money-bouquet',
        ]);

    $this->withSession(['locale' => 'en'])
        ->get(route('products.show', $product))
        ->assertSuccessful()
        ->assertSee('Back to collections')
        ->assertSee('Price for your selection')
        ->assertSee('Gift card message')
        ->assertSee('Customer reviews')
        ->assertSee('No reviews yet')
        ->assertDontSee('Kembali ke koleksi')
        ->assertDontSee('Harga untuk pilihanmu')
        ->assertDontSee('Pesan kartu ucapan');
});
