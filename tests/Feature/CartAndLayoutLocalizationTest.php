<?php

test('English locale translates the empty cart', function () {
    $this->withSession(['locale' => 'en'])
        ->get(route('cart.index'))
        ->assertSuccessful()
        ->assertSee('Shopping cart')
        ->assertSee('Your cart is empty')
        ->assertDontSee('Keranjangmu masih kosong');
});

test('the storefront header renders before Custom Bouquet content', function () {
    $this->withSession(['locale' => 'en'])
        ->get(route('custom-requests.create'))
        ->assertSuccessful()
        ->assertSeeInOrder([
            'Complimentary message card with every bouquet order',
            'Bring your bouquet idea to life',
        ]);
});
