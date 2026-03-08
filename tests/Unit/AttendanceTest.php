<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\AttendanceSetting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_respects_employee_schedule_and_rounding_when_calculating_overtime_and_late()
    {
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
        $this->assertEquals(0, $attendance->late_minutes);
        $this->assertFalse($attendance->is_late);

        // total hours should be computed from 09:00 to 18:00 = 9 hours
        $this->assertEquals(9.0, $attendance->total_hours);

        // overtime should be time after 17:00 i.e. 1 hour => 60 minutes
        $this->assertEquals(60, $attendance->overtime_minutes);
        $this->assertEquals(1.0, $attendance->overtime);
    }
}
