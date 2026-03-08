<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentEmployee extends Model
{
    protected $table = 'department_employee';
    
    protected $fillable = [
        'department_id',
        'employee_id',
        'role',
        'start_date',
        'end_date',
        'is_primary'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_primary' => 'boolean'
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Scope for active assignments (without end date or end date in future)
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', now());
        });
    }

    // Scope for primary assignments
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    // Check if assignment is active
    public function getIsActiveAttribute()
    {
        if (!$this->start_date) return false;
        
        $startDate = \Carbon\Carbon::parse($this->start_date);
        $endDate = $this->end_date ? \Carbon\Carbon::parse($this->end_date) : null;
        
        $now = now();
        
        if ($startDate->isFuture()) return false;
        if ($endDate && $endDate->isPast()) return false;
        
        return true;
    }
}