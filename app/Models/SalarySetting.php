<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalarySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'enable_late_deduction',
        'late_deduction_per_hour',
        'enable_absent_deduction',
        'absent_deduction_per_day',
        'enable_leave_deduction',
        'leave_deduction_per_day',
        'enable_overtime',
        'overtime_rate_per_hour',
        'working_days_per_month',
        'working_hours_per_day',
        'enable_provident_fund',
        'provident_fund_percentage',
        'enable_income_tax',
        'enable_bonus',
        'bonus_type',
        'bonus_amount',
        'custom_fields',
        'formula_settings',
        'created_by',
    ];

    protected $casts = [
        'enable_late_deduction' => 'boolean',
        'late_deduction_per_hour' => 'decimal:2',
        'enable_absent_deduction' => 'boolean',
        'absent_deduction_per_day' => 'decimal:2',
        'enable_leave_deduction' => 'boolean',
        'leave_deduction_per_day' => 'decimal:2',
        'enable_overtime' => 'boolean',
        'overtime_rate_per_hour' => 'decimal:2',
        'working_days_per_month' => 'decimal:2',
        'working_hours_per_day' => 'decimal:2',
        'enable_provident_fund' => 'boolean',
        'provident_fund_percentage' => 'decimal:2',
        'enable_income_tax' => 'boolean',
        'enable_bonus' => 'boolean',
        'bonus_amount' => 'decimal:2',
        'custom_fields' => 'array',
        'formula_settings' => 'array',
    ];
}