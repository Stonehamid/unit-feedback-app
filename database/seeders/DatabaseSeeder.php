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
        // Clear existing data
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
            'created_at' => now(),
        ]);

        $reviewer = User::create([
            'name' => 'Reviewer User',
            'email' => 'reviewer@example.com',
            'password' => Hash::make('password'),
            'role' => 'reviewer',
            'created_at' => now(),
        ]);

        // Create Units
        $units = Unit::factory()->count(15)->create();

        // Create Ratings, Messages, Reports
        foreach ($units as $unit) {
            // Create 3-7 ratings per unit
            Rating::factory()->count(rand(3, 7))->create([
                'unit_id' => $unit->id,
            ]);

            // Create 1-4 messages per unit
            Message::factory()->count(rand(1, 4))->create([
                'unit_id' => $unit->id,
            ]);

            // Create 0-2 reports per unit (some units have no reports)
            if (rand(0, 1)) {
                Report::factory()->count(rand(0, 2))->create([
                    'admin_id' => $admin->id,
                    'unit_id' => $unit->id,
                ]);
            }
        }

        // Update unit average ratings
        foreach ($units as $unit) {
            $avgRating = Rating::where('unit_id', $unit->id)
                ->where('is_approved', true)
                ->avg('rating');
            
            $unit->update(['avg_rating' => round($avgRating ?: 0, 2)]);
        }

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('👤 Admin: admin@example.com / password');
        $this->command->info('📝 Reviewer: reviewer@example.com / password');
    }
}