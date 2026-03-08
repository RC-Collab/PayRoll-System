<?php

// Leave helper functions
if (!function_exists('leave_type_color')) {
    function leave_type_color($type)
    {
        $colors = [
            'sick' => 'danger',
            'casual' => 'info',
            'annual' => 'success',
            'maternity' => 'purple',
            'paternity' => 'teal',
            'bereavement' => 'secondary',
            'unpaid' => 'warning',
        ];
        
        return $colors[$type] ?? 'primary';
    }
}

if (!function_exists('format_leave_days')) {
    function format_leave_days($days)
    {
        if ($days == floor($days)) {
            return $days . ' day' . ($days > 1 ? 's' : '');
        } else {
            return floor($days) . '.5 days';
        }
    }
}

if (!function_exists('leave_type_name')) {
    function leave_type_name($type)
    {
        $names = [
            'sick' => 'Sick Leave',
            'casual' => 'Casual Leave',
            'annual' => 'Annual Leave',
            'maternity' => 'Maternity Leave',
            'paternity' => 'Paternity Leave',
            'bereavement' => 'Bereavement Leave',
            'unpaid' => 'Unpaid Leave',
        ];
        
        return $names[$type] ?? ucfirst($type);
    }
}

if (!function_exists('leave_status_color')) {
    function leave_status_color($status)
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
        ];
        
        return $colors[$status] ?? 'secondary';
    }
}

// Employee helper functions
if (!function_exists('employee_full_name')) {
    function employee_full_name($employee)
    {
        if (!$employee) return '';
        
        if (is_array($employee)) {
            $employee = (object) $employee;
        }
        
        return trim(
            ($employee->first_name ?? '') . ' ' . 
            ($employee->middle_name ? $employee->middle_name . ' ' : '') . 
            ($employee->last_name ?? '')
        );
    }
}

if (!function_exists('format_nepali_date')) {
    function format_nepali_date($date, $format = 'd M, Y')
    {
        if (!$date) return 'N/A';
        
        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return 'Invalid Date';
        }
    }
}

// Salary helper functions
if (!function_exists('format_currency')) {
    function format_currency($amount)
    {
        if (!is_numeric($amount)) return 'रु 0.00';
        
        return 'रु ' . number_format($amount, 2);
    }
}

if (!function_exists('calculate_nepal_tax')) {
    function calculate_nepal_tax($annualSalary)
    {
        if (!is_numeric($annualSalary) || $annualSalary <= 0) {
            return 0;
        }
        
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
        
        return round($tax, 2);
    }
}

// Validation helper functions
if (!function_exists('is_valid_nepali_mobile')) {
    function is_valid_nepali_mobile($number)
    {
        // Nepali mobile numbers start with 98, 97, or 96
        $pattern = '/^(98|97|96)[0-9]{8}$/';
        
        // Remove spaces and special characters
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);
        
        return preg_match($pattern, $cleanNumber) === 1;
    }
}

if (!function_exists('is_valid_citizenship_number')) {
    function is_valid_citizenship_number($number)
    {
        // Nepali citizenship number pattern: 1-99/[0-9][0-9]-[0-9][0-9]-[0-9][0-9][0-9]
        $pattern = '/^\d{1,2}\/\d{2}-\d{2}-\d{3}$/';
        
        return preg_match($pattern, $number) === 1;
    }
}

// Department helper functions
if (!function_exists('get_department_name')) {
    function get_department_name($employee)
    {
        if (!$employee || !method_exists($employee, 'departments')) {
            return 'N/A';
        }
        
        if ($employee->departments && $employee->departments->count() > 0) {
            return $employee->departments->first()->name ?? 'N/A';
        }
        
        return 'Not Assigned';
    }
}

// Employee status helper
if (!function_exists('employee_status_badge')) {
    function employee_status_badge($status)
    {
        $colors = [
            'active' => 'success',
            'inactive' => 'secondary',
            'on-leave' => 'warning',
            'suspended' => 'danger',
            'terminated' => 'dark',
        ];
        
        $color = $colors[$status] ?? 'secondary';
        $display = str_replace('-', ' ', ucfirst($status));
        
        return "<span class='badge bg-{$color}'>{$display}</span>";
    }
}

// Leave entitlements helper
if (!function_exists('get_leave_entitlements')) {
    function get_leave_entitlements($employeeType)
    {
        $entitlements = [
            'permanent' => ['sick' => 15, 'casual' => 12, 'annual' => 18],
            'contract' => ['sick' => 10, 'casual' => 8, 'annual' => 12],
            'temporary' => ['sick' => 7, 'casual' => 5, 'annual' => 0],
            'probation' => ['sick' => 5, 'casual' => 3, 'annual' => 0],
            'part-time' => ['sick' => 5, 'casual' => 3, 'annual' => 0],
        ];
        
        return $entitlements[$employeeType] ?? $entitlements['contract'];
    }
}