<?php

namespace App\Services;

use App\Enums\CustomRequestStatus;
use App\Models\CustomRequest;
use Illuminate\Support\Str;

class CustomRequestWhatsAppService
{
    public function customerFollowUpUrl(CustomRequest $customRequest): ?string
    {
        if (! in_array($customRequest->status, [CustomRequestStatus::WAITING_REVIEW, CustomRequestStatus::REVISION_REQUESTED], true)) {
            return null;
        }

        $number = $this->adminNumber();

        if ($number === null) {
            return null;
        }

        $categoryName = $this->categoryName($customRequest);
        $message = "Halo Suki Craft, saya {$customRequest->customer->name} ingin menindaklanjuti request {$customRequest->request_number} untuk {$categoryName}.\n\nStatus saat ini: {$customRequest->status->label()}.";

        return 'https://wa.me/'.$number.'?text='.urlencode($message);
    }

    public function quoteUrl(CustomRequest $customRequest): ?string
    {
        if ($customRequest->status !== CustomRequestStatus::QUOTATION_SENT || $customRequest->quoted_price === null) {
            return null;
        }

        $number = $this->customerNumber($customRequest);

        if ($number === null) {
            return null;
        }

        $categoryName = $this->categoryName($customRequest);
        $approvalUrl = route('customer.custom-requests.show', $customRequest);
        $expiresAt = $customRequest->quote_expires_at?->locale('id')->translatedFormat('d M Y, H.i').' WIB';
        $message = "Halo {$customRequest->customer->name}, penawaran untuk {$categoryName} ({$customRequest->request_number}) sudah tersedia.\n\nHarga penawaran: Rp".number_format((float) $customRequest->quoted_price, 0, ',', '.')."\nBerlaku sampai: {$expiresAt}\n\nBuka link ini untuk menyetujui atau meminta revisi:\n{$approvalUrl}";

        return 'https://wa.me/'.$number.'?text='.urlencode($message);
    }

    private function adminNumber(): ?string
    {
        return $this->normalizePhone((string) config('payment.whatsapp_number'));
    }

    private function categoryName(CustomRequest $customRequest): string
    {
        return $customRequest->custom_category_name ?? $customRequest->customBouquetCategory?->name ?? 'Custom Bouquet';
    }

    private function customerNumber(CustomRequest $customRequest): ?string
    {
        return $this->normalizePhone((string) $customRequest->customer->phone);
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = Str::of($phone)->replaceMatches('/\D+/', '')->toString();

        if (blank($phone)) {
            return null;
        }

        if (str_starts_with($phone, '0')) {
            return '62'.substr($phone, 1);
        }

        return $phone;
    }
}
