<?php

use App\Models\Customer;
use App\Services\PromotionService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

afterEach(function () {
    Carbon::setTestNow();
});

test('Custom Bouquet validation uses English field names when English is active', function () {
    $customer = Customer::factory()->create();

    $response = $this->actingAs($customer, 'customer')
        ->withSession(['locale' => 'en'])
        ->post(route('custom-requests.store'), []);

    $response->assertSessionHasErrors([
        'budget_range' => 'The budget range field is required.',
        'custom_bouquet_category_id' => 'The bouquet category field is required.',
        'items' => 'The bouquet contents field is required.',
        'needed_date' => 'The needed date field is required.',
    ]);
});

test('customer authentication and tracking errors use English when English is active', function () {
    Customer::factory()->create(['email' => 'nadia@example.com']);

    $this->withSession(['locale' => 'en'])
        ->post(route('customer.login.store'), ['login' => 'nadia@example.com', 'password' => 'incorrect-password'])
        ->assertSessionHasErrors(['login' => 'The email or WhatsApp number and password do not match.']);

    $this->withSession(['locale' => 'en'])
        ->post(route('tracking.store'), ['order_number' => 'SC-UNKNOWN', 'phone' => '081234567890'])
        ->assertSessionHasErrors(['order_number' => 'The order number or WhatsApp number does not match.']);
});

test('checkout validation errors use English when English is active', function () {
    Carbon::setTestNow(Carbon::parse('2026-09-10 08:00', 'Asia/Jakarta'));
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customer')
        ->withSession(['locale' => 'en'])
        ->post(route('checkout.store'), [
            'customer_email' => null,
            'customer_name' => 'Nadia Putri',
            'customer_phone' => 'invalid',
            'delivery_address' => '12 Melati Street, Jakarta',
            'delivery_date' => '2026-09-09',
            'delivery_time_slot' => '12:00-15:00',
            'idempotency_token' => fake()->uuid(),
        ])
        ->assertSessionHasErrors([
            'customer_phone' => 'Use an Indonesian phone number in the format 08xx, +628xx, or 628xx.',
            'delivery_date' => 'The delivery date cannot be earlier than today.',
        ]);

    $this->actingAs($customer, 'customer')
        ->withSession(['locale' => 'en'])
        ->post(route('checkout.store'), [
            'customer_email' => null,
            'customer_name' => 'Nadia Putri',
            'customer_phone' => '081234567890',
            'delivery_address' => '12 Melati Street, Jakarta',
            'delivery_date' => '2026-09-10',
            'delivery_time_slot' => '12:00-15:00',
            'idempotency_token' => fake()->uuid(),
        ])
        ->assertSessionHasErrors([
            'delivery_time_slot' => 'This time slot is no longer available today. Please choose another slot.',
        ]);
});

test('storefront business errors use English when English is active', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customer')
        ->withSession(['locale' => 'en'])
        ->get(route('checkout.index'))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('error', 'Add a bouquet to your cart before checkout.');

    app()->setLocale('en');

    try {
        app(PromotionService::class)->validate('MISSING', 100000, '081234567890', $customer->id);
        $this->fail('Expected invalid promotion code validation.');
    } catch (ValidationException $exception) {
        expect($exception->errors()['promotion_code'])->toBe(['Promotion code was not found.']);
    }
});

test('English and Indonesian storefront validation dictionaries stay aligned', function () {
    app()->setLocale('en');
    $englishMessages = __('store.validation.messages');
    $englishAttributes = __('store.validation.attributes');

    app()->setLocale('id');
    $indonesianMessages = __('store.validation.messages');
    $indonesianAttributes = __('store.validation.attributes');

    expect(array_keys($englishMessages))->toBe(array_keys($indonesianMessages))
        ->and(array_keys($englishAttributes['custom_request']))->toBe(array_keys($indonesianAttributes['custom_request']));
});
