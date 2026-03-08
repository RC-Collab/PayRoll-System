<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MarkAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    /**
     * Check in for an employee (or update existing record)
     * POST /api/attendance/check-in
     */
    public function checkIn(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'timestamp' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        // default to authenticated user's employee if not provided
        if (empty($data['employee_id']) && $request->user() && $request->user()->employee) {
            $data['employee_id'] = $request->user()->employee->id;
        }

        if (empty($data['employee_id'])) {
            return response()->json(['message' => 'employee_id is required'], 422);
        }

        $time = isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : now();

        // disallow future timestamps
        if ($time->isFuture()) {
            return response()->json([
                'message' => 'Cannot check in for a future date'
            ], 422);
        }

        // Restrict API clients: only the Android app should mark attendance.
        if ($request->header('X-APP-CLIENT') !== 'android') {
            return response()->json([
                'message' => 'Only Android app may perform this action'
            ], 403);
        }

        // prevent attendance on non-working days (weekends/holidays)
        if (!self::isWorkingDay($time)) {
            return response()->json([
                'message' => 'Cannot check in on a non-working day'
            ], 422);
        }

        $attendance = Attendance::updateOrCreate(
            [
                'employee_id' => $data['employee_id'],
                'date' => $time->toDateString(),
            ],
            [
                'check_in' => $time,
                'notes' => $data['notes'] ?? null,
            ]
        );

        // recalc related fields using employee-specific schedule
        $attendance->recalcWithSettings();
        $attendance->save();

        return response()->json([
            'message' => 'Checked in successfully',
            'data' => new AttendanceResource($attendance),
        ], 201);
    }

    /**
     * Check out for an attendance record
     * POST /api/attendance/check-out
     */
    public function checkOut(Request $request)
    {
        $data = $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'timestamp' => 'nullable|date',
        ]);

        $attendance = Attendance::findOrFail($data['attendance_id']);
        $time = isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : now();

        // Restrict API clients: only the Android app should mark attendance.
        if ($request->header('X-APP-CLIENT') !== 'android') {
            return response()->json([
                'message' => 'Only Android app may perform this action'
            ], 403);
        }

        // if the original attendance date is not a working day, prevent checkout
        if (!self::isWorkingDay(Carbon::parse($attendance->date))) {
            return response()->json([
                'message' => 'Cannot check out on a non-working day'
            ], 422);
        }

        $attendance->check_out = $time;

        // recalc fields (handles lateness, total hours, overtime, status)
        if ($attendance->check_in) {
            $attendance->recalcWithSettings();
        }

        $attendance->save();

        return response()->json([
            'message' => 'Checked out successfully',
            'data' => new AttendanceResource($attendance),
        ], 200);
    }

    /**
     * Admin / HR can manually mark attendance
     * POST /api/attendance/mark
     */
    public function mark(MarkAttendanceRequest $request)
    {
        $validated = $request->validated();
        $attendanceDate = Carbon::parse($validated['date']);
        if ($attendanceDate->isFuture()) {
            return response()->json([
                'message' => 'Cannot mark attendance for a future date'
            ], 422);
        }

        $attendance = Attendance::updateOrCreate(
            [
                'employee_id' => $validated['employee_id'],
                'date' => $attendanceDate->toDateString(),
            ],
            [
                'check_in' => isset($validated['check_in']) ? Carbon::parse($validated['check_in']) : null,
                'check_out' => isset($validated['check_out']) ? Carbon::parse($validated['check_out']) : null,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // recalc using employee schedule (handles all derived fields)
        if ($attendance->check_in && $attendance->check_out) {
            $attendance->recalcWithSettings();
            $attendance->save();
        }

        return response()->json([
            'message' => 'Attendance marked',
            'data' => new AttendanceResource($attendance),
        ], 201);
    }

    /**
     * GET /api/attendance/today
     */
    public function today(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;
        if (!$employee) {
            return response()->json(['message' => 'Employee profile not found'], 404);
        }

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'No attendance record found for today'], 404);
        }

        return response()->json([
            'message' => 'Today\'s attendance',
            'data' => new AttendanceResource($attendance),
        ], 200);
    }

    /**
     * Quick mark (admin/HR) with simple type/check_in/out/notes
     * POST /api/attendance/quick-mark
     */
    public function quickMark(Request $request)
    {
        // admin/HR may override working-day constraint; no check here
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'type' => 'required|in:present,absent,leave,half_day,overtime',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        $attendanceDate = Carbon::parse($data['date']);
        if ($attendanceDate->isFuture()) {
            return response()->json([
                'message' => 'Cannot quick-mark future dates'
            ], 422);
        }

        // normalize to date string for comparisons
        $dateString = $attendanceDate->toDateString();

        // try to find existing record manually, since SQLite date comparison can be tricky
        $attendance = Attendance::where('employee_id', $data['employee_id'])
            ->whereDate('date', $dateString)
            ->first();

        if ($attendance) {
            // update existing fields
            $attendance->status = $data['type'];
            $attendance->check_in = isset($data['check_in'])
                ? Carbon::parse($data['date'].' '.$data['check_in'])
                : null;
            $attendance->check_out = isset($data['check_out'])
                ? Carbon::parse($data['date'].' '.$data['check_out'])
                : null;
            $attendance->notes = $data['notes'] ?? null;
        } else {
            $attendance = new Attendance([
                'employee_id' => $data['employee_id'],
                'date' => $dateString,
                'status' => $data['type'],
                'check_in' => isset($data['check_in'])
                    ? Carbon::parse($data['date'].' '.$data['check_in'])
                    : null,
                'check_out' => isset($data['check_out'])
                    ? Carbon::parse($data['date'].' '.$data['check_out'])
                    : null,
                'notes' => $data['notes'] ?? null,
            ]);
        }

        if ($attendance->check_in && $attendance->check_out) {
            $attendance->recalcWithSettings();
        }

        // persist changes regardless of recalculation
        $attendance->save();

        return response()->json([
            'message' => 'Attendance quick-marked',
            'data' => new AttendanceResource($attendance),
        ], 201);
    }

    /**
     * Authenticated employee marks themselves present (check-in time)
     * POST /api/attendance/present
     */
    public function present(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->employee) {
            return response()->json(['message' => 'Employee profile not found'], 404);
        }
        $empId = $user->employee->id;
        $time = now();

        // Restrict API clients: only the Android app should mark attendance.
        if ($request->header('X-APP-CLIENT') !== 'android') {
            return response()->json([
                'message' => 'Only Android app may perform this action'
            ], 403);
        }

        $today = Carbon::today();
        if (!self::isWorkingDay($today)) {
            return response()->json([
                'message' => 'Cannot check in on a non-working day'
            ], 422);
        }

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $empId, 'date'=>$today->toDateString()],
            ['check_in'=>$time, 'status'=>'present']
        );

        return response()->json([
            'message'=>'Checked in',
            'data'=>new AttendanceResource($attendance),
        ],200);
    }

    /**
     * Authenticated employee marks absence for a date
     * POST /api/attendance/absent
     */
    public function absent(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->employee) {
            return response()->json(['message' => 'Employee profile not found'], 404);
        }
        $empId = $user->employee->id;
        $date = Carbon::parse($request->input('date', Carbon::today()->toDateString()));

        // Restrict API clients: only the Android app should mark attendance.
        if ($request->header('X-APP-CLIENT') !== 'android') {
            return response()->json([
                'message' => 'Only Android app may perform this action'
            ], 403);
        }

        // disallow marking future absences
        if ($date->isFuture()) {
            return response()->json([
                'message' => 'Cannot mark absence for a future date'
            ], 422);
        }

        if (!self::isWorkingDay($date)) {
            return response()->json([
                'message' => 'Cannot mark absence on a non-working day'
            ], 422);
        }

        $attendance = Attendance::updateOrCreate(
            ['employee_id'=>$empId, 'date'=>$date->toDateString()],
            ['status'=>'absent']
        );

        return response()->json([
            'message'=>'Marked absent',
            'data'=>new AttendanceResource($attendance),
        ],200);
    }

    /**
     * GET /api/attendance/history
     * Optional filters: month, year, employee_id
     */
    public function history(Request $request)
    {
        $query = Attendance::query();

        if ($request->user() && $request->user()->employee) {
            $query->where('employee_id', $request->user()->employee->id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('month')) {
            $year = $request->get('year', now()->year);
            $start = Carbon::createFromDate($year, $request->month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $query->whereBetween('date', [$start, $end]);
        } elseif ($request->filled('year')) {
            $start = Carbon::createFromDate($request->year, 1, 1)->startOfYear();
            $end = $start->copy()->endOfYear();
            $query->whereBetween('date', [$start, $end]);
        }

        $attendances = $query->orderBy('date')->get();

        return response()->json([
            'message' => 'Attendance history retrieved',
            'data' => AttendanceResource::collection($attendances),
        ], 200);
    }

    /**
     * GET /api/attendance/summary
     */
    public function summary(Request $request)
    {
        $query = Attendance::query();

        if ($request->user() && $request->user()->employee) {
            $query->where('employee_id', $request->user()->employee->id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $start = null;
        $end = null;
        if ($request->filled('month')) {
            $year = $request->get('year', now()->year);
            $start = Carbon::createFromDate($year, $request->month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $query->whereBetween('date', [$start, $end]);
        } elseif ($request->filled('year')) {
            $start = Carbon::createFromDate($request->year, 1, 1)->startOfYear();
            $end = $start->copy()->endOfYear();
            $query->whereBetween('date', [$start, $end]);
        }

        $attendances = $query->get();

        $summary = [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'overtime' => $attendances->where('status', 'overtime')->count(),
            'total_hours' => $attendances->sum('total_hours'),
            'late_minutes' => $attendances->sum('late_minutes'),
            'overtime_minutes' => $attendances->sum('overtime_minutes'),
        ];

        // include working-days and holidays if a month range is specified
        if ($start && $end) {
            $holidayDates = \App\Models\Holiday::where('is_active', true)
                ->whereBetween('date', [$start, $end])
                ->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                ->toArray();

            $workingDaysFromDb = \App\Models\WorkingDay::getWorkingDays(1);
            if (empty($workingDaysFromDb)) {
                $workingDaysFromDb = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
            }

            $totalDays = $end->day;
            $workingCount = 0;
            for ($d = 1; $d <= $totalDays; $d++) {
                $dt = $start->copy()->setDay($d);
                $dayName = $dt->format('l');
                if (in_array($dayName, $workingDaysFromDb) && !in_array($dt->format('Y-m-d'), $holidayDates)) {
                    $workingCount++;
                }
            }

            $summary['holidays'] = count($holidayDates);
            $summary['working_days'] = $workingCount;
        }

        return response()->json([
            'message' => 'Attendance summary',
            'summary' => $summary,
        ], 200);
    }

    /**
     * Determine whether a given date falls on a working day and is not a holiday.
     * Defaults to Monday–Friday when no working days are configured.
     */
    public static function isWorkingDay(Carbon $date): bool
    {
        $dayName = $date->format('l');
        $workingDays = \App\Models\WorkingDay::getWorkingDays(1);
        if (empty($workingDays)) {
            $workingDays = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
        }
        if (!in_array($dayName, $workingDays)) {
            return false;
        }

        // treat active holidays as non-working as well
        $isHoliday = \App\Models\Holiday::where('is_active', true)
            ->where('date', $date->format('Y-m-d'))
            ->exists();

        return !$isHoliday;
    }
}
