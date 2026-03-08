<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
        'activated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'activated_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ========== ROLE METHODS ==========
    
    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        if (!isset($this->role)) {
            return false;
        }
        
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        
        return $this->role === $role;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    /**
     * Check if user is HR
     */
    public function isHR()
    {
        return $this->role === 'hr';
    }
    
    /**
     * Check if user is accountant
     */
    public function isAccountant()
    {
        return $this->role === 'accountant';
    }
    
    /**
     * Check if user is employee
     */
    public function isEmployee()
    {
        return $this->role === 'employee';
    }
    
    /**
     * Check if user is manager
     */
    public function isManager()
    {
        return $this->role === 'manager';
    }
    
    /**
     * Get role display name
     */
    public function getRoleNameAttribute()
    {
        $roles = [
            'admin' => 'Administrator',
            'hr' => 'HR Manager',
            'accountant' => 'Accountant',
            'employee' => 'Employee',
            'manager' => 'Manager',
        ];
        
        return $roles[$this->role] ?? ucfirst($this->role);
    }
    
    /**
     * Get role badge HTML
     */
    public function getRoleBadgeAttribute()
    {
        $colors = [
            'admin' => 'danger',
            'hr' => 'warning',
            'accountant' => 'info',
            'employee' => 'success',
            'manager' => 'primary',
        ];
        
        $color = $colors[$this->role] ?? 'secondary';
        
        return '<span class="badge bg-' . $color . '">' . $this->role_name . '</span>';
    }

    // ========== RELATIONSHIPS ==========
    
    /**
     * Get the employee associated with this user
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get notifications for this user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}