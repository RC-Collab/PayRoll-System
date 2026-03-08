<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportApiController extends Controller
{
    /**
     * GET /api/report/monthly
     * Get monthly attendance and leave report
     */
    public function monthly(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee profile not found',
                ], 404);
            }

            $validated = $request->validate([
                'month' => 'required|numeric|between:1,12',
                'year' => 'required|numeric|digits:4',
            ]);

            $month = $validated['month'];
            $year = $validated['year'];
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            // unified simple attendance data
            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'asc')
                ->get();

            $attendanceSummary = [
                'total_days' => $attendances->count(),
                'present' => $attendances->where('status', 'present')->count(),
                'absent' => $attendances->where('status', 'absent')->count(),
                'overtime' => $attendances->where('status', 'overtime')->count(),
                'total_hours' => (float) $attendances->sum('total_hours'),
                'total_overtime_minutes' => $attendances->sum('overtime_minutes'),
                'total_overtime_hours' => (float) $attendances->sum('overtime'),
                'total_regular_hours' => (float) $attendances->sum('regular_hours'),
                'late_minutes' => $attendances->sum('late_minutes'),
            ];

            // Get leave data
            $leaves = LeaveRecord::where('employee_id', $employee->id)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $startDate)
                      ->where('end_date', '>=', $endDate);
                })
                ->get();

            $leaveSummary = [
                'total' => $leaves->count(),
                'approved' => $leaves->where('status', 'approved')->count(),
                'pending' => $leaves->where('status', 'pending')->count(),
                'rejected' => $leaves->where('status', 'rejected')->count(),
                'cancelled' => $leaves->where('status', 'cancelled')->count(),
                'total_leave_days' => $leaves->where('status', 'approved')->sum('total_days'),
                'by_type' => $leaves->where('status', 'approved')->groupBy('leave_type')->map(function ($group) {
                    return [
                        'type' => $group->first()->leave_type,
                        'count' => $group->count(),
                        'days' => $group->sum('total_days'),
                    ];
                })->values()->toArray(),
            ];

            // Detailed daily report (simplified)
            $dailyReport = [];
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');

                $dayAttendance = $attendances->where('date', $dateStr)->first();
                $entry = [
                    'date' => $dateStr,
                    'day' => $currentDate->format('l'),
                    'attendance' => $dayAttendance ? [
                        'status' => $dayAttendance->status,
                        'check_in' => $dayAttendance->check_in?->format('H:i:s'),
                        'check_out' => $dayAttendance->check_out?->format('H:i:s'),
                        'total_hours' => $dayAttendance->total_hours,
                        'late_minutes' => $dayAttendance->late_minutes,
                        'overtime_minutes' => $dayAttendance->overtime_minutes,
                    ] : null,
                ];

                $dayLeaves = $leaves->where('status', 'approved')
                    ->filter(function ($leave) use ($dateStr) {
                        return $dateStr >= $leave->start_date->format('Y-m-d') && 
                               $dateStr <= $leave->end_date->format('Y-m-d');
                    });

                if ($dayLeaves->isNotEmpty()) {
                    $entry['leaves'] = $dayLeaves->map(function ($leave) {
                        return [
                            'type' => $leave->leave_type,
                            'total_days' => $leave->total_days,
                        ];
                    })->toArray();
                }

                $dailyReport[] = $entry;
                $currentDate->addDay();
            }

            return response()->json([
                'message' => 'Monthly report retrieved successfully',
                'month' => $month,
                'year' => $year,
                'month_name' => $startDate->format('F Y'),
                'attendance_summary' => $attendanceSummary,
                'leave_summary' => $leaveSummary,
                'daily_report' => $dailyReport,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve monthly report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/report/yearly
     * Get yearly attendance and leave report
     */
    public function yearly(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee profile not found',
                ], 404);
            }

            $validated = $request->validate([
                'year' => 'required|numeric|digits:4',
            ]);

            $year = $validated['year'];
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();

            // Get all attendance for the year (simple unified table)
            $allAttendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            // Get all leaves for the year
            $allLeaves = LeaveRecord::where('employee_id', $employee->id)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $startDate)
                      ->where('end_date', '>=', $endDate);
                })
                ->get();

            // Generate monthly breakdown
            $monthlyBreakdown = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthStart = Carbon::createFromDate($year, $m, 1)->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();

                $monthAttendances = $allAttendances->whereBetween('date', [$monthStart, $monthEnd]);

                $monthLeaves = $allLeaves->where('status', 'approved')
                    ->filter(function ($leave) use ($monthStart, $monthEnd) {
                        return $leave->start_date <= $monthEnd && $leave->end_date >= $monthStart;
                    });

                $breakdown = [
                    'month' => $m,
                    'month_name' => $monthStart->format('F'),
                    'attendance' => [
                        'total_days' => $monthAttendances->count(),
                        'present' => $monthAttendances->where('status', 'present')->count(),
                        'absent' => $monthAttendances->where('status', 'absent')->count(),
                        'overtime' => $monthAttendances->where('status', 'overtime')->count(),
                        'total_hours' => (float) $monthAttendances->sum('total_hours'),
                    ],
                ];

                $breakdown['leaves'] = [
                    'total_days' => $monthLeaves->sum('total_days'),
                    'count' => $monthLeaves->count(),
                ];

                $monthlyBreakdown[] = $breakdown;
            }

            // Overall summary
            $summary = [
                'total_working_days' => $allAttendances->count(),
                'present' => $allAttendances->where('status', 'present')->count(),
                'absent' => $allAttendances->where('status', 'absent')->count(),
                'overtime' => $allAttendances->where('status', 'overtime')->count(),
                'total_hours' => (float) $allAttendances->sum('total_hours'),
                'late_minutes' => $allAttendances->sum('late_minutes'),
                'overtime_minutes' => $allAttendances->sum('overtime_minutes'),
            ];

            $summary = array_merge($summary, [
                'total_leave_days' => $allLeaves->where('status', 'approved')->sum('total_days'),
                'total_leaves' => $allLeaves->where('status', 'approved')->count(),
            ]);

            return response()->json([
                'message' => 'Yearly report retrieved successfully',
                'year' => $year,
                'summary' => $summary,
                'monthly_breakdown' => $monthlyBreakdown,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve yearly report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
