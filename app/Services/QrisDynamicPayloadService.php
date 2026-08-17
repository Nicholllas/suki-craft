<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentSetting;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class QrisDynamicPayloadService
{
    private const MAX_TRANSACTION_AMOUNT = 10_000_000;

    public function paymentConfiguration(): array
    {
        $payment = config('payment');
        $setting = PaymentSetting::query()->first();

        if ($setting === null) {
            return $payment;
        }

        return [
            ...$payment,
            'bank_account_holder' => $setting->bank_account_holder,
            'bank_account_number' => $setting->bank_account_number,
            'bank_name' => $setting->bank_name,
            'qris_path' => $setting->qris_image_path ? Storage::disk('public')->url($setting->qris_image_path) : $payment['qris_path'],
            'qris_payload' => $setting->qris_payload,
        ];
    }

    public function isEnabled(array $payment): bool
    {
        return filled($payment['qris_payload']);
    }

    public function svgFor(Order $order, array $payment): ?string
    {
        $staticPayload = $payment['qris_payload'];

        if (! is_string($staticPayload) || blank($staticPayload)) {
            return null;
        }

        $qrCode = new QrCode(
            data: $this->convert($staticPayload, $this->orderAmount($order)),
            encoding: new Encoding('ISO-8859-1'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 512,
            margin: 12,
        );

        return (new SvgWriter)->write($qrCode)->getString();
    }

    public function convert(string $staticPayload, int $amount): string
    {
        if ($amount < 1 || $amount > self::MAX_TRANSACTION_AMOUNT) {
            throw new InvalidArgumentException('Nominal QRIS harus antara Rp1 dan Rp10.000.000.');
        }

        $elements = $this->parse($staticPayload);
        $this->ensureValidStaticPayload($elements, $staticPayload);

        $dynamicPayload = '';
        $amountAdded = false;

        foreach ($elements as $element) {
            if ($element['tag'] === '63' || in_array($element['tag'], ['54', '55', '56', '57'], true)) {
                continue;
            }

            if ($element['tag'] === '01') {
                $dynamicPayload .= $this->tlv('01', '12');

                continue;
            }

            if ($element['tag'] === '58' && ! $amountAdded) {
                $dynamicPayload .= $this->tlv('54', (string) $amount);
                $amountAdded = true;
            }

            $dynamicPayload .= $this->tlv($element['tag'], $element['value']);
        }

        if (! $amountAdded) {
            $dynamicPayload .= $this->tlv('54', (string) $amount);
        }

        $payloadWithCrcTag = $dynamicPayload.'6304';

        return $payloadWithCrcTag.$this->crc16($payloadWithCrcTag);
    }

    public function isValid(string $payload): bool
    {
        try {
            $elements = $this->parse($payload);
            $checksum = $elements[array_key_last($elements)];

            return $checksum['tag'] === '63'
                && $checksum['value'] === $this->crc16(substr($payload, 0, -4));
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * @return list<array{tag: string, value: string}>
     */
    private function parse(string $payload): array
    {
        $elements = [];
        $offset = 0;
        $payloadLength = strlen($payload);

        while ($offset < $payloadLength) {
            if ($payloadLength - $offset < 4) {
                throw new InvalidArgumentException('Format payload QRIS tidak lengkap.');
            }

            $tag = substr($payload, $offset, 2);
            $valueLength = substr($payload, $offset + 2, 2);

            if (! ctype_digit($tag) || ! ctype_digit($valueLength)) {
                throw new InvalidArgumentException('Format TLV QRIS tidak valid.');
            }

            $offset += 4;
            $length = (int) $valueLength;

            if ($payloadLength - $offset < $length) {
                throw new InvalidArgumentException('Panjang nilai TLV QRIS tidak valid.');
            }

            $elements[] = [
                'tag' => $tag,
                'value' => substr($payload, $offset, $length),
            ];
            $offset += $length;
        }

        if ($elements === []) {
            throw new InvalidArgumentException('Payload QRIS kosong.');
        }

        return $elements;
    }

    /**
     * @param  list<array{tag: string, value: string}>  $elements
     */
    private function ensureValidStaticPayload(array $elements, string $payload): void
    {
        $tags = [];
        $hasMerchantAccount = false;

        foreach ($elements as $element) {
            $tags[$element['tag']] = $element['value'];
            $hasMerchantAccount = $hasMerchantAccount || ((int) $element['tag'] >= 26 && (int) $element['tag'] <= 51);
        }

        $checksum = $elements[array_key_last($elements)];

        if ($checksum['tag'] !== '63' || $checksum['value'] !== $this->crc16(substr($payload, 0, -4))) {
            throw new InvalidArgumentException('Checksum QRIS tidak valid.');
        }

        if (($tags['00'] ?? null) !== '01' || ($tags['01'] ?? null) !== '11' || ($tags['53'] ?? null) !== '360') {
            throw new InvalidArgumentException('Payload harus berupa QRIS statis berdenominasi Rupiah.');
        }

        if (! $hasMerchantAccount || ! isset($tags['58'], $tags['59'], $tags['60'])) {
            throw new InvalidArgumentException('Data merchant QRIS tidak lengkap.');
        }
    }

    private function orderAmount(Order $order): int
    {
        $amount = (int) $order->total;

        if ((float) $order->total !== (float) $amount) {
            throw new InvalidArgumentException('Total pesanan QRIS harus berupa Rupiah tanpa pecahan.');
        }

        return $amount;
    }

    private function tlv(string $tag, string $value): string
    {
        $length = strlen($value);

        if ($length > 99) {
            throw new InvalidArgumentException('Nilai TLV QRIS terlalu panjang.');
        }

        return $tag.str_pad((string) $length, 2, '0', STR_PAD_LEFT).$value;
    }

    private function crc16(string $payload): string
    {
        $crc = 0xFFFF;

        for ($offset = 0, $length = strlen($payload); $offset < $length; $offset++) {
            $crc ^= ord($payload[$offset]) << 8;

            for ($bit = 0; $bit < 8; $bit++) {
                $crc = ($crc & 0x8000) !== 0
                    ? (($crc << 1) ^ 0x1021) & 0xFFFF
                    : ($crc << 1) & 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
