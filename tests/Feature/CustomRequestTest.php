<?php

use App\Enums\AdminRole;
use App\Enums\CustomRequestStatus;
use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\CartItemGroup;
use App\Models\Category;
use App\Models\CustomBouquetCategory;
use App\Models\Customer;
use App\Models\CustomRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');

    $this->category = Category::factory()->create(['is_active' => true]);
    $this->customProduct = Product::factory()->create([
        'category_id' => $this->category->id,
        'is_active' => true,
        'is_custom_request' => true,
        'name' => 'Buat Buket Custom',
    ]);
    $this->customBouquetCategory = CustomBouquetCategory::factory()->create(['name' => 'Buket Wisuda', 'slug' => 'buket-wisuda']);
    $this->customer = Customer::factory()->create(['name' => 'Nadia Putri', 'phone' => '081234567890']);
    $this->admin = Admin::query()->create([
        'email' => 'custom-admin@example.com',
        'is_active' => true,
        'name' => 'Admin Custom',
        'password' => 'password',
        'role' => AdminRole::ADMIN,
    ]);
});

test('regular product and generic Custom Bouquet request page render successfully', function () {
    $regularProduct = Product::factory()->create([
        'category_id' => $this->category->id,
        'is_active' => true,
        'is_custom_request' => false,
    ]);

    $this->get(route('products.show', $this->customProduct))->assertNotFound();

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('Request buket custom')
        ->assertSee(route('custom-requests.create'));

    $this->actingAs($this->customer, 'customer')->get(route('custom-requests.create'))
        ->assertOk()
        ->assertSee('Request Buket Spesifik')
        ->assertSee('Buket Wisuda');

    $this->get(route('products.show', $regularProduct))
        ->assertOk()
        ->assertSee($regularProduct->name);
});

test('a customer can submit a pure Custom Bouquet request with item details and a private reference image', function () {
    $response = $this->actingAs($this->customer, 'customer')->post(route('custom-requests.store'), customRequestPayload());

    $customRequest = CustomRequest::query()->with('items')->sole();

    $response->assertRedirect(route('customer.custom-requests.show', $customRequest));
    expect($customRequest->request_number)->toBe('CR00001')
        ->and($customRequest->status)->toBe(CustomRequestStatus::WAITING_REVIEW)
        ->and($customRequest->custom_category_name)->toBe('Buket Wisuda')
        ->and($customRequest->items)->toHaveCount(2)
        ->and($customRequest->items->first()->item_name)->toBe('Cokelat Kinder')
        ->and(CartItemGroup::query()->count())->toBe(0);
    Storage::disk('local')->assertExists($customRequest->reference_image_path);

    $this->actingAs($this->customer, 'customer')->get(route('customer.custom-requests.show', $customRequest))
        ->assertOk()
        ->assertSee('Buket Wisuda #CR00001')
        ->assertSee('Cokelat Kinder');
});

test('a customer can submit a Custom Bouquet request in each locale when the internal product category is inactive', function (string $locale) {
    $this->category->update(['is_active' => false]);

    $response = $this->actingAs($this->customer, 'customer')
        ->withSession(['locale' => $locale])
        ->post(route('custom-requests.store'), customRequestPayload(['reference_image' => null]));

    $customRequest = CustomRequest::query()->sole();

    $response->assertRedirect(route('customer.custom-requests.show', $customRequest));
    expect($customRequest->customer_id)->toBe($this->customer->id)
        ->and($customRequest->product_id)->toBe($this->customProduct->id);
})->with([
    'Indonesian' => 'id',
    'English' => 'en',
]);

test('custom request provides WhatsApp links for customer follow-up and admin quote delivery', function () {
    config(['payment.whatsapp_number' => '628112223333']);
    $this->actingAs($this->customer, 'customer')->post(route('custom-requests.store'), customRequestPayload(['reference_image' => null]))->assertRedirect();
    $customRequest = CustomRequest::query()->sole();

    $this->actingAs($this->customer, 'customer')->get(route('customer.custom-requests.show', $customRequest))
        ->assertOk()
        ->assertSee('Follow up via WhatsApp')
        ->assertSee('wa.me/628112223333');

    $this->actingAs($this->admin, 'admin')->patch(route('admin.custom-requests.quote', $customRequest), [
        'quote_expires_at' => now()->addDay()->format('Y-m-d\\TH:i'),
        'quote_note' => 'Harga sudah termasuk rangkaian dan wrapping.',
        'quoted_price' => 325000,
    ])->assertRedirect(route('admin.custom-requests.show', $customRequest));

    $this->actingAs($this->admin, 'admin')->get(route('admin.custom-requests.show', $customRequest))
        ->assertOk()
        ->assertSee('Kirim penawaran via WhatsApp')
        ->assertSee('wa.me/6281234567890')
        ->assertSee(urlencode(route('customer.custom-requests.show', $customRequest)));

    $this->actingAs($this->customer, 'customer')->get(route('customer.custom-requests.show', $customRequest))
        ->assertOk()
        ->assertDontSee('Follow up via WhatsApp');
});

test('a customer can submit a counter offer with their custom request revision', function () {
    $this->actingAs($this->customer, 'customer')->post(route('custom-requests.store'), customRequestPayload(['reference_image' => null]))->assertRedirect();
    $customRequest = CustomRequest::query()->sole();

    $this->actingAs($this->admin, 'admin')->patch(route('admin.custom-requests.quote', $customRequest), [
        'quote_expires_at' => now()->addDay()->format('Y-m-d\\TH:i'),
        'quoted_price' => 325000,
    ])->assertRedirect();

    $this->actingAs($this->customer, 'customer')->post(route('customer.custom-requests.revision', $customRequest), [
        'counter_offer' => 300000,
        'revision_note' => 'Mohon wrapping dibuat lebih sederhana.',
    ])->assertRedirect(route('customer.custom-requests.show', $customRequest));

    $customRequest->refresh()->load('histories');

    expect($customRequest->status)->toBe(CustomRequestStatus::REVISION_REQUESTED)
        ->and($customRequest->histories->last()->note)->toContain('Rp300.000')
        ->toContain('Mohon wrapping dibuat lebih sederhana.');
});

test('an approved custom request enters cart at the server quoted price and completes normal checkout', function () {
    config(['delivery.flat_fee' => 0]);
    $this->actingAs($this->customer, 'customer')->post(route('custom-requests.store'), customRequestPayload(['reference_image' => null]))->assertRedirect();
    $customRequest = CustomRequest::query()->sole();

    $this->actingAs($this->admin, 'admin')->patch(route('admin.custom-requests.quote', $customRequest), [
        'quote_expires_at' => now()->addDay()->format('Y-m-d\TH:i'),
        'quote_note' => 'Harga sudah termasuk rangkaian dan wrapping.',
        'quoted_price' => 325000,
    ])->assertRedirect(route('admin.custom-requests.show', $customRequest));

    expect($customRequest->refresh()->status)->toBe(CustomRequestStatus::QUOTATION_SENT)
        ->and((float) $customRequest->quoted_price)->toBe(325000.0);

    $this->actingAs($this->customer, 'customer')->post(route('customer.custom-requests.approve', $customRequest), ['quoted_price' => 1])
        ->assertRedirect(route('cart.index'));

    $cartItemGroup = CartItemGroup::query()->where('custom_request_id', $customRequest->id)->sole();
    expect($customRequest->refresh()->status)->toBe(CustomRequestStatus::CONVERTED_TO_CART)
        ->and($cartItemGroup->bundle_quantity)->toBe(1)
        ->and($cartItemGroup->requires_quote)->toBeFalse()
        ->and((float) $cartItemGroup->service_price)->toBe(325000.0);

    $this->actingAs($this->customer, 'customer')->post(route('customer.custom-requests.approve', $customRequest))
        ->assertSessionHasErrors('custom_request');
    expect(CartItemGroup::query()->where('custom_request_id', $customRequest->id)->count())->toBe(1);

    $this->actingAs($this->customer, 'customer')->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Custom Bouquet #'.$customRequest->request_number)
        ->assertSee('Harga Custom Bouquet');

    $this->actingAs($this->customer, 'customer')->post(route('checkout.store'), customCheckoutData())->assertRedirect();

    $order = Order::query()->with('itemGroups')->sole();
    $orderItemGroup = $order->itemGroups->sole();
    expect($order->status)->toBe(OrderStatus::PENDING_PAYMENT)
        ->and((float) $order->subtotal)->toBe(325000.0)
        ->and($orderItemGroup->custom_request_id)->toBe($customRequest->id)
        ->and($orderItemGroup->requires_quote)->toBeFalse()
        ->and((float) $orderItemGroup->service_price)->toBe(325000.0);

    $this->get(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]))
        ->assertOk()
        ->assertSee('Custom Bouquet #'.$customRequest->request_number)
        ->assertSee('Harga Custom Bouquet');
});

test('a customer cannot view or approve another customers Custom Bouquet request', function () {
    $this->actingAs($this->customer, 'customer')->post(route('custom-requests.store'), customRequestPayload(['reference_image' => null]))->assertRedirect();
    $customRequest = CustomRequest::query()->sole();
    $otherCustomer = Customer::factory()->create();

    $this->actingAs($otherCustomer, 'customer')->get(route('customer.custom-requests.show', $customRequest))->assertNotFound();
    $this->actingAs($otherCustomer, 'customer')->post(route('customer.custom-requests.approve', $customRequest))->assertNotFound();
});

test('an expired custom quotation cannot be approved and is marked expired', function () {
    $customRequest = CustomRequest::factory()->create([
        'customer_id' => $this->customer->id,
        'product_id' => $this->customProduct->id,
        'quote_expires_at' => now()->subMinute(),
        'quoted_at' => now()->subDay(),
        'quoted_price' => 325000,
        'status' => CustomRequestStatus::QUOTATION_SENT,
    ]);

    $this->actingAs($this->customer, 'customer')->post(route('customer.custom-requests.approve', $customRequest))
        ->assertSessionHasErrors(['custom_request' => 'Penawaran ini sudah kedaluwarsa.']);

    expect($customRequest->refresh()->status)->toBe(CustomRequestStatus::EXPIRED);
});

function customRequestPayload(array $overrides = []): array
{
    return [
        'additional_notes' => 'Nuansa pastel dan elegan.',
        'budget_range' => '250k_500k',
        'custom_bouquet_category_id' => CustomBouquetCategory::query()->value('id'),
        'item_source' => 'sukicraft_purchases',
        'items' => [
            ['name' => 'Cokelat Kinder', 'notes' => 'Susun bagian depan.', 'quantity' => 6],
            ['name' => 'Boneka kecil', 'notes' => null, 'quantity' => 1],
        ],
        'needed_date' => now('Asia/Jakarta')->addDays(3)->toDateString(),
        'reference_image' => UploadedFile::fake()->image('referensi-buket.png'),
        'wrapping_preference' => 'Pink pastel',
        ...$overrides,
    ];
}

function customCheckoutData(array $overrides = []): array
{
    return [
        'customer_email' => 'penerima@example.com',
        'customer_name' => 'Nadia Putri',
        'customer_phone' => '081234567890',
        'delivery_address' => 'Jl. Mawar No. 10, Jakarta Selatan',
        'delivery_date' => now('Asia/Jakarta')->addDays(3)->toDateString(),
        'delivery_time_slot' => '12:00-15:00',
        'idempotency_token' => fake()->uuid(),
        'notes' => 'Hubungi sebelum tiba.',
        ...$overrides,
    ];
}
