<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    // All fields that should be filled
    protected $fillable = [
        // User Relationship
        'user_id',
        
        // Personal Information
        'employee_code', 'first_name', 'middle_name', 'last_name',
        'email', 'mobile_number', 'alternate_phone',
        'gender', 'date_of_birth', 'marital_status', 'blood_group', 'religion',
        
        // Citizenship & Identification
        'citizenship_number', 'citizenship_issue_date', 'citizenship_issued_district',
        'pan_number',
        
        // Address
        'current_address', 'permanent_address', 'district', 'municipality', 'ward_number',
        'present_address', 'city', 'state', 'country', 'postal_code',
        
        // Emergency Contact
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
        
        // Employment Details
        'joining_date', 'employee_type', 'employment_status', 'contract_end_date',
        'designation', 'qualification', 'institution_name', 'experience_years',
        'reports_to', 'probation_end_date', 'work_shift', 'confirmation_date',
        
        // Bank Details
        'bank_name', 'account_number', 'account_holder_name', 'branch_name',
        'ifsc_code', 'uan_number', 'esi_number',
        
        // Other
        'profile_image', 'is_active', 'notes'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'citizenship_issue_date' => 'date',
        'joining_date' => 'date',
        'contract_end_date' => 'date',
        'probation_end_date' => 'date',
        'confirmation_date' => 'date',
        'is_active' => 'boolean',
        'marital_status' => 'boolean', // false = unmarried, true = married
        'experience_years' => 'integer',
        'reports_to' => 'integer'
    ];

    // ========== SCOPES ==========
    
    /**
     * Scope for active employees
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->orWhere('employment_status', 'active');
    }
    
    /**
     * Scope for inactive employees
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
    
    /**
     * Scope for probation employees
     */
    public function scopeProbation($query)
    {
        return $query->where('employment_status', 'probation');
    }
    
    /**
     * Scope for permanent employees
     */
    public function scopePermanent($query)
    {
        return $query->where('employee_type', 'permanent');
    }
    
    /**
     * Scope for contract employees
     */
    public function scopeContract($query)
    {
        return $query->where('employee_type', 'contract');
    }
    
    /**
     * Scope for employees on leave
     */
    public function scopeOnLeave($query)
    {
        return $query->where('employment_status', 'leave');
    }
    
    /**
     * Scope for terminated employees
     */
    public function scopeTerminated($query)
    {
        return $query->where('employment_status', 'terminated');
    }
    
    /**
     * Scope for employees with specific designation
     */
    public function scopeByDesignation($query, $designation)
    {
        return $query->where('designation', $designation);
    }
    
    /**
     * Scope for employees in specific department
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->whereHas('departments', function ($q) use ($departmentId) {
            $q->where('departments.id', $departmentId);
        });
    }

    /**
     * Scope for married employees
     */
    public function scopeMarried($query)
    {
        return $query->where('marital_status', true);
    }

    /**
     * Scope for unmarried employees
     */
    public function scopeUnmarried($query)
    {
        return $query->where('marital_status', false);
    }
    
    /**
     * Scope for employees with salary structure
     */
    public function scopeWithSalaryStructure($query)
    {
        return $query->whereHas('salaryStructure');
    }
    
    /**
     * Scope for employees without salary structure
     */
    public function scopeWithoutSalaryStructure($query)
    {
        return $query->whereDoesntHave('salaryStructure');
    }

    // ========== RELATIONSHIPS ==========

    /**
     * Get the user associated with the employee
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the departments the employee belongs to
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_employee')
                    ->select(['departments.id', 'departments.name', 'departments.description'])
                    ->withPivot('role', 'joined_date')
                    ->withTimestamps();
    }

    /**
     * Get the attendance settings for the employee
     */
    public function attendanceSettings()
    {
        return $this->hasOne(AttendanceSetting::class);
    }

    /**
     * Get all attendances for the employee
     */
    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class);
    }

    /**
     * Get today's attendance for the employee
     */
    public function todayAttendance()
    {
        return $this->hasOne(\App\Models\Attendance::class)->whereDate('date', today());
    }

    /**
     * Get the salary structure for the employee
     */
    public function salaryStructure()
    {
        return $this->hasOne(SalaryStructure::class);
    }

    /**
     * Get all monthly salaries for the employee
     */
    public function monthlySalaries()
    {
        return $this->hasMany(MonthlySalary::class)->orderBy('salary_month', 'desc');
    }

    /**
     * Get all leave records for the employee
     */
    public function leaveRecords()
    {
        return $this->hasMany(LeaveRecord::class);
    }

    /**
     * Get all qualifications for the employee
     */
    public function qualifications()
    {
        return $this->hasMany(Qualification::class);
    }

    /**
     * Get all experiences for the employee
     */
    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }

    /**
     * Get all emergency contacts for the employee
     */
    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class);
    }

    /**
     * Get all documents for the employee
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the manager this employee reports to
     */
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'reports_to');
    }

    /**
     * Get all subordinates this employee manages
     */
    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'reports_to');
    }

    /**
     * Get all allowances for the employee (with pivot data)
     */
    public function allowances()
    {
        return $this->belongsToMany(Allowance::class, 'employee_allowance')
            ->withPivot('custom_value', 'is_active', 'effective_from', 'effective_to')
            ->withTimestamps();
    }

    /**
     * Get all deductions for the employee (with pivot data)
     */
    public function deductions()
    {
        return $this->belongsToMany(Deduction::class, 'employee_deduction')
            ->withPivot('custom_value', 'is_active', 'effective_from', 'effective_to')
            ->withTimestamps();
    }

    // ========== ACCESSORS ==========
    
    /**
     * Get the employee's full name
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name);
    }
    
    /**
     * Get employee status with color coding
     */
    public function getStatusBadgeAttribute()
    {
        $status = $this->employment_status;
        $colors = [
            'active' => 'success',
            'probation' => 'warning',
            'leave' => 'info',
            'terminated' => 'danger',
            'inactive' => 'secondary',
        ];
        
        $color = $colors[$status] ?? 'secondary';
        
        return '<span class="badge bg-' . $color . '">' . ucfirst($status) . '</span>';
    }
    
    /**
     * Get employee type badge
     */
    public function getTypeBadgeAttribute()
    {
        $type = $this->employee_type;
        $colors = [
            'permanent' => 'primary',
            'contract' => 'info',
            'temporary' => 'warning',
            'intern' => 'success',
        ];
        
        $color = $colors[$type] ?? 'secondary';
        
        return '<span class="badge bg-' . $color . '">' . ucfirst($type) . '</span>';
    }
    
    /**
     * Get marital status as text
     */
    public function getMaritalStatusTextAttribute()
    {
        return $this->marital_status ? 'Married' : 'Unmarried';
    }
    
    /**
     * Get marital status badge
     */
    public function getMaritalStatusBadgeAttribute()
    {
        $color = $this->marital_status ? 'primary' : 'secondary';
        $text = $this->marital_status ? 'Married' : 'Unmarried';
        
        return '<span class="badge bg-' . $color . '">' . $text . '</span>';
    }
    
    /**
     * Get current salary (from salary structure)
     */
    public function getCurrentSalaryAttribute()
    {
        if ($this->salaryStructure) {
            return $this->salaryStructure->basic_salary + $this->salaryStructure->total_allowances;
        }
        return 0;
    }
    
    /**
     * Get current department name
     */
    public function getDepartmentNameAttribute()
    {
        if ($this->departments->count() > 0) {
            return $this->departments->first()->name;
        }
        return 'N/A';
    }

    /**
     * Get formatted current salary
     */
    public function getFormattedCurrentSalaryAttribute()
    {
        return 'रु ' . number_format($this->current_salary, 2);
    }

    /**
     * Get last salary record
     */
    public function getLastSalaryAttribute()
    {
        return $this->monthlySalaries()->latest('salary_month')->first();
    }

    /**
     * Get formatted last salary
     */
    public function getFormattedLastSalaryAttribute()
    {
        $lastSalary = $this->last_salary;
        if ($lastSalary) {
            return 'रु ' . number_format($lastSalary->net_salary, 2) . ' (' . $lastSalary->salary_month . ')';
        }
        return 'Not calculated';
    }

    /**
     * Check if employee is married
     */
    public function getIsMarriedAttribute()
    {
        return $this->marital_status;
    }

    // ========== METHODS ==========

    /**
     * Get active allowances for the employee
     */
    public function getActiveAllowances()
    {
        return $this->allowances()
            ->wherePivot('is_active', true)
            ->where(function($q) {
                $q->whereNull('pivot_effective_to')
                  ->orWhere('pivot_effective_to', '>=', now());
            })
            ->get();
    }

    /**
     * Get active deductions for the employee
     */
    public function getActiveDeductions()
    {
        return $this->deductions()
            ->wherePivot('is_active', true)
            ->where(function($q) {
                $q->whereNull('pivot_effective_to')
                  ->orWhere('pivot_effective_to', '>=', now());
            })
            ->get();
    }

    /**
     * Get attendance summary for a specific month
     */
    public function getAttendanceSummary($year, $month)
    {
        $startDate = \Carbon\Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $attendances = $this->attendances()
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        return [
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'leave' => $attendances->where('status', 'leave')->count(),
            'late' => $attendances->where('is_late', true)->count(),
            'total_days' => $startDate->diffInDays($endDate) + 1,
        ];
    }
    
    /**
     * Check if employee has salary for specific month
     */
    public function hasSalaryForMonth($yearMonth)
    {
        return $this->monthlySalaries()
            ->where('salary_month', $yearMonth)
            ->exists();
    }
    
    /**
     * Get salary for specific month
     */
    public function getSalaryForMonth($yearMonth)
    {
        return $this->monthlySalaries()
            ->where('salary_month', $yearMonth)
            ->first();
    }
}