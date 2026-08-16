<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\Courier;
use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class DeliveryService
{
    public function __construct(private InventoryService $inventoryService) {}

    public function assignCourier(Order $order, Courier $courier, ?Admin $changedBy = null): void
    {
        DB::transaction(function () use ($changedBy, $courier, $order) {
            $lockedOrder = $this->lockedOrder($order->id);
            $this->ensureStatus($lockedOrder, [OrderStatus::PAYMENT_CONFIRMED, OrderStatus::PROCESSING, OrderStatus::OUT_FOR_DELIVERY]);
            $lockedCourier = $this->lockedActiveCourier($courier->id);

            $lockedOrder->update(['courier_id' => $lockedCourier->id]);
            $lockedOrder->statusHistories()->create([
                'changed_by' => $changedBy?->id,
                'note' => 'Kurir '.$lockedCourier->name.' ditugaskan untuk pengiriman.',
                'status' => $lockedOrder->status,
            ]);
        });
    }

    public function markProcessing(Order $order, ?Admin $changedBy = null): void
    {
        DB::transaction(function () use ($changedBy, $order) {
            $lockedOrder = $this->lockedOrder($order->id);
            $this->ensureStatus($lockedOrder, [OrderStatus::PAYMENT_CONFIRMED]);
            $this->updateOrderStatus($lockedOrder, OrderStatus::PROCESSING, 'Buket mulai disiapkan.', $changedBy);
            $this->inventoryService->deductForOrder($lockedOrder);
        });
    }

    public function markOutForDelivery(Order $order, ?Admin $changedBy = null): void
    {
        DB::transaction(function () use ($changedBy, $order) {
            $lockedOrder = $this->lockedOrder($order->id);
            $this->ensureStatus($lockedOrder, [OrderStatus::PROCESSING]);

            if (! $lockedOrder->courier_id) {
                throw ValidationException::withMessages(['courier_id' => 'Tugaskan kurir sebelum menandai pesanan sedang dikirim.']);
            }

            $this->updateOrderStatus($lockedOrder, OrderStatus::OUT_FOR_DELIVERY, 'Pesanan sedang diantar oleh kurir.', $changedBy);
        });
    }

    public function markDelivered(Order $order, ?UploadedFile $proofPhoto = null, ?Admin $changedBy = null): void
    {
        $path = null;

        try {
            DB::transaction(function () use ($changedBy, $order, $proofPhoto, &$path) {
                $lockedOrder = $this->lockedOrder($order->id);
                $this->ensureStatus($lockedOrder, [OrderStatus::OUT_FOR_DELIVERY]);

                if ($proofPhoto) {
                    $path = $proofPhoto->store('delivery-proofs', 'local');
                }

                $lockedOrder->update([
                    'delivered_at' => now(),
                    'delivery_proof_path' => $path,
                ]);
                $this->updateOrderStatus($lockedOrder, OrderStatus::DELIVERED, 'Pesanan telah diterima pelanggan.', $changedBy);
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }
    }

    public function markCancelled(Order $order, string $reason, ?Admin $changedBy = null): void
    {
        DB::transaction(function () use ($changedBy, $order, $reason) {
            $lockedOrder = $this->lockedOrder($order->id);
            $this->ensureStatus($lockedOrder, [OrderStatus::PAYMENT_CONFIRMED, OrderStatus::PROCESSING, OrderStatus::OUT_FOR_DELIVERY]);

            if (blank($reason)) {
                throw ValidationException::withMessages(['reason' => 'Alasan pembatalan wajib diisi.']);
            }

            $lockedOrder->update(['cancellation_reason' => $reason]);
            $this->updateOrderStatus($lockedOrder, OrderStatus::CANCELLED, 'Pesanan dibatalkan: '.$reason, $changedBy);
        });
    }

    private function ensureStatus(Order $order, array $statuses): void
    {
        if (! in_array($order->status, $statuses, true)) {
            throw ValidationException::withMessages(['delivery' => 'Status pesanan ini tidak dapat diperbarui untuk tahap pengiriman tersebut.']);
        }
    }

    private function lockedActiveCourier(int $courierId): Courier
    {
        $courier = Courier::query()->whereKey($courierId)->where('is_active', true)->lockForUpdate()->first();

        if (! $courier) {
            throw ValidationException::withMessages(['courier_id' => 'Kurir yang dipilih tidak aktif atau tidak tersedia.']);
        }

        return $courier;
    }

    private function lockedOrder(int $orderId): Order
    {
        return Order::query()->lockForUpdate()->findOrFail($orderId);
    }

    private function updateOrderStatus(Order $order, OrderStatus $status, string $note, ?Admin $changedBy): void
    {
        $order->update(['status' => $status]);
        $order->statusHistories()->create([
            'changed_by' => $changedBy?->id,
            'note' => $note,
            'status' => $status,
        ]);
    }
}
