<?php

namespace App\Enums;

enum UnitType: string
{
    case KESEHATAN = 'kesehatan';
    case AKADEMIK = 'akademik';
    case ADMINISTRASI = 'administrasi';
    case FASILITAS = 'fasilitas';
    case LAINNYA = 'lainnya';

    public static function labels(): array
    {
        return [
            self::KESEHATAN->value => 'Kesehatan',
            self::AKADEMIK->value => 'Akademik',
            self::ADMINISTRASI->value => 'Administrasi',
            self::FASILITAS->value => 'Fasilitas',
            self::LAINNYA->value => 'Lainnya',
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
}