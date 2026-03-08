<?php
// database/migrations/2026_01_25_165125_ensure_all_employees_have_settings.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Employee;
use App\Models\AttendanceSetting;
use Illuminate\Support\Facades\Log; // Add this import

return new class extends Migration
{
    public function up()
    {
        // Get all employees without settings
        $employees = Employee::whereDoesntHave('attendanceSettings')->get();
        
        foreach ($employees as $employee) {
            AttendanceSetting::create([
                'employee_id' => $employee->id,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'late_threshold_minutes' => 15,
                'half_day_threshold_hours' => 4,
                'is_period_based' => false,
                'working_days' => json_encode([1, 2, 3, 4, 5]),
                'auto_calculate_hours' => true,
                'enable_overtime' => false,
                'enable_early_departure' => false,
                'early_departure_minutes' => 30,
                'total_periods' => 8,
                'first_period_start' => '09:00',
                'first_period_end' => '09:45',
                'flexible_schedule' => false,
                'enable_overtime_periods' => false,
                'max_overtime_periods' => 2,
            ]);
        }
        
        Log::info("Created settings for {$employees->count()} employees");
    }

    public function down()
    {
        // Nothing to rollback
    }
};