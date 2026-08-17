<?php

test('guests can view the about page and access storefront navigation', function () {
    $this->get(route('about'))
        ->assertSuccessful()
        ->assertViewIs('store.about')
        ->assertSee('Apa yang kami lakukan')
        ->assertSee('Lihat Koleksi Buket')
        ->assertSee(route('products.index'), false);
});
