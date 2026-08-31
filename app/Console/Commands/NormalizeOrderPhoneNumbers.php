<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Order;
use App\Models\PromotionUsage;
use App\Services\PhoneNumberNormalizer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:normalize-phone-numbers {--dry-run : Display affected records without changing data}')]
#[Description('Normalize existing customer, order, and promotion usage phone numbers')]
class NormalizeOrderPhoneNumbers extends Command
{
    public function handle(): int
    {
        $orders = $this->normalizePhones(Order::class, 'customer_phone');
        $promotionUsages = $this->normalizePhones(PromotionUsage::class, 'customer_phone');
        $customers = $this->normalizePhones(Customer::class, 'phone', true);
        $verb = $this->option('dry-run') ? 'akan dinormalisasi' : 'dinormalisasi';

        $this->info("{$orders['normalized']} nomor order {$verb}.");
        $this->info("{$promotionUsages['normalized']} nomor penggunaan promo {$verb}.");
        $this->info("{$customers['normalized']} nomor pelanggan {$verb}.");

        $invalidPhones = $orders['invalid'] + $promotionUsages['invalid'] + $customers['invalid'];
        if ($invalidPhones) {
            $this->warn("{$invalidPhones} nomor tidak valid dilewati.");
        }

        $conflictingPhones = $customers['conflicting'];
        if ($conflictingPhones) {
            $this->warn("{$conflictingPhones} nomor pelanggan konflik dilewati.");
        }

        return self::SUCCESS;
    }

    private function normalizePhones(string $modelClass, string $phoneColumn, bool $hasUniquePhone = false): array
    {
        $conflictingPhones = 0;
        $normalizedPhones = 0;
        $invalidPhones = 0;

        $modelClass::query()
            ->select(['id', $phoneColumn])
            ->chunkById(100, function ($records) use ($hasUniquePhone, $modelClass, $phoneColumn, &$conflictingPhones, &$invalidPhones, &$normalizedPhones): void {
                foreach ($records as $record) {
                    $phone = (string) $record->{$phoneColumn};
                    if (! PhoneNumberNormalizer::isValid($phone)) {
                        $invalidPhones++;

                        continue;
                    }

                    $normalizedPhone = PhoneNumberNormalizer::normalize($phone);
                    if ($phone === $normalizedPhone) {
                        continue;
                    }

                    if ($hasUniquePhone && $modelClass::query()->where($phoneColumn, $normalizedPhone)->whereKeyNot($record->getKey())->exists()) {
                        $conflictingPhones++;

                        continue;
                    }

                    $normalizedPhones++;

                    if (! $this->option('dry-run')) {
                        $record->update([$phoneColumn => $normalizedPhone]);
                    }
                }
            });

        return ['conflicting' => $conflictingPhones, 'invalid' => $invalidPhones, 'normalized' => $normalizedPhones];
    }
}
