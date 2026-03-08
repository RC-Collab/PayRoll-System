<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryStructure extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'basic_salary',
        'dearness_allowance',
        'house_rent_allowance',
        'medical_allowance',
        'tiffin_allowance',
        'transport_allowance',
        'special_allowance',
        'overtime_rate',
        'provident_fund_enabled',
        'provident_fund_percentage',
        'citizen_investment',
        'tax_exempt',
        'tax_deduction_source',
        'daily_rate',
        'hourly_rate',
        'remarks',
        'is_active',
        'effective_from',
        'effective_to',
        'is_current',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'dearness_allowance' => 'decimal:2',
        'house_rent_allowance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'tiffin_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'special_allowance' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'provident_fund_enabled' => 'boolean',
        'provident_fund_percentage' => 'decimal:2',
        'citizen_investment' => 'decimal:2',
        'tax_exempt' => 'boolean',
        'tax_deduction_source' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_current' => 'boolean',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Accessors
    public function getTotalAllowancesAttribute()
    {
        return $this->dearness_allowance + 
               $this->house_rent_allowance + 
               $this->medical_allowance + 
               $this->tiffin_allowance + 
               $this->transport_allowance + 
               $this->special_allowance;
    }

    public function getGrossSalaryAttribute()
    {
        return $this->basic_salary + $this->total_allowances;
    }

    // Calculate daily and hourly rates if not set
    public function getCalculatedDailyRateAttribute()
    {
        if ($this->daily_rate) {
            return $this->daily_rate;
        }
        return $this->basic_salary / 26; // 26 working days
    }

    public function getCalculatedHourlyRateAttribute()
    {
        if ($this->hourly_rate) {
            return $this->hourly_rate;
        }
        return $this->calculated_daily_rate / 8; // 8 working hours per day
    }
}