<?php

namespace App\Enums;

enum VisitorAction: string
{
    case RATING = 'rating';
    case REPORT = 'report';
    case VISIT = 'visit';
    case VIEW = 'view';

    public static function labels(): array
    {
        return [
            self::RATING->value => 'Memberi Rating',
            self::REPORT->value => 'Mengirim Laporan',
            self::VISIT->value => 'Mengunjungi Unit',
            self::VIEW->value => 'Melihat Unit',
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
            self::RATING => 'star',
            self::REPORT => 'flag',
            self::VISIT => 'location-arrow',
            self::VIEW => 'eye',
        };
    }
}