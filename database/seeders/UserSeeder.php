<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder now correctly creates the owner and assigns the 'Owner' role.
     */
    public function run(): void
    {
        // Use the User model to create the user
        $owner = User::create([
            'name' => 'Business Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
            // The 'role' column is no longer needed here as Spatie handles it
        ]);

        // Use the Spatie package to assign the 'Owner' role
        $owner->assignRole('Owner');
    }
}