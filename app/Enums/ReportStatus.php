<?php

namespace App\Enums;

enum ReportStatus: string
{
    case BARU = 'baru';
    case DIPROSES = 'diproses';
    case SELESAI = 'selesai';
    case DITOLAK = 'ditolak';

    public static function labels(): array
    {
        return [
            self::BARU->value => 'Baru',
            self::DIPROSES->value => 'Diproses',
            self::SELESAI->value => 'Selesai',
            self::DITOLAK->value => 'Ditolak',
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
            self::BARU => 'secondary',
            self::DIPROSES => 'primary',
            self::SELESAI => 'success',
            self::DITOLAK => 'danger',
        };
    }
}