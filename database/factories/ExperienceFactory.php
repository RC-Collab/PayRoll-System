<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExperienceFactory extends Factory
{
    protected $model = Experience::class;

    public function definition()
    {
        $startDate = $this->faker->dateTime('-10 years');
        $endDate = $this->faker->dateTime('-1 year');
        
        return [
            'employee_id' => Employee::factory(),
            'company' => $this->faker->company(),
            'position' => $this->faker->jobTitle(),
            'location' => $this->faker->city(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_current' => false,
            'description' => $this->faker->paragraph(),
            'achievements' => $this->faker->sentence(),
            'certificate_path' => null,
        ];
    }
}
