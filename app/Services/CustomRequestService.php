<?php

namespace App\Services;

use App\Enums\CustomRequestStatus;
use App\Models\Admin;
use App\Models\CartItemGroup;
use App\Models\CustomBouquetCategory;
use App\Models\Customer;
use App\Models\CustomRequest;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class CustomRequestService
{
    public function __construct(private CartService $cartService) {}

    /**
     * @return array<string, array{label: string, min: int, max: int|null}>
     */
    public static function budgetRanges(): array
    {
        return [
            'under_250k' => ['label' => 'Di bawah Rp250.000', 'min' => 0, 'max' => 250000],
            '250k_500k' => ['label' => 'Rp250.000 - Rp500.000', 'min' => 250000, 'max' => 500000],
            '500k_750k' => ['label' => 'Rp500.000 - Rp750.000', 'min' => 500000, 'max' => 750000],
            '750k_1m' => ['label' => 'Rp750.000 - Rp1.000.000', 'min' => 750000, 'max' => 1000000],
            'over_1m' => ['label' => 'Di atas Rp1.000.000', 'min' => 1000000, 'max' => null],
        ];
    }

    public function create(Customer $customer, CustomBouquetCategory $customBouquetCategory, array $data, ?UploadedFile $referenceImage = null): CustomRequest
    {
        $customBouquetCategory = CustomBouquetCategory::query()
            ->where('is_active', true)
            ->findOrFail($customBouquetCategory->id);
        $product = Product::query()
            ->where('is_active', true)
            ->where('is_custom_request', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->firstOrFail();
        $budgetRange = self::budgetRanges()[$data['budget_range']] ?? null;

        if ($budgetRange === null) {
            throw ValidationException::withMessages(['budget_range' => ['Rentang anggaran tidak valid.']]);
        }

        $referenceImagePath = $referenceImage?->store('custom-request-references', 'local');

        try {
            return DB::transaction(function () use ($budgetRange, $customer, $customBouquetCategory, $data, $product, $referenceImagePath): CustomRequest {
                $customRequest = CustomRequest::query()->create([
                    'additional_notes' => $data['additional_notes'] ?? null,
                    'budget_max' => $budgetRange['max'],
                    'budget_min' => $budgetRange['min'],
                    'customer_id' => $customer->id,
                    'custom_bouquet_category_id' => $customBouquetCategory->id,
                    'custom_category_name' => $customBouquetCategory->name,
                    'item_source' => $data['item_source'],
                    'needed_date' => $data['needed_date'],
                    'product_id' => $product->id,
                    'reference_image_path' => $referenceImagePath,
                    'request_kind' => 'full_bouquet',
                    'status' => CustomRequestStatus::WAITING_REVIEW,
                    'wrapping_preference' => $data['wrapping_preference'] ?? null,
                ]);
                $customRequest->update(['request_number' => sprintf('CR%05d', $customRequest->id)]);
                $customRequest->items()->createMany(collect($data['items'])->map(fn (array $item): array => [
                    'item_name' => $item['name'],
                    'notes' => $item['notes'] ?? null,
                    'quantity' => $item['quantity'],
                ])->all());
                $customRequest->histories()->create([
                    'actor_type' => 'customer',
                    'customer_id' => $customer->id,
                    'note' => "Permintaan buket {$customBouquetCategory->name} dibuat.",
                    'status' => CustomRequestStatus::WAITING_REVIEW,
                ]);

                return $customRequest->load(['customBouquetCategory', 'items', 'product']);
            }, attempts: 3);
        } catch (Throwable $exception) {
            if ($referenceImagePath !== null) {
                Storage::disk('local')->delete($referenceImagePath);
            }

            throw $exception;
        }
    }

    public function quote(CustomRequest $customRequest, array $data, Admin $admin): CustomRequest
    {
        return DB::transaction(function () use ($admin, $customRequest, $data): CustomRequest {
            $customRequest = CustomRequest::query()->lockForUpdate()->findOrFail($customRequest->id);

            if (! in_array($customRequest->status, [CustomRequestStatus::WAITING_REVIEW, CustomRequestStatus::REVISION_REQUESTED, CustomRequestStatus::QUOTATION_SENT], true)) {
                throw ValidationException::withMessages(['custom_request' => ['Status permintaan tidak dapat diberi penawaran.']]);
            }

            $customRequest->update([
                'approved_at' => null,
                'converted_to_cart_at' => null,
                'quote_expires_at' => Carbon::parse($data['quote_expires_at']),
                'quote_note' => $data['quote_note'] ?? null,
                'quoted_at' => Carbon::now('Asia/Jakarta'),
                'quoted_price' => $data['quoted_price'],
                'status' => CustomRequestStatus::QUOTATION_SENT,
            ]);
            $customRequest->histories()->create([
                'actor_type' => 'admin',
                'admin_id' => $admin->id,
                'note' => 'Penawaran harga dikirim kepada pelanggan.',
                'status' => CustomRequestStatus::QUOTATION_SENT,
            ]);

            return $customRequest->refresh();
        }, attempts: 3);
    }

    public function requestRevision(CustomRequest $customRequest, Customer $customer, string $note, ?int $counterOffer = null): CustomRequest
    {
        $quoteExpired = false;
        $updatedCustomRequest = DB::transaction(function () use ($counterOffer, $customer, $customRequest, $note, &$quoteExpired): ?CustomRequest {
            $customRequest = CustomRequest::query()->whereBelongsTo($customer, 'customer')->lockForUpdate()->findOrFail($customRequest->id);

            if ($customRequest->status !== CustomRequestStatus::QUOTATION_SENT) {
                throw ValidationException::withMessages(['custom_request' => ['Revisi hanya dapat diminta setelah penawaran dikirim.']]);
            }

            if ($this->expireLockedRequestIfNeeded($customRequest)) {
                $quoteExpired = true;

                return null;
            }

            $customRequest->update(['status' => CustomRequestStatus::REVISION_REQUESTED]);
            $customRequest->histories()->create([
                'actor_type' => 'customer',
                'customer_id' => $customer->id,
                'note' => $counterOffer === null ? $note : 'Usulan harga pelanggan: Rp'.number_format($counterOffer, 0, ',', '.')."\n\n{$note}",
                'status' => CustomRequestStatus::REVISION_REQUESTED,
            ]);

            return $customRequest->refresh();
        }, attempts: 3);

        if ($quoteExpired) {
            throw ValidationException::withMessages(['custom_request' => ['Penawaran ini sudah kedaluwarsa.']]);
        }

        return $updatedCustomRequest;
    }

    public function reject(CustomRequest $customRequest, string $reason, Admin $admin): CustomRequest
    {
        return DB::transaction(function () use ($admin, $customRequest, $reason): CustomRequest {
            $customRequest = CustomRequest::query()->lockForUpdate()->findOrFail($customRequest->id);

            if (! in_array($customRequest->status, [CustomRequestStatus::WAITING_REVIEW, CustomRequestStatus::REVISION_REQUESTED, CustomRequestStatus::QUOTATION_SENT], true)) {
                throw ValidationException::withMessages(['custom_request' => ['Status permintaan tidak dapat ditolak.']]);
            }

            $customRequest->update(['status' => CustomRequestStatus::REJECTED]);
            $customRequest->histories()->create([
                'actor_type' => 'admin',
                'admin_id' => $admin->id,
                'note' => $reason,
                'status' => CustomRequestStatus::REJECTED,
            ]);

            return $customRequest->refresh();
        }, attempts: 3);
    }

    public function approveAndAddToCart(CustomRequest $customRequest, Customer $customer): CartItemGroup
    {
        $quoteExpired = false;
        $cartItemGroup = DB::transaction(function () use ($customer, $customRequest, &$quoteExpired): ?CartItemGroup {
            $customRequest = CustomRequest::query()->whereBelongsTo($customer, 'customer')->lockForUpdate()->findOrFail($customRequest->id);

            if ($customRequest->status !== CustomRequestStatus::QUOTATION_SENT) {
                throw ValidationException::withMessages(['custom_request' => ['Penawaran ini tidak dapat ditambahkan ke keranjang.']]);
            }

            if ($this->expireLockedRequestIfNeeded($customRequest)) {
                $quoteExpired = true;

                return null;
            }

            $cartItemGroup = $this->cartService->addApprovedCustomRequest($customRequest, $customer);
            $customRequest->update([
                'approved_at' => Carbon::now('Asia/Jakarta'),
                'converted_to_cart_at' => Carbon::now('Asia/Jakarta'),
                'status' => CustomRequestStatus::CONVERTED_TO_CART,
            ]);
            $customRequest->histories()->create([
                'actor_type' => 'customer',
                'customer_id' => $customer->id,
                'note' => 'Penawaran disetujui dan ditambahkan ke keranjang.',
                'status' => CustomRequestStatus::CONVERTED_TO_CART,
            ]);

            return $cartItemGroup;
        }, attempts: 3);

        if ($quoteExpired) {
            throw ValidationException::withMessages(['custom_request' => ['Penawaran ini sudah kedaluwarsa.']]);
        }

        return $cartItemGroup;
    }

    public function expireIfQuoteExpired(CustomRequest $customRequest): bool
    {
        return DB::transaction(function () use ($customRequest): bool {
            $customRequest = CustomRequest::query()->lockForUpdate()->findOrFail($customRequest->id);

            return $this->expireLockedRequestIfNeeded($customRequest);
        }, attempts: 3);
    }

    private function expireLockedRequestIfNeeded(CustomRequest $customRequest): bool
    {
        if ($customRequest->status !== CustomRequestStatus::QUOTATION_SENT || $customRequest->quote_expires_at === null || $customRequest->quote_expires_at->isFuture()) {
            return false;
        }

        $customRequest->update(['status' => CustomRequestStatus::EXPIRED]);
        $customRequest->histories()->create([
            'actor_type' => 'system',
            'note' => 'Penawaran kedaluwarsa sebelum disetujui.',
            'status' => CustomRequestStatus::EXPIRED,
        ]);

        return true;
    }
}
