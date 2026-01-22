<?php

namespace App\Enums;

enum ReportPriority: string
{
    case RENDAH = 'rendah';
    case SEDANG = 'sedang';
    case TINGGI = 'tinggi';
    case KRITIS = 'kritis';

    public static function labels(): array
    {
        return [
            self::RENDAH->value => 'Rendah',
            self::SEDANG->value => 'Sedang',
            self::TINGGI->value => 'Tinggi',
            self::KRITIS->value => 'Kritis',
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
            self::RENDAH => 'success',
            self::SEDANG => 'warning',
            self::TINGGI => 'orange',
            self::KRITIS => 'danger',
        };
    }

    public function level(): int
    {
        return match($this) {
            self::RENDAH => 1,
            self::SEDANG => 2,
            self::TINGGI => 3,
            self::KRITIS => 4,
        };
    }
}