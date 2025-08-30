<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => 1,
            'employee_number' => 'EMP' . $this->faker->unique()->numberBetween(1000, 9999),
            
            // This now correctly generates a single 'name'
            'name' => $this->faker->name(),
            
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'cnic' => $this->faker->unique()->numerify('#####-#######-#'),
            'joining_date' => $this->faker->date(),
            'status' => 'active',
        ];
    }
}