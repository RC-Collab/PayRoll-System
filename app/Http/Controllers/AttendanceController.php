<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // display a simple attendance monitor
    public function index(Request $request)
    {
        // Handle both date and month filters
        $date = $request->get('date');
        $month = $request->get('month');
        
        if ($date) {
            // Filter by exact day when date is chosen
            $selectedDate = Carbon::parse($date);
            $startDate = $selectedDate->copy()->startOfDay();
            $endDate = $selectedDate->copy()->endOfDay();
            $currentMonth = $selectedDate->copy();
        } else {
            // Filter by month or current month
            $monthStr = $month ?? now()->format('Y-m');
            $startDate = Carbon::parse($monthStr . '-01')->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $currentMonth = $startDate->copy();
        }

        $employeeId = $request->get('employee', 'all');
        $departmentId = $request->get('department', 'all');

        $query = Attendance::with('employee')
            ->whereBetween('date', [$startDate, $endDate]);

        if ($employeeId !== 'all') {
            $query->where('employee_id', $employeeId);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();
        
        // Calculate monthly stats
        $allAttendances = Attendance::whereBetween('date', [$startDate, $endDate])->get();
        
        // Get active holidays for this month
        $holidaysInMonth = \App\Models\Holiday::where('is_active', true)
            ->whereBetween('date', [$startDate, $endDate])
            ->count();
        
        // Calculate working days
        $workingDaysFromDb = \App\Models\WorkingDay::getWorkingDays(1);
        if (empty($workingDaysFromDb)) {
            // fallback to default Mon-Fri until settings are saved
            $workingDaysFromDb = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        }
        $totalDaysInMonth = $endDate->day;
        $workingDaysCount = 0;
        
        // Count working days excluding holidays
        for ($day = 1; $day <= $totalDaysInMonth; $day++) {
            $currentDate = $startDate->copy()->setDay($day);
            $dayName = $currentDate->format('l'); // e.g., Monday, Tuesday
            
            // Check if it's a working day and not a holiday
            if (in_array($dayName, $workingDaysFromDb)) {
                    $holidayDates = \App\Models\Holiday::where('is_active', true)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->pluck('date')
                        ->map(fn($d) => $d->format('Y-m-d'))
                        ->toArray();
                    if (!in_array($currentDate->format('Y-m-d'), $holidayDates)) {
                        $workingDaysCount++;
                    }
                }
        }
        
        $monthlyStats = [
            'day_wise' => [
                'present' => $allAttendances->where('status', 'present')->count(),
                'absent' => $allAttendances->where('status', 'absent')->count(),
                'late' => $allAttendances->where('is_late', true)->count(),
                'holidays' => $holidaysInMonth,
                'total' => $allAttendances->count(),
                'working_days' => $workingDaysCount,
            ],
        ];

        $month = $startDate->format('Y-m');
        $allEmployees = Employee::select('id', 'first_name', 'middle_name', 'last_name', 'employee_code')
            ->orderBy('first_name')->get();
        $departments = \App\Models\Department::all();
        // determine if today is a holiday (active)
        $todayHoliday = \App\Models\Holiday::where('organization_id', 1)
            ->where('date', now()->format('Y-m-d'))
            ->where('is_active', true)
            ->first();

        return view('attendance.index', compact('attendances', 'allEmployees', 'departments', 'month', 'employeeId', 'departmentId', 'monthlyStats', 'currentMonth', 'todayHoliday'));
    }

    // show history for a specific employee
    public function employeeHistory(Employee $employee)
    {
        // show paginated history instead of grabbing all records at once
        $attendances = Attendance::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('attendance.history', compact('employee', 'attendances'));
    }

    // summary report for employee (reuse existing view if appropriate)
    public function employeeReport(Employee $employee, Request $request)
    {
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        // support "all" or invalid month values by falling back to full-year range
        if ($month === 'all' || !is_numeric($month) || (int)$month < 1 || (int)$month > 12) {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate   = Carbon::createFromDate($year, 12, 31)->endOfDay();
        } else {
            $monthInt  = (int) $month;
            $startDate = Carbon::createFromDate($year, $monthInt, 1)->startOfMonth();
            $endDate   = $startDate->copy()->endOfMonth();
        }

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // simple summary
        $summary = [
            'total_days' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'overtime' => $attendances->where('status', 'overtime')->count(),
            'total_hours' => (float) $attendances->sum('total_hours'),
        ];

        // load settings (may be null) and derive type
        $settings = $employee->attendanceSettings ?: new \App\Models\AttendanceSetting();
        $attendanceType = 'day_wise';

        return view('attendance.reports.employee', compact(
            'employee',
            'attendances',
            'summary',
            'month',
            'year',
            'settings',
            'attendanceType'
        ));
    }

    /**
     * Delete an attendance record (admin/hr only).
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->back()->with('success', 'Attendance record deleted');
    }
}
