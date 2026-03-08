<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Qualification;
use Illuminate\Database\Eloquent\Factories\Factory;

class QualificationFactory extends Factory
{
    protected $model = Qualification::class;

    public function definition()
    {
        return [
            'employee_id' => Employee::factory(),
            'degree' => $this->faker->randomElement(['Bachelor', 'Master', 'PhD']),
            'institution' => $this->faker->company(),
            'board' => $this->faker->randomElement(['TU', 'Kathmandu University', 'Tribhuvan University']),
            'year' => $this->faker->year(),
            'percentage' => $this->faker->numberBetween(60, 100),
            'grade' => $this->faker->randomElement(['A', 'A+', 'B', 'B+']),
            'specialization' => $this->faker->word(),
            'start_date' => $this->faker->dateTime('-10 years'),
            'end_date' => $this->faker->dateTime('-5 years'),
            'is_pursuing' => false,
            'certificate_path' => null,
        ];
    }
}
