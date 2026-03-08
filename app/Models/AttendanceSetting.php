<?php
// app/Models/AttendanceSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'start_time',
        'end_time',
        'late_threshold_minutes',
        'half_day_threshold_hours',
        'is_period_based',
        
        // Flexible period settings
        'total_periods',
        'first_period_start',
        'first_period_end',
        'period_schedule',
        'enable_overtime_periods',
        'max_overtime_periods',
        
        // Flexible schedule settings
        'flexible_schedule',
        'custom_schedule',
        
        // Original fields
        'working_days',
        'auto_calculate_hours',
        'enable_overtime',
        'overtime_rate',
        'enable_early_departure',
        'early_departure_minutes',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'first_period_start' => 'datetime:H:i',
        'first_period_end' => 'datetime:H:i',
        'is_period_based' => 'boolean',
        'working_days' => 'array',
        'period_schedule' => 'array',
        'custom_schedule' => 'array',
        'auto_calculate_hours' => 'boolean',
        'enable_overtime' => 'boolean',
        'enable_overtime_periods' => 'boolean',
        'enable_early_departure' => 'boolean',
        'flexible_schedule' => 'boolean',
        'late_threshold_minutes' => 'integer',
        'half_day_threshold_hours' => 'decimal:2',
        'overtime_rate' => 'decimal:10,2',
        'early_departure_minutes' => 'integer',
        'total_periods' => 'integer',
        'max_overtime_periods' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Get custom schedule or default
    public function getDailySchedule()
    {
        if ($this->flexible_schedule && !empty($this->custom_schedule)) {
            return $this->custom_schedule;
        }
        
        // Default schedule
        return [
            'start_time' => $this->start_time ? $this->start_time->format('H:i') : '09:00',
            'end_time' => $this->end_time ? $this->end_time->format('H:i') : '17:00',
        ];
    }

    // Get period schedule
    public function getPeriodSchedule()
    {
        if (!empty($this->period_schedule)) {
            return $this->period_schedule;
        }
        
        // Generate default period schedule
        $schedule = [];
        $totalPeriods = $this->total_periods ?? 8;
        
        for ($i = 1; $i <= $totalPeriods; $i++) {
            $schedule[] = [
                'period' => $i,
                'name' => "Period $i",
                'start_time' => $this->calculatePeriodTime($i, 'start'),
                'end_time' => $this->calculatePeriodTime($i, 'end'),
                'duration' => 45, // minutes
            ];
        }
        
        return $schedule;
    }

    private function calculatePeriodTime($periodNumber, $type)
    {
        $start = $this->first_period_start ? $this->first_period_start : now()->setTime(9, 0);
        $end = $this->first_period_end ? $this->first_period_end : now()->setTime(9, 45);
        
        $duration = $start->diffInMinutes($end);
        
        if ($type == 'start') {
            $minutesToAdd = ($periodNumber - 1) * ($duration + 15); // 15 min break between periods
            return $start->copy()->addMinutes($minutesToAdd)->format('H:i');
        } else {
            $minutesToAdd = ($periodNumber * $duration) + (($periodNumber - 1) * 15);
            return $start->copy()->addMinutes($minutesToAdd)->format('H:i');
        }
    }
}