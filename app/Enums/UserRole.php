<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super_admin';

    public static function labels(): array
    {
        return [
            self::ADMIN->value => 'Admin',
            self::SUPER_ADMIN->value => 'Super Admin',
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

    public function permissions(): array
    {
        return match($this) {
            self::ADMIN => [
                'view_dashboard',
                'manage_ratings',
                'manage_reports',
                'view_units',
                'send_messages',
            ],
            self::SUPER_ADMIN => [
                'view_dashboard',
                'manage_ratings',
                'manage_reports',
                'manage_units',
                'manage_employees',
                'manage_users',
                'send_messages',
                'export_data',
                'system_settings',
            ],
        };
    }
}