<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- Make sure to import this

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // This is the correct way to insert data in a seeder
        DB::table('shifts')->insert([
            [
                'name' => 'Morning Shift',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'punch_in_window_start' => '08:30:00',
                'punch_in_window_end' => '09:30:00',
                'grace_period_in_minutes' => 15,
                'weekly_off' => 'Sunday',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Evening Shift',
                'start_time' => '17:00:00',
                'end_time' => '01:00:00',
                'punch_in_window_start' => '16:30:00',
                'punch_in_window_end' => '17:30:00',
                'grace_period_in_minutes' => 15,
                'weekly_off' => 'Sunday',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}