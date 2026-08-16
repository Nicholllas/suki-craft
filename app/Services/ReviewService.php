<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReviewService
{
    public function canReview(OrderItem $orderItem): bool
    {
        $orderItem->loadMissing('order');

        return $orderItem->order?->status === OrderStatus::DELIVERED && ! $orderItem->review()->exists();
    }

    public function submit(OrderItem $orderItem, array $data, ?Customer $customer = null): Review
    {
        $photoPath = null;

        try {
            return DB::transaction(function () use ($customer, $data, $orderItem, &$photoPath): Review {
                $lockedOrderItem = OrderItem::query()->with('order')->lockForUpdate()->findOrFail($orderItem->id);

                if (! $this->canReview($lockedOrderItem)) {
                    throw ValidationException::withMessages(['review' => 'Ulasan untuk produk ini tidak dapat dikirim.']);
                }

                if ($customer && $lockedOrderItem->order->customer_id !== $customer->id) {
                    throw ValidationException::withMessages(['review' => 'Pesanan ini bukan milik akun Anda.']);
                }

                if (($data['photo'] ?? null) instanceof UploadedFile) {
                    $photoPath = $data['photo']->store('reviews', 'public');
                }

                return Review::query()->create([
                    'comment' => $data['comment'] ?? null,
                    'customer_id' => $customer?->id,
                    'order_id' => $lockedOrderItem->order_id,
                    'order_item_id' => $lockedOrderItem->id,
                    'photo_path' => $photoPath,
                    'product_id' => $lockedOrderItem->product_id,
                    'rating' => $data['rating'],
                    'reviewer_name' => $lockedOrderItem->order->customer_name,
                    'status' => ReviewStatus::PENDING,
                ]);
            });
        } catch (Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }
    }

    public function approve(Review $review, Admin $admin): void
    {
        $this->ensureAdminExists($admin);

        DB::transaction(function () use ($review): void {
            $lockedReview = Review::query()->lockForUpdate()->findOrFail($review->id);
            $this->ensurePending($lockedReview);
            $lockedReview->update(['reviewed_at' => now(), 'status' => ReviewStatus::APPROVED]);
        });
    }

    public function reject(Review $review, Admin $admin, string $reason): void
    {
        $this->ensureAdminExists($admin);

        if (blank($reason)) {
            throw ValidationException::withMessages(['reason' => 'Alasan penolakan wajib diisi.']);
        }

        DB::transaction(function () use ($reason, $review): void {
            $lockedReview = Review::query()->lockForUpdate()->findOrFail($review->id);
            $this->ensurePending($lockedReview);
            $lockedReview->update([
                'admin_note' => $reason,
                'reviewed_at' => now(),
                'status' => ReviewStatus::REJECTED,
            ]);
        });
    }

    public function getApprovedForProduct(Product $product): LengthAwarePaginator
    {
        return Review::query()
            ->whereBelongsTo($product)
            ->where('status', ReviewStatus::APPROVED)
            ->latest('reviewed_at')
            ->paginate(10);
    }

    public function getAverageRating(Product $product): float
    {
        return (float) Review::query()
            ->whereBelongsTo($product)
            ->where('status', ReviewStatus::APPROVED)
            ->avg('rating');
    }

    private function ensureAdminExists(Admin $admin): void
    {
        if (! $admin->exists) {
            throw ValidationException::withMessages(['admin' => 'Admin tidak valid.']);
        }
    }

    private function ensurePending(Review $review): void
    {
        if ($review->status !== ReviewStatus::PENDING) {
            throw ValidationException::withMessages(['review' => 'Ulasan ini sudah dimoderasi.']);
        }
    }
}
