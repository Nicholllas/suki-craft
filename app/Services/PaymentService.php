<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentProofStatus;
use App\Models\Admin;
use App\Models\Order;
use App\Models\PaymentProof;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class PaymentService
{
    public function uploadProof(Order $order, UploadedFile $file): PaymentProof
    {
        $path = null;

        try {
            return DB::transaction(function () use ($order, $file, &$path) {
                $lockedOrder = $this->lockedOrder($order->id);

                if ($lockedOrder->status !== OrderStatus::PENDING_PAYMENT) {
                    throw ValidationException::withMessages(['proof' => 'Bukti pembayaran belum dapat diunggah untuk pesanan ini.']);
                }

                $path = $file->store('payment-proofs', 'local');
                $proof = $lockedOrder->paymentProofs()->create([
                    'path' => $path,
                    'status' => PaymentProofStatus::PENDING,
                    'uploaded_at' => now(),
                ]);

                $this->updateOrderStatus($lockedOrder, OrderStatus::AWAITING_VERIFICATION, 'Bukti pembayaran diunggah dan menunggu verifikasi.');

                return $proof;
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }
    }

    public function approve(PaymentProof $proof, Admin $verifier): void
    {
        DB::transaction(function () use ($proof, $verifier) {
            [$lockedOrder, $lockedProof] = $this->lockedOrderAndProof($proof);
            $this->ensureProofCanBeReviewed($lockedOrder, $lockedProof);

            $lockedProof->update([
                'rejection_reason' => null,
                'status' => PaymentProofStatus::APPROVED,
                'verified_at' => now(),
                'verified_by' => $verifier->id,
            ]);
            $this->updateOrderStatus($lockedOrder, OrderStatus::PAYMENT_CONFIRMED, 'Pembayaran dikonfirmasi oleh '.$verifier->name.'.', $verifier);
        });
    }

    public function reject(PaymentProof $proof, Admin $verifier, string $reason): void
    {
        DB::transaction(function () use ($proof, $reason, $verifier) {
            [$lockedOrder, $lockedProof] = $this->lockedOrderAndProof($proof);
            $this->ensureProofCanBeReviewed($lockedOrder, $lockedProof);

            $lockedProof->update([
                'rejection_reason' => $reason,
                'status' => PaymentProofStatus::REJECTED,
                'verified_at' => now(),
                'verified_by' => $verifier->id,
            ]);
            $this->updateOrderStatus($lockedOrder, OrderStatus::PENDING_PAYMENT, 'Bukti pembayaran ditolak: '.$reason, $verifier);
        });
    }

    private function ensureProofCanBeReviewed(Order $order, PaymentProof $proof): void
    {
        if ($order->status !== OrderStatus::AWAITING_VERIFICATION || $proof->status !== PaymentProofStatus::PENDING) {
            throw ValidationException::withMessages(['payment' => 'Bukti pembayaran ini tidak lagi menunggu verifikasi.']);
        }
    }

    private function lockedOrder(int $orderId): Order
    {
        return Order::query()->lockForUpdate()->findOrFail($orderId);
    }

    private function lockedOrderAndProof(PaymentProof $proof): array
    {
        $order = $this->lockedOrder($proof->order_id);
        $lockedProof = PaymentProof::query()->whereBelongsTo($order)->lockForUpdate()->findOrFail($proof->id);

        return [$order, $lockedProof];
    }

    private function updateOrderStatus(Order $order, OrderStatus $status, string $note, ?Admin $changedBy = null): void
    {
        $order->update(['status' => $status]);
        $order->statusHistories()->create([
            'changed_by' => $changedBy?->id,
            'note' => $note,
            'status' => $status,
        ]);
    }
}
