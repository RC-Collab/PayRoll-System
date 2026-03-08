<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class MonthlySalary extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'salary_month',
        'working_days',
        'present_days',
        'absent_days',
        'leave_days',
        'unauthorized_leave_days',
        'overtime_hours',
        'overtime_rate',
        'overtime_amount',
        'basic_salary',
        'dearness_allowance',
        'house_rent_allowance',
        'medical_allowance',
        'tiffin_allowance',
        'transport_allowance',
        'special_allowance',
        'provident_fund',
        'citizen_investment',
        'income_tax',
        'penalty_leave_deduction',
        'late_deduction',
        'late_deduction_amount',
        'absent_deduction_amount',
        'insurance_amount',
        'bonus_amount',
        'advance_deduction',
        'total_allowances',
        'total_deductions',
        'gross_salary',
        'net_salary',
        'payment_status',
        'payment_date',
        'payment_method',
        'payment_bank',
        'transaction_reference',
        'cheque_number',
        'paid_amount',
        'calculated_by',
        'calculated_at',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'remarks',
        'calculation_details',
        'custom_components',
        'attendance_summary',
    ];

    protected $casts = [
        // salary_month is stored as a simple YYYY-MM string; we handle
        // conversion manually in the accessor/mutator below.
        'salary_month' => 'string',
        'payment_date' => 'date',
        'calculated_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'calculation_details' => 'array',
        'custom_components' => 'array',
        'attendance_summary' => 'array',
        'paid_amount' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'dearness_allowance' => 'decimal:2',
        'house_rent_allowance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'tiffin_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'special_allowance' => 'decimal:2',
        'provident_fund' => 'decimal:2',
        'citizen_investment' => 'decimal:2',
        'income_tax' => 'decimal:2',
        'penalty_leave_deduction' => 'decimal:2',
        'late_deduction' => 'decimal:2',
        'late_deduction_amount' => 'decimal:2',
        'absent_deduction_amount' => 'decimal:2',
        'insurance_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];
    
    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /*
     * -------------------------------------------------------------------------
     * Custom accessors / mutators for salary_month
     * -------------------------------------------------------------------------
     * The column itself is a VARCHAR(7) holding "YYYY-MM".  We still want
     * to work with Carbon instances throughout the application, so we cast
     * to/from Carbon here instead of relying on the normal date cast which
     * would produce a full date (and trigger the truncation error).
     */
    public function setSalaryMonthAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['salary_month'] = null;
            return;
        }

        $this->attributes['salary_month'] = Carbon::parse($value)->format('Y-m');
    }

    public function getSalaryMonthAttribute($value)
    {
        // attach day-01 to provide a deterministic Carbon instance
        return $value ? Carbon::parse($value . '-01') : null;
    }

    // Accessor already existed; update to use the new salary_month behaviour.
    public function getMonthYearAttribute()
    {
        if (! $this->salary_month) {
            return null;
        }

        return Carbon::parse($this->salary_month . '-01')->format('F Y');
    }

    public function calculatedBy()
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }


    public function getFormattedNetSalaryAttribute()
    {
        return 'रु ' . number_format($this->net_salary, 2);
    }

    // Method to calculate custom components for employee
    public function calculateCustomComponents($employee, $baseSalary, $attendanceData = [])
    {
        $components = [];
        $totalAllowances = 0;
        $totalDeductions = 0;
        
        $customComponents = SalaryCustomComponent::active()->get();
        $departmentId = $employee->departments->first()->id ?? null;
        
        foreach ($customComponents as $component) {
            if ($component->appliesToEmployee($employee->id, $departmentId)) {
                $amount = $component->calculateAmount($employee, $baseSalary);
                
                $components[] = [
                    'id' => $component->id,
                    'name' => $component->name,
                    'type' => $component->type,
                    'amount' => $amount,
                    'calculation_type' => $component->calculation_type,
                ];
                
                if ($component->type === 'allowance') {
                    $totalAllowances += $amount;
                } elseif ($component->type === 'deduction') {
                    $totalDeductions += $amount;
                }
            }
        }
        
        return [
            'components' => $components,
            'total_allowances' => $totalAllowances,
            'total_deductions' => $totalDeductions,
        ];
    }

    // Method to get custom components breakdown
    public function getCustomComponentsBreakdown()
    {
        if (!$this->custom_components) {
            return [
                'allowances' => [],
                'deductions' => [],
                'others' => [],
            ];
        }
        
        $breakdown = [
            'allowances' => [],
            'deductions' => [],
            'others' => [],
        ];
        
        foreach ($this->custom_components as $component) {
            $item = [
                'name' => $component['name'],
                'amount' => $component['amount'],
                'calculation_type' => $component['calculation_type'],
            ];
            
            switch ($component['type']) {
                case 'allowance':
                    $breakdown['allowances'][] = $item;
                    break;
                case 'deduction':
                    $breakdown['deductions'][] = $item;
                    break;
                default:
                    $breakdown['others'][] = $item;
            }
        }
        
        return $breakdown;
    }

    // Method to get attendance summary
    public function getAttendanceSummary()
    {
        if (!$this->attendance_summary) {
            return [
                'present_days' => 0,
                'absent_days' => 0,
                'leave_days' => 0,
                'late_days' => 0,
                'total_late_minutes' => 0,
                'working_days' => 0,
            ];
        }

        // cast string->array if necessary
        if (is_string($this->attendance_summary)) {
            $decoded = json_decode($this->attendance_summary, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $this->attendance_summary;
    }

    // Method to calculate total with custom components
    public function getTotalWithCustomComponents()
    {
        $standardAllowances = $this->dearness_allowance + 
                             $this->house_rent_allowance + 
                             $this->medical_allowance + 
                             $this->tiffin_allowance + 
                             $this->transport_allowance + 
                             $this->special_allowance + 
                             $this->overtime_amount;
        
        $standardDeductions = $this->provident_fund + 
                            $this->citizen_investment + 
                            $this->income_tax + 
                            $this->penalty_leave_deduction + 
                            $this->late_deduction + 
                            $this->late_deduction_amount + 
                            $this->absent_deduction_amount + 
                            $this->insurance_amount + 
                            $this->advance_deduction;
        
        $custom = $this->getCustomComponentsBreakdown();
        
        $customAllowances = array_sum(array_column($custom['allowances'], 'amount'));
        $customDeductions = array_sum(array_column($custom['deductions'], 'amount'));
        
        return [
            'total_standard_allowances' => $standardAllowances,
            'total_custom_allowances' => $customAllowances,
            'total_allowances' => $standardAllowances + $customAllowances,
            'total_standard_deductions' => $standardDeductions,
            'total_custom_deductions' => $customDeductions,
            'total_deductions' => $standardDeductions + $customDeductions,
            'bonus' => $this->bonus_amount,
        ];
    }

    // Method to recalculate net salary with custom components
    public function recalculateWithCustomComponents()
    {
        $totals = $this->getTotalWithCustomComponents();
        
        $this->total_allowances = $totals['total_allowances'];
        $this->total_deductions = $totals['total_deductions'];
        
        // Add bonus to gross salary
        $grossSalary = $this->basic_salary + $totals['total_allowances'] + $this->bonus_amount;
        $this->gross_salary = $grossSalary;
        
        $this->net_salary = $grossSalary - $totals['total_deductions'];
        
        return $this;
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeCalculated($query)
    {
        return $query->where('payment_status', 'calculated');
    }

    public function scopeForMonth($query, $month)
    {
        return $query->where('salary_month', $month);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // Scope for salary with custom components
    public function scopeWithCustomComponents($query)
    {
        return $query->whereNotNull('custom_components');
    }

    // Scope for salary with attendance issues
    public function scopeWithAttendanceDeductions($query)
    {
        return $query->where(function($q) {
            $q->where('late_deduction_amount', '>', 0)
              ->orWhere('absent_deduction_amount', '>', 0)
              ->orWhere('penalty_leave_deduction', '>', 0);
        });
    }
}