<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class, // <-- Run this first
            UserSeeder::class,
            BusinessSeeder::class,
            ShiftSeeder::class,
            EmployeeSeeder::class,
        ]);
    }
}