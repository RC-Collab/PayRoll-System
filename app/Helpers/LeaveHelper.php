<?php

namespace App\Helpers;

use Carbon\Carbon;

class LeaveHelper
{
    /**
     * Get CSS class color for leave type badge
     */
    public static function getLeaveTypeColor($type)
    {
        return leave_type_color($type);
    }

    /**
     * Format leave days display
     */
    public static function formatLeaveDays($days)
    {
        return format_leave_days($days);
    }

    /**
     * Get leave type display name
     */
    public static function getLeaveTypeName($type)
    {
        return leave_type_name($type);
    }

    /**
     * Get status badge color
     */
    public static function getStatusColor($status)
    {
        return leave_status_color($status);
    }

    /**
     * Calculate leave entitlements based on employee type
     */
    public static function getLeaveEntitlements($employeeType)
    {
        return get_leave_entitlements($employeeType);
    }

    /**
     * Calculate working days between two dates (excluding weekends)
     */
    public static function calculateWorkingDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $totalDays = 0;
        for ($date = $start; $date->lte($end); $date->addDay()) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($date->dayOfWeek !== 0 && $date->dayOfWeek !== 6) {
                $totalDays++;
            }
        }
        
        return $totalDays;
    }
}