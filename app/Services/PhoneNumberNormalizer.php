<?php

namespace App\Services;

class PhoneNumberNormalizer
{
    public static function equivalentNumbers(string $phone): array
    {
        $normalizedPhone = self::normalize($phone);

        return array_unique([
            $normalizedPhone,
            '0'.substr($normalizedPhone, 2),
            '+'.$normalizedPhone,
        ]);
    }

    public static function isValid(string $phone): bool
    {
        return preg_match(self::validationPattern(), trim($phone)) === 1;
    }

    public static function normalize(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';

        return str_starts_with($phone, '0') ? '62'.substr($phone, 1) : $phone;
    }

    public static function validationRule(): string
    {
        return 'regex:'.self::validationPattern();
    }

    private static function validationPattern(): string
    {
        return '/^(?:(?:0|(?:\+?62))[\s-]*8)[\s-]*[1-9](?:[\s-]*[0-9]){7,11}$/';
    }
}
