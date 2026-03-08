<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmergencyContact;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmergencyContactFactory extends Factory
{
    protected $model = EmergencyContact::class;

    public function definition()
    {
        return [
            'employee_id' => Employee::factory(),
            'name' => $this->faker->name(),
            'relationship' => $this->faker->randomElement(['Parent', 'Sibling', 'Spouse', 'Relative', 'Friend']),
            'phone' => $this->faker->numerify('##########'),
            'phone2' => $this->faker->numerify('##########'),
            'email' => $this->faker->email(),
            'address' => $this->faker->address(),
            'is_primary' => false,
        ];
    }
}
