<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'head_of_department',
        'icon',
        'roles',
        'is_active'
    ];

    protected $casts = [
        'roles' => 'array',
        'is_active' => 'boolean'
    ];

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'department_employee')
                    ->withPivot('role', 'start_date', 'end_date', 'is_primary')
                    ->withTimestamps();
    }

    // Helper method to get employee count
    public function getEmployeeCountAttribute()
    {
        return $this->employees()->count();
    }

    // Helper method to get active employees
    public function activeEmployees()
    {
        return $this->employees()->where('status', 'active');
    }
}