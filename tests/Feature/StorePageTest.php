<?php

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
