<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuoteService
{
    public function quote(Order $order, array $quoteData, Admin $admin): void
    {
        DB::transaction(function () use ($admin, $order, $quoteData): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! in_array($lockedOrder->status, [OrderStatus::AWAITING_QUOTE, OrderStatus::AWAITING_APPROVAL], true)) {
                throw ValidationException::withMessages(['quote' => 'Penawaran hanya dapat dibuat untuk pesanan custom yang menunggu harga.']);
            }

            $subtotal = (int) $quoteData['quote_subtotal'];
            $total = $subtotal + (int) $lockedOrder->delivery_fee - (int) $lockedOrder->discount_amount;

            $lockedOrder->update([
                'quote_approved_at' => null,
                'quote_expires_at' => $quoteData['quote_expires_at'],
                'quote_note' => $quoteData['quote_note'] ?? null,
                'quoted_at' => now(),
                'status' => OrderStatus::AWAITING_APPROVAL,
                'subtotal' => $subtotal,
                'total' => $total,
            ]);
            $lockedOrder->statusHistories()->create([
                'changed_by' => $admin->id,
                'note' => 'Penawaran harga Rp'.number_format($total, 0, ',', '.').' dikirim. Berlaku sampai '.$lockedOrder->quote_expires_at->locale('id')->translatedFormat('d F Y, H.i').' WIB.',
                'status' => OrderStatus::AWAITING_APPROVAL,
            ]);
        });
    }

    public function approve(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status !== OrderStatus::AWAITING_APPROVAL) {
                throw ValidationException::withMessages(['quote' => 'Penawaran ini tidak lagi dapat disetujui.']);
            }

            if ($this->quoteHasExpired($lockedOrder)) {
                $this->expireQuote($lockedOrder);

                throw ValidationException::withMessages(['quote' => 'Masa berlaku penawaran telah berakhir. Silakan hubungi admin untuk penawaran baru.']);
            }

            $lockedOrder->update([
                'quote_approved_at' => now(),
                'status' => OrderStatus::PENDING_PAYMENT,
            ]);
            $lockedOrder->statusHistories()->create([
                'note' => 'Pelanggan menyetujui penawaran harga dan dapat melanjutkan pembayaran.',
                'status' => OrderStatus::PENDING_PAYMENT,
            ]);
        });
    }

    public function expireIfQuoteExpired(Order $order): bool
    {
        return DB::transaction(function () use ($order): bool {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status !== OrderStatus::AWAITING_APPROVAL || ! $this->quoteHasExpired($lockedOrder)) {
                return false;
            }

            $this->expireQuote($lockedOrder);

            return true;
        });
    }

    private function quoteHasExpired(Order $order): bool
    {
        return $order->quote_expires_at !== null && now('Asia/Jakarta')->greaterThanOrEqualTo($order->quote_expires_at);
    }

    private function expireQuote(Order $order): void
    {
        $order->update([
            'cancellation_reason' => 'Masa berlaku penawaran custom telah berakhir.',
            'status' => OrderStatus::CANCELLED,
        ]);
        $order->statusHistories()->create([
            'note' => 'Pesanan custom dibatalkan otomatis karena penawaran tidak disetujui sebelum masa berlakunya berakhir.',
            'status' => OrderStatus::CANCELLED,
        ]);
    }
}
