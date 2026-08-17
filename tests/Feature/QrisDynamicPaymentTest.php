<?php

use App\Models\Order;
use App\Services\QrisDynamicPayloadService;

const STATIC_QRIS_PAYLOAD = '00020101021126140010COM.EXAMPL5204581253033605802ID5910SUKI CRAFT6007JAKARTA6304D33A';

test('it converts a static QRIS payload to a dynamic payload with the order total', function () {
    $qrisService = app(QrisDynamicPayloadService::class);
    $dynamicPayload = $qrisService->convert(STATIC_QRIS_PAYLOAD, 125000);

    expect($dynamicPayload)
        ->toBe('00020101021226140010COM.EXAMPL52045812530336054061250005802ID5910SUKI CRAFT6007JAKARTA6304EDBD')
        ->and($qrisService->isValid($dynamicPayload))->toBeTrue();
});

test('it serves a locally generated QRIS image for the order total', function () {
    config(['payment.qris_payload' => STATIC_QRIS_PAYLOAD]);
    $order = Order::factory()->create(['total' => 125000]);

    $this->get(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]))
        ->assertOk()
        ->assertSee(route('orders.qris.show', ['orderNumber' => $order->order_number, 'token' => $order->public_token]), false)
        ->assertSee('Scan untuk bayar sesuai total pesanan');

    $this->get(route('orders.qris.show', ['orderNumber' => $order->order_number, 'token' => $order->public_token]))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml')
        ->assertSee('<svg', false);
});

test('it rejects an invalid QRIS checksum', function () {
    app(QrisDynamicPayloadService::class)->convert(
        '00020101021126140010COM.EXAMPL5204581253033605802ID5910SUKI CRAFT6007JAKARTA63040000',
        125000,
    );
})->throws(InvalidArgumentException::class, 'Checksum QRIS tidak valid.');

test('it rejects QRIS amounts above the transaction limit', function () {
    app(QrisDynamicPayloadService::class)->convert(STATIC_QRIS_PAYLOAD, 10_000_001);
})->throws(InvalidArgumentException::class, 'Nominal QRIS harus antara Rp1 dan Rp10.000.000.');
