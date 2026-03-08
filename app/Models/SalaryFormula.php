<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryFormula extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'variable_name',
        'formula',
        'description',
        'variables',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'variables' => 'array',
    ];

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ========== ADD THESE SCOPES ==========
    
    /**
     * Scope for active formulas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope for inactive formulas
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
    
    /**
     * Scope for formulas by variable name
     */
    public function scopeByVariable($query, $variableName)
    {
        return $query->where('variable_name', $variableName);
    }

    // Evaluate formula with given data
    public function evaluate($data = [])
    {
        $formula = $this->formula;
        
        // Replace variables in formula with actual values
        foreach ($data as $key => $value) {
            $formula = str_replace('{' . $key . '}', $value, $formula);
        }
        
        // Safe evaluation
        try {
            // Remove any dangerous characters
            $formula = preg_replace('/[^0-9+\-*\/().,\s]/', '', $formula);
            
            // Use eval for simple formulas
            if (preg_match('/^[0-9+\-*\/().,\s]+$/', $formula)) {
                $result = eval("return $formula;");
                return is_numeric($result) ? $result : 0;
            }
        } catch (\Exception $e) {
            return 0;
        }
        
        return 0;
    }

    // Get list of available variables for formulas
    public static function getAvailableVariables()
    {
        return [
            'basic_salary' => 'Basic Salary',
            'dearness_allowance' => 'Dearness Allowance',
            'house_rent_allowance' => 'House Rent Allowance',
            'medical_allowance' => 'Medical Allowance',
            'present_days' => 'Present Days',
            'absent_days' => 'Absent Days',
            'late_minutes' => 'Late Minutes',
            'overtime_hours' => 'Overtime Hours',
            'working_days' => 'Working Days',
            'gross_salary' => 'Gross Salary',
            'net_salary' => 'Net Salary',
        ];
    }
}