<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\AttendanceSetting;
use Carbon\Carbon;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('respects employee schedule and rounding when calculating overtime and late', function () {
    // create employee and settings
    $employee = Employee::factory()->create();
    $settings = AttendanceSetting::create([
        'employee_id' => $employee->id,
        'start_time' => Carbon::parse('09:00'),
        'end_time' => Carbon::parse('17:00'),
        'late_threshold_minutes' => 15,
        'enable_overtime' => true,
    ]);

    $date = today()->toDateString();

    // check-in at 09:10 (rounded to 09:00) and check-out at 17:50 (rounded to 18:00)
    $attendance = Attendance::create([
        'employee_id' => $employee->id,
        'date' => $date,
        'check_in' => Carbon::parse($date.' 09:10'),
        'check_out' => Carbon::parse($date.' 17:50'),
    ]);

    $attendance->recalcWithSettings();

    // fifteen minutes late threshold but rounded check-in should not count as late
    expect($attendance->late_minutes)->toBe(0);
    expect($attendance->is_late)->toBeFalse();

    // total hours should be computed from 09:00 to 18:00 = 9 hours
    expect($attendance->total_hours)->toBe(9.0);

    // overtime should be time after 17:00 i.e. 1 hour => 60 minutes
    expect($attendance->overtime_minutes)->toBe(60);
    expect($attendance->overtime)->toBe(1.0);
    // regular hours are total minus overtime
    expect($attendance->regular_hours)->toBe(8.0);
});
