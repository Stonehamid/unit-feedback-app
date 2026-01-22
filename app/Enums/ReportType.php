<?php

namespace App\Enums;

enum ReportType: string
{
    case MASALAH = 'masalah';
    case SARAN = 'saran';
    case KELUHAN = 'keluhan';
    case PUJIAN = 'pujian';
    case LAINNYA = 'lainnya';

    public static function labels(): array
    {
        return [
            self::MASALAH->value => 'Masalah',
            self::SARAN->value => 'Saran',
            self::KELUHAN->value => 'Keluhan',
            self::PUJIAN->value => 'Pujian',
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

    public function icon(): string
    {
        return match($this) {
            self::MASALAH => 'exclamation-triangle',
            self::SARAN => 'lightbulb',
            self::KELUHAN => 'frown',
            self::PUJIAN => 'star',
            self::LAINNYA => 'question-circle',
        };
    }
}