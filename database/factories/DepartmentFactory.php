<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'code' => 'DPT' . $this->faker->unique()->numberBetween(100,999),
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement(['academic','administrative','support','technical','operations']),
            'icon' => null,
            'roles' => [],
            'is_active' => true,
        ];
    }
}
