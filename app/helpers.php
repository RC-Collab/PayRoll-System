<?php

// Leave helper functions
if (!function_exists('leave_type_color')) {
    function leave_type_color($type)
    {
        return \App\Helpers\LeaveHelper::getLeaveTypeColor($type);
    }
}

if (!function_exists('format_leave_days')) {
    function format_leave_days($days)
    {
        return \App\Helpers\LeaveHelper::formatLeaveDays($days);
    }
}

if (!function_exists('leave_type_name')) {
    function leave_type_name($type)
    {
        return \App\Helpers\LeaveHelper::getLeaveTypeName($type);
    }
}

if (!function_exists('leave_status_color')) {
    function leave_status_color($status)
    {
        return \App\Helpers\LeaveHelper::getStatusColor($status);
    }
}

// Employee helper functions
if (!function_exists('employee_full_name')) {
    function employee_full_name($employee)
    {
        return trim($employee->first_name . ' ' . 
               ($employee->middle_name ? $employee->middle_name . ' ' : '') . 
               $employee->last_name);
    }
}

if (!function_exists('format_nepali_date')) {
    function format_nepali_date($date, $format = 'd M, Y')
    {
        if (!$date) return 'N/A';
        
        // You can integrate Nepali date library here
        // For now, use English format
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

// Salary helper functions
if (!function_exists('format_currency')) {
    function format_currency($amount)
    {
        return 'रु ' . number_format($amount, 2);
    }
}

if (!function_exists('calculate_tax')) {
    function calculate_tax($annualSalary)
    {
        // Nepal tax slabs for FY 2080/81
        $tax = 0;
        
        if ($annualSalary <= 500000) {
            $tax = $annualSalary * 0.01; // 1%
        } elseif ($annualSalary <= 700000) {
            $tax = 5000 + ($annualSalary - 500000) * 0.10; // 10%
        } elseif ($annualSalary <= 1000000) {
            $tax = 25000 + ($annualSalary - 700000) * 0.20; // 20%
        } elseif ($annualSalary <= 2000000) {
            $tax = 85000 + ($annualSalary - 1000000) * 0.30; // 30%
        } else {
            $tax = 385000 + ($annualSalary - 2000000) * 0.36; // 36%
        }
        
        return $tax;
    }
}