<?php

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;

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
test('English locale translates tracked order details', function () {
    $order = Order::factory()->create([
        'cancellation_reason' => 'Recipient could not be reached.',
        'status' => OrderStatus::CANCELLED,
    ]);
    $order->statusHistories()->create(['status' => OrderStatus::CANCELLED]);

    $this->withSession(['locale' => 'en', 'tracked_order_id' => $order->id])
        ->get(route('tracking.show', $order))
        ->assertSuccessful()
        ->assertSee('Track another order')
        ->assertSee('Order journey')
        ->assertSee('Order cancelled')
        ->assertSee('Current status')
        ->assertDontSee('Lacak pesanan lain')
        ->assertDontSee('Perjalanan pesanan')
        ->assertDontSee('Pesanan dibatalkan');
});
test('English locale translates customer profile and empty order history', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customer')
        ->withSession(['locale' => 'en'])
        ->get(route('customer.profile.edit'))
        ->assertSuccessful()
        ->assertSee('Profile & security')
        ->assertSee('Primary address')
        ->assertDontSee('Profil & keamanan');

    $this->actingAs($customer, 'customer')
        ->withSession(['locale' => 'en'])
        ->get(route('customer.orders.index'))
        ->assertSuccessful()
        ->assertSee('Order history')
        ->assertSee('No orders yet')
        ->assertDontSee('Riwayat pesanan');
});
