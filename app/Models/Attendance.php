<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    // only allow the fields we actually care about in the simplified system
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'total_hours',
        'is_late',
        'late_minutes',
        'overtime_minutes',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'is_late' => 'boolean',
        'late_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'total_hours' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Round a timestamp to nearest 30‑minute increment using 15‑minute tolerance.
     * - minutes <= 15  → round down to hour
     * - minutes >= 45  → round up to next hour
     * - otherwise      → round to :30
     */
    public function roundAttendanceTime(Carbon $time): Carbon
    {
        $minute = $time->minute;
        if ($minute <= 15) {
            return $time->copy()->setMinute(0)->setSecond(0);
        }
        if ($minute >= 45) {
            return $time->copy()->addHour()->setMinute(0)->setSecond(0);
        }
        // between 16 and 44
        return $time->copy()->setMinute(30)->setSecond(0);
    }

    /**
     * Recalculate derived fields using employee schedule and rounding rules.
     */
    public function recalcWithSettings(): void
    {
        $settings = $this->employee->attendanceSettings;
        $date = $this->date->copy();

        $start = $date->copy()->setTime(9, 0);
        $end = $date->copy()->setTime(17, 0);
        $lateThreshold = 15;

        if ($settings) {
            if ($settings->start_time) {
                $start = $date->copy()->setTimeFromTimeString($settings->start_time->format('H:i'));
            }
            if ($settings->end_time) {
                $end = $date->copy()->setTimeFromTimeString($settings->end_time->format('H:i'));
            }
            $lateThreshold = $settings->late_threshold_minutes ?? $lateThreshold;
        }

        if ($this->check_in) {
            $ci = $this->roundAttendanceTime($this->check_in);
            $lateCutoff = $start->copy()->addMinutes($lateThreshold);
            $this->late_minutes = $ci->gt($lateCutoff) ? $ci->diffInMinutes($lateCutoff) : 0;
            $this->is_late = $ci->gt($lateCutoff);
        }

        if ($this->check_in && $this->check_out) {
            $ci = $this->roundAttendanceTime($this->check_in);
            $co = $this->roundAttendanceTime($this->check_out);
            $this->total_hours = $co->floatDiffInHours($ci, true);
            // only record overtime if employee has it enabled
            if ($settings && !$settings->enable_overtime) {
                $this->overtime_minutes = 0;
            } else {
                // use absolute difference so we don't get negative values
                $this->overtime_minutes = $co->gt($end) ? $co->diffInMinutes($end, true) : 0;
            }

            if (!$this->check_in && !$this->check_out) {
                $this->status = 'absent';
            } elseif ($this->overtime_minutes > 0) {
                $this->status = 'overtime';
            } else {
                $this->status = 'present';
            }
        }
    }

    // --------------------------------------------------
    // accessor helpers for simple check-in/out system
    // --------------------------------------------------

    /**
     * Calculate duration in hours between check in/out
     */
    public function getDurationAttribute()
    {
        if (!$this->check_in || !$this->check_out) {
            return null;
        }

        // use rounded times for duration calculation as well
        $ci = $this->roundAttendanceTime($this->check_in);
        $co = $this->roundAttendanceTime($this->check_out);

        return $co->floatDiffInHours($ci, true);
    }

    /**
     * Determine if the employee is late (after 9am of the attendance date)
     */
    public function getIsLateAttribute($value)
    {
        // stored value takes precedence
        if (!is_null($value)) {
            return (bool) $value;
        }

        if (!$this->check_in) {
            return false;
        }

        // determine using employee's schedule and rounding rules
        $settings = $this->employee->attendanceSettings;
        $start = $this->date->copy()->setTime(9, 0);
        $threshold = 15;

        if ($settings) {
            if ($settings->start_time) {
                $start = $this->date->copy()->setTimeFromTimeString($settings->start_time->format('H:i'));
            }
            $threshold = $settings->late_threshold_minutes ?? $threshold;
        }

        $ci = $this->roundAttendanceTime($this->check_in);
        $lateCutoff = $start->copy()->addMinutes($threshold);
        return $ci->gt($lateCutoff);
    }

    /**
     * Overtime hours (after 5pm) as decimal hours
     */
    public function getOvertimeAttribute()
    {
        // if minutes stored use that to calculate hours
        if (!is_null($this->overtime_minutes)) {
            return round($this->overtime_minutes / 60, 2);
        }

        if (!$this->check_out) {
            return 0;
        }

        $settings = $this->employee->attendanceSettings;
        $end = $this->date->copy()->setTime(17, 0);
        if ($settings && $settings->end_time) {
            $end = $this->date->copy()->setTimeFromTimeString($settings->end_time->format('H:i'));
        }

        $co = $this->roundAttendanceTime($this->check_out);
        if ($co->lte($end)) {
            return 0;
        }

        return $co->floatDiffInHours($end, true);
    }

    /**
     * Regular hours (total minus overtime) as decimal hours.
     */
    public function getRegularHoursAttribute()
    {
        if (is_null($this->total_hours)) {
            return 0;
        }

        return max(0, round($this->total_hours - $this->overtime, 2));
    }

    /**
     * Automatic status if not explicitly set
     */
    public function getStatusAttribute($value)
    {
        if ($value) {
            return $value;
        }

        if (!$this->check_in && !$this->check_out) {
            return 'absent';
        }

        $settings = $this->employee->attendanceSettings;
        $end = $this->date->copy()->setTime(17, 0);
        if ($settings && $settings->end_time) {
            $end = $this->date->copy()->setTimeFromTimeString($settings->end_time->format('H:i'));
        }

        $co = $this->check_out ? $this->roundAttendanceTime($this->check_out) : null;
        if ($co && $co->gt($end)) {
            return 'overtime';
        }

        return 'present';
    }

}