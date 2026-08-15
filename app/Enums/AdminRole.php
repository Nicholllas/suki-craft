<?php

namespace App\Enums;

enum AdminRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case STAFF = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::STAFF => 'Staff',
        };
    }
}
