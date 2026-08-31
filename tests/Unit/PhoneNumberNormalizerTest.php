<?php

use App\Services\PhoneNumberNormalizer;

test('it normalizes Indonesian phone number formats', function (string $phone): void {
    expect(PhoneNumberNormalizer::normalize($phone))->toBe('6281234567890');
})->with([
    'local format' => '081234567890',
    'international format' => '+6281234567890',
    'international format without plus' => '6281234567890',
    'separators' => '+62 812-3456 7890',
]);

test('it returns legacy equivalents for a normalized phone number', function (): void {
    expect(PhoneNumberNormalizer::equivalentNumbers('6281234567890'))->toBe([
        '6281234567890',
        '081234567890',
        '+6281234567890',
    ]);
});
