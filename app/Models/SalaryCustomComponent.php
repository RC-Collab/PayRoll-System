<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryCustomComponent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'calculation_type',
        'fixed_amount',
        'percentage_amount',
        'formula',
        'formula_variables',
        'is_active',
        'apply_to_all',
        'applicable_departments',
        'applicable_employees',
        'description',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'fixed_amount' => 'decimal:2',
        'percentage_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'apply_to_all' => 'boolean',
        'applicable_departments' => 'array',
        'applicable_employees' => 'array',
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

    // Check if component applies to employee
    public function appliesToEmployee($employeeId, $departmentId = null)
    {
        if (!$this->is_active) return false;
        
        if ($this->apply_to_all) return true;
        
        if ($this->applicable_employees && in_array($employeeId, $this->applicable_employees)) {
            return true;
        }
        
        if ($departmentId && $this->applicable_departments && in_array($departmentId, $this->applicable_departments)) {
            return true;
        }
        
        return false;
    }

    // Calculate amount for employee
    public function calculateAmount($employee, $baseSalary = 0)
    {
        switch ($this->calculation_type) {
            case 'fixed':
                return $this->fixed_amount;
                
            case 'percentage':
                return $baseSalary * ($this->percentage_amount / 100);
                
            case 'formula':
                // Implement formula calculation
                return $this->calculateFormula($employee, $baseSalary);
                
            default:
                return 0;
        }
    }

    private function calculateFormula($employee, $baseSalary)
    {
        // Basic formula calculation - can be extended
        $formula = strtolower($this->formula);
        
        // Simple formula examples
        if ($formula === 'basic_salary * 0.1') {
            return $baseSalary * 0.1;
        }
        
        // Add more formula parsing logic here
        return 0;
    }
}