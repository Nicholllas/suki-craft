<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\CustomerResetPassword;
use App\Services\CartService;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportException;

beforeEach(function () {
    $category = Category::factory()->create(['is_active' => true]);
    $this->product = Product::factory()->for($category)->create(['is_active' => true]);
});

test('guests are redirected to customer login for account pages', function () {
    $this->get(route('customer.profile.edit'))->assertRedirect(route('customer.login'));
    $this->get(route('customer.orders.index'))->assertRedirect(route('customer.login'));
});

test('a guest can register a customer account and return to their intended checkout', function () {
    $cartService = Mockery::mock(CartService::class);
    $cartService->shouldReceive('mergeGuestCartIntoCustomer')->once()->with(Mockery::type('int'), Mockery::type('string'));
    $this->app->instance(CartService::class, $cartService);

    $this->withSession(['url.intended' => route('checkout.index')])->post(route('customer.register.store'), [
        'email' => 'nadia@example.com',
        'name' => 'Nadia Putri',
        'password' => 'password',
        'password_confirmation' => 'password',
        'phone' => '081234567890',
    ])->assertRedirect(route('checkout.index'));

    $customer = Customer::query()->sole();

    expect($customer->name)->toBe('Nadia Putri')
        ->and(Hash::check('password', $customer->password))->toBeTrue();
    $this->assertAuthenticatedAs($customer, 'customer');
    $this->assertGuest('web');
});

test('a customer can sign in with email or WhatsApp number', function (string $login) {
    $customer = Customer::factory()->create(['email' => 'nadia@example.com', 'phone' => '081234567890']);
    $cartService = Mockery::mock(CartService::class);
    $cartService->shouldReceive('mergeGuestCartIntoCustomer')->once()->with($customer->id, Mockery::type('string'));
    $this->app->instance(CartService::class, $cartService);

    $this->withSession(['url.intended' => route('checkout.index')])
        ->post(route('customer.login.store'), ['login' => $login, 'password' => 'password'])
        ->assertRedirect(route('checkout.index'));

    $this->assertAuthenticatedAs($customer, 'customer');
})->with(['email' => 'nadia@example.com', 'WhatsApp number' => '081234567890']);

test('a guest cart is merged after the session ID is regenerated', function () {
    $customer = Customer::factory()->create();
    $session = app('session')->driver();
    $session->start();
    $guestCartSessionId = $session->getId();
    $request = Request::create('/');
    $request->setLaravelSession($session);
    app()->instance('request', $request);
    $cartService = app(CartService::class);

    $cartService->addToCart($this->product, [], 1);
    $session->regenerate(true);
    $cartService->mergeGuestCartIntoCustomer($customer->id, $guestCartSessionId);

    $cart = Cart::query()->whereBelongsTo($customer)->sole();

    expect($cart->itemGroups()->sum('bundle_quantity'))->toBe(1);
});

test('a customer can update their profile and password', function () {
    $customer = Customer::factory()->create(['password' => 'password']);

    $this->actingAs($customer, 'customer')->put(route('customer.profile.update'), [
        'email' => 'baru@example.com',
        'name' => 'Nadia Baru',
        'phone' => '081234567891',
        'address' => 'Jl. Melati No. 12, Jakarta Selatan 12110',
    ])->assertSessionHas('success');
    $this->actingAs($customer, 'customer')->put(route('customer.profile.password.update'), [
        'current_password' => 'password',
        'password' => 'password-baru',
        'password_confirmation' => 'password-baru',
    ])->assertSessionHas('success');

    $customer->refresh();

    expect($customer->email)->toBe('baru@example.com')
        ->and($customer->name)->toBe('Nadia Baru')
        ->and($customer->address)->toBe('Jl. Melati No. 12, Jakarta Selatan 12110')
        ->and(Hash::check('password-baru', $customer->password))->toBeTrue();
});

test('a customer only sees and opens their own orders', function () {
    $customer = Customer::factory()->create();
    $otherCustomer = Customer::factory()->create();
    config(['payment.whatsapp_number' => '6281234567890']);

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'delivery_fee' => 15000,
        'subtotal' => 170000,
        'total' => 185000,
    ]);
    $orderItem = $order->itemGroups()->create([
        'bundle_quantity' => 1,
        'product_id' => $this->product->id,
        'product_name' => 'Buket Mawar',
        'service_price' => 150000,
        'subtotal' => 170000,
    ]);
    $orderItem->variants()->create([
        'line_subtotal' => 20000,
        'quantity_in_bundle' => 1,
        'unit_price' => 20000,
        'variant_label' => 'Pecahan Rp20.000',
    ]);
    $otherOrder = Order::factory()->create(['customer_id' => $otherCustomer->id]);
    $paymentUrl = route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]);

    $this->actingAs($customer, 'customer')->get(route('customer.orders.index'))
        ->assertSuccessful()
        ->assertSee($order->order_number)
        ->assertSee('Total pembayaran')
        ->assertSee('Rp185.000')
        ->assertDontSee('Buket Mawar')
        ->assertDontSee('Biaya jasa merangkai')
        ->assertSee('Hubungi admin via WhatsApp')
        ->assertDontSee($otherOrder->order_number);
    $this->actingAs($customer, 'customer')->get(route('customer.orders.show', $order))
        ->assertSuccessful()
        ->assertSee('Lanjutkan pembayaran')
        ->assertSee('Rincian pesanan')
        ->assertSee('Total pembayaran')
        ->assertSee('Hubungi admin')
        ->assertSee($paymentUrl);
    $this->actingAs($customer, 'customer')->get(route('customer.orders.show', $otherOrder))->assertNotFound();
});

test('a customer can reset their password through the customer password broker', function () {
    Notification::fake();
    $customer = Customer::factory()->create(['email' => 'nadia@example.com']);

    $this->post(route('customer.password.email'), ['email' => $customer->email])->assertSessionHas('status');
    Notification::assertSentTo($customer, CustomerResetPassword::class, function (CustomerResetPassword $notification, array $channels) use ($customer): bool {
        return $channels === ['mail']
            && $customer->routeNotificationFor('mail', $notification) === $customer->email;
    });

    $this->post(route('customer.password.store'), [
        'email' => $customer->email,
        'password' => 'password-baru',
        'password_confirmation' => 'password-baru',
        'token' => Password::broker('customers')->createToken($customer),
    ])->assertRedirect(route('customer.login', absolute: false));

    expect(Hash::check('password-baru', $customer->fresh()->password))->toBeTrue();
});

test('a customer sees an error when the reset email cannot be delivered', function () {
    $customer = Customer::factory()->create();
    $mailer = Mockery::mock(Mailer::class);
    $mailer->shouldReceive('send')->once()->andThrow(new TransportException('SMTP server unavailable.'));
    Mail::shouldReceive('mailer')->once()->with(null)->andReturn($mailer);

    $this->post(route('customer.password.email'), ['email' => $customer->email])
        ->assertSessionHasErrors('email');
});
