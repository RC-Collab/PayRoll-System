<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'employee_code' => 'EMP' . $this->faker->unique()->numberBetween(1000,9999),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'mobile_number' => $this->faker->unique()->numerify('##########'),
            'gender' => $this->faker->randomElement(['male','female','other']),
            'joining_date' => now(),
            'employee_type' => 'permanent',
            'designation' => 'Employee',
            'bank_name' => 'Test Bank',
            'account_number' => $this->faker->bankAccountNumber,
            'account_holder_name' => $this->faker->name,
            'branch_name' => 'Main',
            'is_active' => true,
            'employment_status' => 'active',
        ];
    }
}
