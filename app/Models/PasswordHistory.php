<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordHistory extends Model
{
    protected $fillable = [
        'user_id',
        'changed_by_user_id',
        'action',
        'reason',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user whose password was changed
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who made the change (admin/hr)
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    /**
     * Log a password change
     */
    public static function logPasswordChange($userId, $action = 'updated', $changedByUserId = null, $reason = null, $request = null)
    {
        return self::create([
            'user_id' => $userId,
            'changed_by_user_id' => $changedByUserId,
            'action' => $action,
            'reason' => $reason,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent()
        ]);
    }
}
