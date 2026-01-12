<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use App\Models\Rating;
use App\Models\Message;
use App\Models\Report;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        User::truncate();
        Unit::truncate();
        Rating::truncate();
        Message::truncate();
        Report::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create Users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $reviewer = User::create([
            'name' => 'Reviewer User',
            'email' => 'reviewer@example.com',
            'password' => Hash::make('password'),
            'role' => 'reviewer',
        ]);

        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Create Units
        $units = Unit::factory()->count(10)->create();

        // Create Ratings for each unit
        foreach ($units as $unit) {
            // Ratings from reviewer
            Rating::factory()->count(3)->create([
                'unit_id' => $unit->id,
                'reviewer_name' => $reviewer->name,
            ]);

            // Random ratings
            Rating::factory()->count(rand(5, 15))->create([
                'unit_id' => $unit->id,
            ]);

            // Messages for each unit
            Message::factory()->count(rand(2, 8))->create([
                'unit_id' => $unit->id,
            ]);
        }

        // Create Reports - assign random unit ke admin
        Report::factory()->count(5)->create([
            'admin_id' => $admin->id,
            'unit_id' => fn() => $units->random()->id, // Random unit dari yang udah dibuat
        ]);

        // Update semua unit average rating
        foreach ($units as $unit) {
            $unit->updateAverageRating();
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Reviewer: reviewer@example.com / password');
        $this->command->info('User: user@example.com / password');
    }
}