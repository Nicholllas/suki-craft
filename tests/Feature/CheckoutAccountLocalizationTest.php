<?php

test('English locale translates the checkout account requirement page', function () {
    $this->withSession(['locale' => 'en'])
        ->get(route('checkout.require-account'))
        ->assertSuccessful()
        ->assertSee('One more step')
        ->assertSee('Continue as a member')
        ->assertSee('Cart summary')
        ->assertSee('0 bouquets')
        ->assertSee('Create a new account')
        ->assertSee('Already have an account? Sign in')
        ->assertSee('Back to cart')
        ->assertDontSee('Satu langkah lagi')
        ->assertDontSee('Daftar Akun Baru');
});
