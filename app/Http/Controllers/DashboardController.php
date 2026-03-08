<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\LeaveRecord;
use App\Models\Attendance;
use App\Models\MonthlySalary;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Total employees
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('employment_status', 'active')->count();
        
        // Departments
        $totalDepartments = Department::count();
        
        // Current month salary data (only paid amounts)
        $currentMonth = Carbon::now()->format('Y-m');
        $monthlySalaries = MonthlySalary::where('salary_month', $currentMonth)
            ->where('payment_status', 'paid')
            ->sum('net_salary');
        
        // Leave approvals pending
        $pendingLeaves = LeaveRecord::where('status', 'pending')->count();
        
        // Attendance summary for current month
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $totalAttendanceRecords = Attendance::whereBetween('date', [$startDate, $endDate])->count();
        $presentCount = Attendance::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'present')
            ->count();
        $absentCount = Attendance::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'absent')
            ->count();
        
        $denominator = $presentCount + $absentCount;
        $presentPercentage = $denominator > 0 ? round(($presentCount / $denominator) * 100) : 0;
        $absentPercentage = $denominator > 0 ? round(($absentCount / $denominator) * 100) : 0;
        
        // Leave summary (approved vs pending)
        $approvedLeaves = LeaveRecord::where('status', 'approved')
            ->whereMonth('start_date', Carbon::now()->month)
            ->whereYear('start_date', Carbon::now()->year)
            ->count();
        $usedLeaves = $approvedLeaves; // here used and approved are same for now
        
        // Recent employees
        $recentEmployees = Employee::with('salaryStructure')
            ->latest('created_at')
            ->limit(5)
            ->get();
        
        // Top earners
        $topEarners = Employee::with('salaryStructure')
            ->whereHas('salaryStructure')
            ->join('salary_structures', 'employees.id', '=', 'salary_structures.employee_id')
            ->orderByDesc('salary_structures.basic_salary')
            ->select('employees.*')
            ->limit(5)
            ->get();
        
        return view('dashboard.index', compact(
            'totalEmployees',
            'activeEmployees',
            'totalDepartments',
            'monthlySalaries',
            'pendingLeaves',
            'presentPercentage',
            'absentPercentage',
            'approvedLeaves',
            'usedLeaves',
            'recentEmployees',
            'topEarners',
            'presentCount',
            'absentCount',
            'currentMonth'
        ));
    }
}
