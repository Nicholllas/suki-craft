<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu moderasi',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
        };
    }
}
