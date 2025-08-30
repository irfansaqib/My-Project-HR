<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Step 1: Create a pool of 20 employees who do NOT have user accounts yet.
        $employees = Employee::factory()->count(20)->create();

        // Step 2: Now, create user accounts for the first 5 employees from that pool.
        // This models your logic that "not every employee will be a user".
        foreach ($employees->take(5) as $employee) {
            User::create([
                // THE FIX IS HERE: Combine first_name and last_name for the user's name.
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'email' => $employee->email,
                'password' => Hash::make('password'),
                'business_id' => $employee->business_id,
                'role' => 'employee',
            ]);
        }
    }
}