<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivationCode extends Model
{
    protected $fillable = [
        'email',
        'phone',
        'code',
        'expires_at',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Check if the code is still valid
     */
    public function isValid()
    {
        return !$this->is_used && $this->expires_at->isFuture();
    }

    /**
     * Mark code as used
     */
    public function markAsUsed()
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
        ]);
    }
}
