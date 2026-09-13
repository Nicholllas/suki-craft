<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Services\QrisDynamicPayloadService;

const STATIC_QRIS_PAYLOAD = '00020101021126140010COM.EXAMPL5204581253033605802ID5910SUKI CRAFT6007JAKARTA6304D33A';

test('it converts a static QRIS payload to a dynamic payload with the order total', function () {
    $qrisService = app(QrisDynamicPayloadService::class);
    $dynamicPayload = $qrisService->convert(STATIC_QRIS_PAYLOAD, 125000);

    expect($dynamicPayload)
        ->toBe('00020101021226140010COM.EXAMPL52045812530336054061250005802ID5910SUKI CRAFT6007JAKARTA6304EDBD')
        ->and($qrisService->isValid($dynamicPayload))->toBeTrue();
});

test('it serves a locally generated QRIS image with a localized matching payment card', function (string $locale, string $scanLabel, string $imageAlt) {
    config(['payment.qris_payload' => STATIC_QRIS_PAYLOAD]);
    $order = Order::factory()->create(['total' => 125000]);

    $this->withSession(['locale' => $locale])
        ->get(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]))
        ->assertOk()
        ->assertSee(route('orders.qris.show', ['orderNumber' => $order->order_number, 'token' => $order->public_token]), false)
        ->assertSee('data-qris-payment-card', false)
        ->assertSee('bg-white p-5 text-left shadow-sm', false)
        ->assertSee($scanLabel)
        ->assertSee($imageAlt);

    $this->get(route('orders.qris.show', ['orderNumber' => $order->order_number, 'token' => $order->public_token]))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml')
        ->assertSee('<svg', false);
})->with([
    'Indonesian' => ['id', 'Scan untuk membayar sesuai total pesanan.', 'QRIS pembayaran Suki Craft'],
    'English' => ['en', 'Scan to pay the exact order total.', 'Suki Craft payment QRIS'],
]);

test('it shows a Suki Craft themed bank transfer card for configured bank accounts', function (string $locale, string $manualLabel, string $copyLabel) {
    PaymentSetting::query()->create([
        'bank_account_holder' => 'Suki Craft',
        'bank_account_number' => '1234567890',
        'bank_name' => 'BCA',
    ]);
    $order = Order::factory()->create(['status' => OrderStatus::PENDING_PAYMENT]);

    $this->withSession(['locale' => $locale])
        ->get(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]))
        ->assertOk()
        ->assertSee('data-bank-transfer-card', false)
        ->assertSee('data-bank-code="BCA"', false)
        ->assertSee('data-bank-name', false)
        ->assertSee('Bank Central Asia')
        ->assertSee('whitespace-nowrap', false)
        ->assertDontSee('✿')
        ->assertDontSee('role=&quot;img&quot;', false)
        ->assertSee('1234 5678 90')
        ->assertSee('data-copy-account', false)
        ->assertSee('data-copy-value="1234567890"', false)
        ->assertSee($manualLabel)
        ->assertSee($copyLabel)
        ->assertDontSee(asset('images/banks/bca.png'), false)
        ->assertDontSee('Kirim bukti transfer')
        ->assertDontSee('Send transfer proof');
})->with([
    'Indonesian' => ['id', 'Transfer manual', 'Salin'],
    'English' => ['en', 'Manual bank transfer', 'Copy'],
]);

test('it keeps QRIS available at the configured maximum order amount', function () {
    config([
        'payment.qris_max_order_amount' => 500_000,
        'payment.qris_payload' => STATIC_QRIS_PAYLOAD,
    ]);
    $order = Order::factory()->create([
        'status' => OrderStatus::PENDING_PAYMENT,
        'total' => 500_000,
    ]);
    $qrisUrl = route('orders.qris.show', ['orderNumber' => $order->order_number, 'token' => $order->public_token]);

    $this->get(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]))
        ->assertOk()
        ->assertSee($qrisUrl, false)
        ->assertDontSee('data-qris-disabled', false);

    $this->get($qrisUrl)->assertOk();
});

test('it disables QRIS above the configured maximum order amount in both storefront languages', function (string $locale, string $message) {
    config([
        'payment.qris_max_order_amount' => 500_000,
        'payment.qris_payload' => STATIC_QRIS_PAYLOAD,
    ]);
    $order = Order::factory()->create([
        'status' => OrderStatus::PENDING_PAYMENT,
        'total' => 500_001,
    ]);
    $confirmationUrl = route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]);
    $qrisUrl = route('orders.qris.show', ['orderNumber' => $order->order_number, 'token' => $order->public_token]);

    $this->withSession(['locale' => $locale])
        ->get($confirmationUrl)
        ->assertOk()
        ->assertSee('data-qris-disabled', false)
        ->assertSee('data-qris-payment-card', false)
        ->assertSee($message)
        ->assertDontSee($qrisUrl, false);

    $this->get($qrisUrl)->assertNotFound();
})->with([
    'Indonesian' => ['id', 'Untuk transaksi di atas Rp500.000, silakan gunakan transfer bank.'],
    'English' => ['en', 'For transactions above Rp500.000, please use a bank transfer.'],
]);

test('it rejects an invalid QRIS checksum', function () {
    app(QrisDynamicPayloadService::class)->convert(
        '00020101021126140010COM.EXAMPL5204581253033605802ID5910SUKI CRAFT6007JAKARTA63040000',
        125000,
    );
})->throws(InvalidArgumentException::class, 'Checksum QRIS tidak valid.');

test('it rejects QRIS amounts above the transaction limit', function () {
    app(QrisDynamicPayloadService::class)->convert(STATIC_QRIS_PAYLOAD, 10_000_001);
})->throws(InvalidArgumentException::class, 'Nominal QRIS harus antara Rp1 dan Rp10.000.000.');
