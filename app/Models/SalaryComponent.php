<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryComponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'calculation_type',
        'fixed_amount',
        'percentage',
        'formula',
        'attendance_field',
        'attendance_rate',
        'is_active',
        'applicable_to',
        'applicable_ids',
        'sort_order',
        'description',
        'created_by',
    ];

    protected $casts = [
        'fixed_amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'attendance_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'applicable_ids' => 'array',
    ];

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAllowances($query)
    {
        return $query->where('type', 'allowance');
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', 'deduction');
    }

    public function scopeBonuses($query)
    {
        return $query->where('type', 'bonus');
    }

    public function scopeApplicableToEmployee($query, $employee)
    {
        return $query->where(function($q) use ($employee) {
            $q->where('applicable_to', 'all')
              ->orWhere(function($q2) use ($employee) {
                  $q2->where('applicable_to', 'employee')
                     ->whereJsonContains('applicable_ids', $employee->id);
              })
              ->orWhere(function($q2) use ($employee) {
                  // Check if applicable to employee's department
                  if ($employee->departments->isNotEmpty()) {
                      $departmentId = $employee->departments->first()->id;
                      $q2->where('applicable_to', 'department')
                         ->whereJsonContains('applicable_ids', $departmentId);
                  }
              })
              ->orWhere(function($q2) use ($employee) {
                  // Check if applicable to employee's designation
                  $q2->where('applicable_to', 'designation')
                     ->whereJsonContains('applicable_ids', $employee->designation);
              });
        });
    }

    // Calculate amount for specific employee
    public function calculateAmount($employee, $baseSalary = 0, $attendanceData = [])
    {
        if (!$this->is_active) {
            return 0;
        }

        switch ($this->calculation_type) {
            case 'fixed':
                return $this->fixed_amount ?? 0;
                
            case 'percentage':
                return $baseSalary * ($this->percentage / 100);
                
            case 'formula':
                return $this->calculateFormulaAmount($employee, $baseSalary, $attendanceData);
                
            case 'attendance_based':
                return $this->calculateAttendanceBasedAmount($attendanceData);
                
            default:
                return 0;
        }
    }

    private function calculateFormulaAmount($employee, $baseSalary, $attendanceData)
    {
        if (empty($this->formula)) {
            return 0;
        }

        // Prepare data for formula
        $data = [
            'basic_salary' => $baseSalary,
            'present_days' => $attendanceData['present_days'] ?? 0,
            'absent_days' => $attendanceData['absent_days'] ?? 0,
            'late_minutes' => $attendanceData['late_minutes'] ?? 0,
            'overtime_hours' => $attendanceData['overtime_hours'] ?? 0,
            'working_days' => $attendanceData['working_days'] ?? 26,
        ];

        // Simple formula evaluation
        $formula = $this->formula;
        foreach ($data as $key => $value) {
            $formula = str_replace('{' . $key . '}', $value, $formula);
        }

        try {
            // Safe evaluation - remove dangerous characters
            $formula = preg_replace('/[^0-9+\-*\/().,\s]/', '', $formula);
            
            if (preg_match('/^[0-9+\-*\/().,\s]+$/', $formula)) {
                return eval("return $formula;");
            }
        } catch (\Exception $e) {
            return 0;
        }

        return 0;
    }

    private function calculateAttendanceBasedAmount($attendanceData)
    {
        if (!$this->attendance_field || !$this->attendance_rate) {
            return 0;
        }

        $fieldValue = $attendanceData[$this->attendance_field] ?? 0;
        
        if ($this->attendance_field === 'late_minutes') {
            // Convert minutes to hours for rate calculation
            $hours = $fieldValue / 60;
            return $hours * $this->attendance_rate;
        }
        
        if ($this->attendance_field === 'absent_days') {
            return $fieldValue * $this->attendance_rate;
        }
        
        if ($this->attendance_field === 'present_days') {
            return $fieldValue * $this->attendance_rate;
        }
        
        return 0;
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        if ($this->calculation_type === 'percentage') {
            return $this->percentage . '% of Basic Salary';
        }
        
        if ($this->calculation_type === 'fixed' && $this->fixed_amount) {
            return 'रु ' . number_format($this->fixed_amount, 2);
        }
        
        if ($this->calculation_type === 'attendance_based' && $this->attendance_rate) {
            return 'रु ' . number_format($this->attendance_rate, 2) . ' per ' . $this->attendance_field;
        }
        
        if ($this->calculation_type === 'formula') {
            return 'Formula: ' . $this->formula;
        }
        
        return 'N/A';
    }

    public function getTypeBadgeAttribute()
    {
        $colors = [
            'allowance' => 'success',
            'deduction' => 'danger',
            'bonus' => 'info',
            'other' => 'secondary',
        ];

        $color = $colors[$this->type] ?? 'secondary';
        
        return '<span class="badge bg-' . $color . '">' . ucfirst($this->type) . '</span>';
    }

    public function getApplicableToTextAttribute()
    {
        $texts = [
            'all' => 'All Employees',
            'department' => 'Specific Departments',
            'employee' => 'Specific Employees',
            'designation' => 'Specific Designations',
        ];

        return $texts[$this->applicable_to] ?? ucfirst($this->applicable_to);
    }

    public function getCalculationTypeTextAttribute()
    {
        $texts = [
            'fixed' => 'Fixed Amount',
            'percentage' => 'Percentage',
            'formula' => 'Formula Based',
            'attendance_based' => 'Attendance Based',
        ];

        return $texts[$this->calculation_type] ?? ucfirst($this->calculation_type);
    }

    /**
     * Determine whether this component should apply to a given employee.
     *
     * This mirrors the logic used in the applicableToEmployee scope but
     * returns a boolean so callers can test individual models.
     */
    public function appliesToEmployee($employee)
    {
        switch ($this->applicable_to) {
            case 'all':
                return true;

            case 'employee':
                return in_array($employee->id, $this->applicable_ids ?? []);

            case 'department':
                if ($employee->departments->isEmpty()) {
                    return false;
                }
                $deptIds = $employee->departments->pluck('id')->toArray();
                return !empty(array_intersect($this->applicable_ids ?? [], $deptIds));

            case 'designation':
                return in_array($employee->designation, $this->applicable_ids ?? []);

            default:
                return false;
        }
    }
}