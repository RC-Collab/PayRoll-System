<?php
// app/Models/Holiday.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'type',
        'description',
        'is_recurring',
        'year',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Check if a date is a holiday
    public static function isHoliday($date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        
        return self::where('date', $date)
            ->where('is_active', true)
            ->exists();
    }

    // Get holiday for a specific date
    public static function getHolidayByDate($date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        
        return self::where('date', $date)
            ->where('is_active', true)
            ->first();
    }

    // Get all holidays for a month/year
    public static function getHolidaysForMonth($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        return self::whereBetween('date', [$startDate, $endDate])
            ->where('is_active', true)
            ->orderBy('date')
            ->get();
    }

    // Get holidays for a date range (for attendance calculations)
    public static function getHolidaysBetween($startDate, $endDate)
    {
        return self::whereBetween('date', [$startDate, $endDate])
            ->where('is_active', true)
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();
    }

    // Check if date is working day (not holiday and not weekend based on settings)
    public static function isWorkingDay($date, $workingDays = [1,2,3,4,5])
    {
        $carbonDate = Carbon::parse($date);
        
        // Check if weekend based on working days settings
        if (!in_array($carbonDate->dayOfWeek, $workingDays)) {
            return false;
        }
        
        // Check if holiday
        return !self::isHoliday($date);
    }
}