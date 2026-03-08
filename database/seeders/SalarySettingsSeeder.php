<?php

namespace Database\Seeders;

use App\Models\SalarySetting;
use Illuminate\Database\Seeder;

class SalarySettingsSeeder extends Seeder
{
    public function run()
    {
        SalarySetting::create([
            'company_name' => 'Your Company Name',
            'working_days_per_month' => 26,
            'working_hours_per_day' => 8,
            'enable_income_tax' => true,
            'enable_provident_fund' => true,
            'provident_fund_percentage' => 10,
            'enable_late_deduction' => true,
            'late_deduction_per_hour' => 50,
            'enable_absent_deduction' => true,
            'absent_deduction_per_day' => 500,
            'enable_overtime' => true,
            'overtime_rate_per_hour' => 100,
            'created_by' => 1,
        ]);
    }
}