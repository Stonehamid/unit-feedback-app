<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case AKTIF = 'aktif';
    case CUTI = 'cuti';
    case RESIGN = 'resign';

    public static function labels(): array
    {
        return [
            self::AKTIF->value => 'Aktif',
            self::CUTI->value => 'Cuti',
            self::RESIGN->value => 'Resign',
        ];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    public function color(): string
    {
        return match($this) {
            self::AKTIF => 'success',
            self::CUTI => 'warning',
            self::RESIGN => 'danger',
        };
    }
}