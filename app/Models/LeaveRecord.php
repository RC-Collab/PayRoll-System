<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'contact_during_leave',
        'medical_certificate',
        'status',
        'approved_by',
        'approved_at',
        'approval_remarks',
        'rejected_by',
        'rejected_at',
        'remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'total_days' => 'float',
        'medical_certificate' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Helper methods
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
        ];
        
        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getLeaveTypeNameAttribute()
    {
        $types = [
            'sick_leave' => 'Sick Leave',
            'casual_leave' => 'Casual Leave',
            'annual_leave' => 'Annual Leave',
            'maternity_leave' => 'Maternity Leave',
            'paternity_leave' => 'Paternity Leave',
            'study_leave' => 'Study Leave',
            'bereavement_leave' => 'Bereavement Leave',
            'public_holiday' => 'Public Holiday',
            'unpaid_leave' => 'Unpaid Leave',
        ];
        
        return $types[$this->leave_type] ?? $this->leave_type;
    }
}