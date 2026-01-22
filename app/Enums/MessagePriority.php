<?php

namespace App\Enums;

enum MessagePriority: string
{
    case BIASA = 'biasa';
    case PENTING = 'penting';
    case SANGAT_PENTING = 'sangat_penting';

    public static function labels(): array
    {
        return [
            self::BIASA->value => 'Biasa',
            self::PENTING->value => 'Penting',
            self::SANGAT_PENTING->value => 'Sangat Penting',
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
            self::BIASA => 'secondary',
            self::PENTING => 'warning',
            self::SANGAT_PENTING => 'danger',
        };
    }
}