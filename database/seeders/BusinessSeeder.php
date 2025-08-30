<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Step 1: Create the business and link it to the user with ID 1
        DB::table('businesses')->insert([
            'user_id' => 1, // This links to the owner created in UserSeeder
            'legal_name' => 'My Company (Legal) Name',
            'business_name' => 'My Awesome Company',
            'business_type' => 'Sole Proprietorship',
            'registration_number' => '12345-6789012-3',
            'address' => '123 Main Street, Rawalpindi, Pakistan',
            'phone_number' => '051-1234567',
            'email' => 'contact@awesomecompany.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Step 2: Update the owner user to link them back to the business.
        DB::table('users')->where('id', 1)->update(['business_id' => 1]);
    }
}