<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'nama' => 'Administrator',
            'email' => 'admin@university.edu',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
        ]);

        User::create([
            'nama' => 'Staff Unit',
            'email' => 'staff@university.edu',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }
}