<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingDay extends Model
{
    protected $fillable = [
        'day',
        'organization_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to filter working days by organization.
     */
    public function scopeByOrganization($query, $organizationId = 1)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Get all working days for an organization.
     */
    public static function getWorkingDays($organizationId = 1)
    {
        return self::where('organization_id', $organizationId)
            ->pluck('day')
            ->toArray();
    }

    /**
     * Check if a given day is a working day.
     */
    public static function isWorkingDay($day, $organizationId = 1)
    {
        return self::where('day', $day)
            ->where('organization_id', $organizationId)
            ->exists();
    }

    /**
     * Get all days of the week with their working status.
     */
    public static function getAllDaysStatus($organizationId = 1)
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $workingDays = self::getWorkingDays($organizationId);

        return collect($days)->mapWithKeys(function ($day) use ($workingDays) {
            return [$day => in_array($day, $workingDays)];
        })->toArray();
    }
}
