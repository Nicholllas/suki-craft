<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentSettingsRequest;
use App\Models\PaymentSetting;
use App\Services\QrisDynamicPayloadService;
use App\Services\QrisImagePayloadDecoder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentSettingsController extends Controller
{
    public function __construct(
        private QrisDynamicPayloadService $qrisDynamicPayloadService,
        private QrisImagePayloadDecoder $qrisImagePayloadDecoder,
    ) {}

    public function edit(): View
    {
        return view('admin.settings.index', ['paymentSetting' => PaymentSetting::query()->first() ?? new PaymentSetting]);
    }

    public function update(UpdatePaymentSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('qris_image') && blank($validated['qris_payload'] ?? null)) {
            try {
                $validated['qris_payload'] = $this->qrisImagePayloadDecoder->decode($request->file('qris_image'));
            } catch (\InvalidArgumentException $exception) {
                throw ValidationException::withMessages(['qris_image' => $exception->getMessage()]);
            }
        }

        if (filled($validated['qris_payload'] ?? null)) {
            try {
                $this->qrisDynamicPayloadService->convert($validated['qris_payload'], 1);
            } catch (\InvalidArgumentException $exception) {
                throw ValidationException::withMessages(['qris_payload' => $exception->getMessage()]);
            }
        }

        $paymentSetting = PaymentSetting::query()->firstOrNew();
        $previousImagePath = $paymentSetting->qris_image_path;

        if ($request->hasFile('qris_image')) {
            $validated['qris_image_path'] = $request->file('qris_image')->store('payment-settings', 'public');
        }

        $paymentSetting->fill($validated)->save();

        if (isset($validated['qris_image_path']) && $previousImagePath) {
            Storage::disk('public')->delete($previousImagePath);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan QRIS berhasil diperbarui.');
    }
}
