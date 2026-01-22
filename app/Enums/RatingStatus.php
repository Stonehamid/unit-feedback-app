<?php

namespace App\Enums;

enum RatingStatus: string
{
    case PENDING = 'pending';
    case DIBALAS = 'dibalas';
    case SELESAI = 'selesai';

    public static function labels(): array
    {
        return [
            self::PENDING->value => 'Menunggu',
            self::DIBALAS->value => 'Dibalas',
            self::SELESAI->value => 'Selesai',
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
            self::PENDING => 'warning',
            self::DIBALAS => 'info',
            self::SELESAI => 'success',
        };
    }
}