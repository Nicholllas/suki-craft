<?php

use App\Enums\AdminRole;
use App\Enums\OrderStatus;
use App\Enums\PaymentProofStatus;
use App\Models\Admin;
use App\Models\Order;
use App\Models\PaymentProof;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = Admin::create([
        'email' => 'verifikator@example.com',
        'is_active' => true,
        'name' => 'Rani Verifikator',
        'password' => 'password',
        'role' => AdminRole::ADMIN,
    ]);
});

test('a customer can upload a payment proof for a pending order', function () {
    Storage::fake('local');
    $order = Order::factory()->create(['status' => OrderStatus::PENDING_PAYMENT]);

    $response = $this->post(route('orders.payment-proofs.store', [
        'orderNumber' => $order->order_number,
        'token' => $order->public_token,
    ]), ['proof' => UploadedFile::fake()->image('bukti-bayar.png')]);

    $response->assertRedirect(route('orders.confirmation', [
        'orderNumber' => $order->order_number,
        'token' => $order->public_token,
    ]));
    expect($order->refresh()->status)->toBe(OrderStatus::AWAITING_VERIFICATION)
        ->and($order->paymentProofs()->count())->toBe(1)
        ->and($order->statusHistories()->latest()->value('status'))->toBe(OrderStatus::AWAITING_VERIFICATION);

    $proof = $order->paymentProofs()->sole();
    expect($proof->status)->toBe(PaymentProofStatus::PENDING)
        ->and($proof->uploaded_at)->not->toBeNull();
    Storage::disk('local')->assertExists($proof->path);
});

test('an admin can view orders awaiting payment verification', function () {
    $order = Order::factory()->create(['status' => OrderStatus::AWAITING_VERIFICATION]);
    PaymentProof::factory()->for($order)->create();

    $this->actingAs($this->admin, 'admin')
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee($order->order_number);
});

test('an admin can approve a pending payment proof', function () {
    $order = Order::factory()->create(['status' => OrderStatus::AWAITING_VERIFICATION]);
    $proof = PaymentProof::factory()->for($order)->create();

    $this->actingAs($this->admin, 'admin')
        ->patch(route('admin.orders.payment-proofs.approve', ['order' => $order, 'paymentProof' => $proof]))
        ->assertRedirect(route('admin.orders.show', $order));

    expect($order->refresh()->status)->toBe(OrderStatus::PAYMENT_CONFIRMED)
        ->and($proof->refresh()->status)->toBe(PaymentProofStatus::APPROVED)
        ->and($proof->verified_by)->toBe($this->admin->id)
        ->and($proof->verified_at)->not->toBeNull()
        ->and($order->statusHistories()->latest()->value('status'))->toBe(OrderStatus::PAYMENT_CONFIRMED);
});

test('a rejected payment proof lets a customer upload another proof', function () {
    Storage::fake('local');
    $order = Order::factory()->create(['status' => OrderStatus::AWAITING_VERIFICATION]);
    $proof = PaymentProof::factory()->for($order)->create();

    $this->actingAs($this->admin, 'admin')
        ->patch(route('admin.orders.payment-proofs.reject', ['order' => $order, 'paymentProof' => $proof]), [
            'reason' => 'Nominal pada bukti pembayaran belum sesuai total pesanan.',
        ])
        ->assertRedirect(route('admin.orders.show', $order));

    expect($order->refresh()->status)->toBe(OrderStatus::PENDING_PAYMENT)
        ->and($proof->refresh()->status)->toBe(PaymentProofStatus::REJECTED)
        ->and($proof->rejection_reason)->toBe('Nominal pada bukti pembayaran belum sesuai total pesanan.');

    $this->post(route('orders.payment-proofs.store', [
        'orderNumber' => $order->order_number,
        'token' => $order->public_token,
    ]), ['proof' => UploadedFile::fake()->create('bukti-baru.pdf', 100, 'application/pdf')])
        ->assertRedirect();

    expect($order->refresh()->status)->toBe(OrderStatus::AWAITING_VERIFICATION)
        ->and($order->paymentProofs()->count())->toBe(2);
});

test('payment proof previews require admin authentication', function () {
    $order = Order::factory()->create(['status' => OrderStatus::AWAITING_VERIFICATION]);
    $proof = PaymentProof::factory()->for($order)->create();

    $this->get(route('admin.orders.payment-proofs.preview', ['order' => $order, 'paymentProof' => $proof]))
        ->assertRedirect(route('admin.login'));
});

test('an invalid payment proof cannot be uploaded', function () {
    $order = Order::factory()->create(['status' => OrderStatus::PENDING_PAYMENT]);

    $this->from(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]))
        ->post(route('orders.payment-proofs.store', ['orderNumber' => $order->order_number, 'token' => $order->public_token]), [
            'proof' => UploadedFile::fake()->create('bukti.txt', 100, 'text/plain'),
        ])
        ->assertRedirect(route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]))
        ->assertSessionHasErrors('proof');
    expect($order->refresh()->status)->toBe(OrderStatus::PENDING_PAYMENT)
        ->and($order->paymentProofs()->count())->toBe(0);
});
