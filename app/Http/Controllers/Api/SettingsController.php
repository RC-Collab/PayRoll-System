<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\WorkingDay;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get all holidays
     */
    public function getHolidays(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $holidays = Holiday::where('organization_id', 1)
            ->where('is_active', true)
            ->whereBetween('date', [$startDate, $endDate])
            ->get(['id', 'date', 'name', 'type']);

        return response()->json([
            'success' => true,
            'data' => $holidays,
        ]);
    }

    /**
     * Add a holiday
     */
    public function addHoliday(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays',
            'name' => 'required|string|max:255',
            // types must match the main holiday management list so both UIs stay consistent
            'type' => 'required|in:national,regional,company,optional',
        ]);

        $holiday = Holiday::create([
            'date' => $validated['date'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'organization_id' => 1,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Holiday added successfully',
            'data' => $holiday,
        ], 201);
    }

    /**
     * Delete a holiday
     */
    public function deleteHoliday($id)
    {
        $holiday = Holiday::find($id);

        if (!$holiday) {
            return response()->json([
                'success' => false,
                'message' => 'Holiday not found',
            ], 404);
        }

        $holiday->delete();

        return response()->json([
            'success' => true,
            'message' => 'Holiday deleted successfully',
        ]);
    }

    /**
     * Get working days configuration
     */
    public function getWorkingDays()
    {
        $organizationId = 1;
        $default = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $workingDays = WorkingDay::getWorkingDays($organizationId);

        // if no configuration exists yet, seed with default
        if (empty($workingDays)) {
            foreach ($default as $day) {
                WorkingDay::firstOrCreate([
                    'day' => $day,
                    'organization_id' => $organizationId,
                ]);
            }
            $workingDays = $default;
        } else {
            // ensure default workdays are present (handles stray or partial data)
            foreach ($default as $day) {
                if (!in_array($day, $workingDays)) {
                    WorkingDay::firstOrCreate([
                        'day' => $day,
                        'organization_id' => $organizationId,
                    ]);
                    $workingDays[] = $day;
                }
            }
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $daysStatus = collect($days)->mapWithKeys(function ($day) use ($workingDays) {
            return [$day => in_array($day, $workingDays)];
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => $daysStatus,
        ]);
    }

    /**
     * Save working days configuration
     */
    public function saveWorkingDays(Request $request)
    {
        $validated = $request->validate([
            'working_days' => 'required|array|min:1',
            'working_days.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);

        $organizationId = 1;

        // Delete existing working days
        WorkingDay::where('organization_id', $organizationId)->delete();

        // Insert new working days
        foreach ($validated['working_days'] as $day) {
            WorkingDay::create([
                'day' => $day,
                'organization_id' => $organizationId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Working days saved successfully',
            'data' => $validated['working_days'],
        ]);
    }
}
