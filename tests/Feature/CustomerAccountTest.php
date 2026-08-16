<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\CustomerResetPassword;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

beforeEach(function () {
    $category = Category::factory()->create(['is_active' => true]);
    $this->product = Product::factory()->for($category)->create(['is_active' => true]);
});

test('guests are redirected to customer login for account pages', function () {
    $this->get(route('customer.profile.edit'))->assertRedirect(route('customer.login'));
    $this->get(route('customer.orders.index'))->assertRedirect(route('customer.login'));
});

test('a guest can register a customer account', function () {
    $this->post(route('customer.register.store'), [
        'email' => 'nadia@example.com',
        'name' => 'Nadia Putri',
        'password' => 'password',
        'password_confirmation' => 'password',
        'phone' => '081234567890',
    ])->assertRedirect(route('customer.profile.edit', absolute: false));

    $customer = Customer::query()->sole();

    expect($customer->name)->toBe('Nadia Putri')
        ->and(Hash::check('password', $customer->password))->toBeTrue();
    $this->assertAuthenticatedAs($customer, 'customer');
    $this->assertGuest('web');
});

test('a customer can sign in with email or WhatsApp number', function (string $login) {
    $customer = Customer::factory()->create(['email' => 'nadia@example.com', 'phone' => '081234567890']);

    $this->post(route('customer.login.store'), ['login' => $login, 'password' => 'password'])
        ->assertRedirect(route('customer.profile.edit', absolute: false));

    $this->assertAuthenticatedAs($customer, 'customer');
})->with(['email' => 'nadia@example.com', 'WhatsApp number' => '081234567890']);

test('a guest cart is merged into the customer cart after authentication', function () {
    $customer = Customer::factory()->create();
    $session = app('session')->driver();
    $session->start();
    $request = Request::create('/');
    $request->setLaravelSession($session);
    app()->instance('request', $request);
    $cartService = app(CartService::class);

    $cartService->addItem($this->product->id, null, 1);
    $cartService->mergeGuestCartIntoCustomer($customer->id);

    $cart = Cart::query()->whereBelongsTo($customer)->sole();

    expect($cart->items()->sum('quantity'))->toBe(1);
});

test('a customer can update their profile and password', function () {
    $customer = Customer::factory()->create(['password' => 'password']);

    $this->actingAs($customer, 'customer')->put(route('customer.profile.update'), [
        'email' => 'baru@example.com',
        'name' => 'Nadia Baru',
        'phone' => '081234567891',
    ])->assertSessionHas('success');
    $this->actingAs($customer, 'customer')->put(route('customer.profile.password.update'), [
        'current_password' => 'password',
        'password' => 'password-baru',
        'password_confirmation' => 'password-baru',
    ])->assertSessionHas('success');

    $customer->refresh();

    expect($customer->email)->toBe('baru@example.com')
        ->and($customer->name)->toBe('Nadia Baru')
        ->and(Hash::check('password-baru', $customer->password))->toBeTrue();
});

test('a customer only sees and opens their own orders', function () {
    $customer = Customer::factory()->create();
    $otherCustomer = Customer::factory()->create();
    $order = Order::factory()->create(['customer_id' => $customer->id]);
    $otherOrder = Order::factory()->create(['customer_id' => $otherCustomer->id]);

    $this->actingAs($customer, 'customer')->get(route('customer.orders.index'))
        ->assertSuccessful()
        ->assertSee($order->order_number)
        ->assertDontSee($otherOrder->order_number);
    $this->actingAs($customer, 'customer')->get(route('customer.orders.show', $order))->assertSuccessful();
    $this->actingAs($customer, 'customer')->get(route('customer.orders.show', $otherOrder))->assertNotFound();
});

test('a customer can reset their password through the customer password broker', function () {
    Notification::fake();
    $customer = Customer::factory()->create(['email' => 'nadia@example.com']);

    $this->post(route('customer.password.email'), ['email' => $customer->email])->assertSessionHas('status');
    Notification::assertSentTo($customer, CustomerResetPassword::class);

    $this->post(route('customer.password.store'), [
        'email' => $customer->email,
        'password' => 'password-baru',
        'password_confirmation' => 'password-baru',
        'token' => Password::broker('customers')->createToken($customer),
    ])->assertRedirect(route('customer.login', absolute: false));

    expect(Hash::check('password-baru', $customer->fresh()->password))->toBeTrue();
});
