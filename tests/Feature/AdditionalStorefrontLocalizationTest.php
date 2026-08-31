<?php

test('English locale translates the remaining public storefront pages', function () {
    $this->withSession(['locale' => 'en'])
        ->get(route('about'))
        ->assertSuccessful()
        ->assertSee('We arrange')
        ->assertSee('Behind every stem');

    $this->withSession(['locale' => 'en'])
        ->get(route('customer.login'))
        ->assertSuccessful()
        ->assertSee('Welcome back')
        ->assertSee('Forgot password?');

    $this->withSession(['locale' => 'en'])
        ->get(route('customer.register'))
        ->assertSuccessful()
        ->assertSee('Create your account');

    $this->withSession(['locale' => 'en'])
        ->get(route('tracking.create'))
        ->assertSuccessful()
        ->assertSee('Track your order');

    $this->withSession(['locale' => 'en'])
        ->get(route('custom-requests.create'))
        ->assertSuccessful()
        ->assertSee('Bring your bouquet idea to life');
});
