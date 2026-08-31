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

test('English locale translates customer password recovery pages', function () {
    $this->withSession(['locale' => 'en'])
        ->get(route('customer.password.request'))
        ->assertSuccessful()
        ->assertSee('Forgot your password?')
        ->assertSee('Send reset link')
        ->assertDontSee('Lupa password?')
        ->assertDontSee('Kirim tautan reset');

    $this->withSession(['locale' => 'en'])
        ->get(route('customer.password.reset', ['token' => 'test-token']))
        ->assertSuccessful()
        ->assertSee('Set a new password')
        ->assertSee('Confirm new password')
        ->assertSee('Save new password')
        ->assertDontSee('Atur password baru')
        ->assertDontSee('Simpan password baru');
});
