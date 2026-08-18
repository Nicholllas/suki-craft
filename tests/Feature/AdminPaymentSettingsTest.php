<?php

use App\Enums\AdminRole;
use App\Models\Admin;
use App\Models\PaymentSetting;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = Admin::create([
        'email' => 'settings@example.com',
        'is_active' => true,
        'name' => 'Admin Settings',
        'password' => 'password',
        'role' => AdminRole::ADMIN,
    ]);
});

test('an admin can replace the QRIS image and dynamic payload', function () {
    fakeStorageDisk('public');

    $this->actingAs($this->admin, 'admin')->put(route('admin.settings.update'), [
        'qris_image' => UploadedFile::fake()->createWithContent('qris-baru.png', (new PngWriter)->write(new QrCode(data: staticQrisPayload()))->getString()),
    ])->assertRedirect(route('admin.settings.index'));

    $setting = PaymentSetting::query()->sole();

    expect($setting->qris_payload)->toBe(staticQrisPayload())
        ->and($setting->qris_image_path)->toStartWith('payment-settings/');
    Storage::disk('public')->assertExists($setting->qris_image_path);
});

test('an admin can disable dynamic QRIS without removing the fallback image', function () {
    $setting = PaymentSetting::create(['qris_image_path' => 'payment-settings/qris.png', 'qris_payload' => staticQrisPayload()]);

    $this->actingAs($this->admin, 'admin')->put(route('admin.settings.update'), ['qris_payload' => null])
        ->assertRedirect(route('admin.settings.index'));

    expect($setting->refresh()->qris_payload)->toBeNull()
        ->and($setting->qris_image_path)->toBe('payment-settings/qris.png');
});

function staticQrisPayload(): string
{
    return '00020101021126140010COM.EXAMPL5204581253033605802ID5910SUKI CRAFT6007JAKARTA6304D33A';
}
