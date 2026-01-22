<?php

namespace App\Enums;

enum MessageType: string
{
    case SARAN = 'saran';
    case INSTRUKSI = 'instruksi';
    case PENGUMUMAN = 'pengumuman';
    case LAINNYA = 'lainnya';

    public static function labels(): array
    {
        return [
            self::SARAN->value => 'Saran',
            self::INSTRUKSI->value => 'Instruksi',
            self::PENGUMUMAN->value => 'Pengumuman',
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