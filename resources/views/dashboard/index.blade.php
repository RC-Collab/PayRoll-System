@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<!-- Welcome -->
<div class="mb-8">
    <h2 class="text-3xl font-bold text-slate-800">
        Welcome back 👋
    </h2>
    <p class="text-slate-500 mt-1">
        Payroll overview with live insights
    </p>
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-6">

    <!-- Employees -->
    <a href="{{ route('employees.index') }}" class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 text-white shadow-lg hover:scale-[1.02] transition block">
        <div class="absolute top-0 right-0 opacity-20 text-7xl font-bold">👥</div>
        <p class="text-sm opacity-90">Total Employees</p>
        <h3 class="text-4xl font-bold mt-3">{{ $totalEmployees }}</h3>
        <p class="mt-2 text-sm opacity-80">{{ $activeEmployees }} active</p>
    </a>

    <!-- Salary -->
    <a href="{{ route('salary.index', ['month' => $currentMonth]) }}" class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-500 to-sky-600 p-6 text-white shadow-lg hover:scale-[1.02] transition block">
        <div class="absolute top-0 right-0 opacity-20 text-7xl font-bold">💰</div>
        <p class="text-sm opacity-90">Monthly Salary</p>
        <h3 class="text-3xl font-bold mt-3">रु {{ number_format($monthlySalaries, 0) }}</h3>
        <p class="mt-2 text-sm opacity-80">{{ \Carbon\Carbon::now()->format('F') }} payout</p>
    </a>

    <!-- Departments -->
    <a href="{{ route('departments.index') }}" class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 p-6 text-white shadow-lg hover:scale-[1.02] transition block">
        <div class="absolute top-0 right-0 opacity-20 text-7xl font-bold">🏢</div>
        <p class="text-sm opacity-90">Departments</p>
        <h3 class="text-4xl font-bold mt-3">{{ $totalDepartments }}</h3>
        <p class="mt-2 text-sm opacity-80">Organization units</p>
    </a>

    <!-- Attendance Present -->
    <a href="{{ route('attendance.index') }}?month={{ $currentMonth }}" class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-400 to-blue-500 p-6 text-white shadow-lg hover:scale-[1.02] transition block">
        <div class="absolute top-0 right-0 opacity-20 text-7xl font-bold">✓</div>
        <p class="text-sm opacity-90">Attendance Present</p>
        <h3 class="text-4xl font-bold mt-3">{{ $presentCount }}</h3>
        <p class="mt-2 text-sm opacity-80">{{ $presentPercentage }}% present this month</p>
    </a>

    <!-- Leave Approval -->
    <a href="{{ route('leaves.index') }}?status=pending" class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-400 to-rose-500 p-6 text-white shadow-lg hover:scale-[1.02] transition block">
        <div class="absolute top-0 right-0 opacity-20 text-7xl font-bold">📩</div>
        <p class="text-sm opacity-90">Pending Leaves</p>
        <h3 class="text-4xl font-bold mt-3">{{ $pendingLeaves }}</h3>
        <p class="mt-2 text-sm opacity-80">Leaves awaiting approval</p>
    </a>

</div>

<!-- Analytics Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-10">

    <!-- Attendance Summary -->
    <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">
            Attendance Summary
        </h3>

        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Present</span>
                    <span>{{ $presentPercentage }}%</span>
                </div>
                <div class="w-full h-2 bg-slate-200 rounded mt-1">
                    <div class="h-2 bg-emerald-500 rounded" style="width: {{ $presentPercentage }}%"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Absent</span>
                    <span>{{ $absentPercentage }}%</span>
                </div>
                <div class="w-full h-2 bg-slate-200 rounded mt-1">
                    <div class="h-2 bg-rose-500 rounded" style="width: {{ $absentPercentage }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Summary -->
    <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">
            Leave Summary
        </h3>

        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Approved Leaves</span>
                    <span>{{ $approvedLeaves }}</span>
                </div>
                <div class="w-full h-2 bg-slate-200 rounded mt-1">
                    <div class="h-2 bg-emerald-500 rounded" style="width: {{ $approvedLeaves > 0 ? min(100, ($approvedLeaves/($approvedLeaves+$pendingLeaves))*100) : 0 }}%"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Pending Leaves</span>
                    <span>{{ $pendingLeaves }}</span>
                </div>
                <div class="w-full h-2 bg-slate-200 rounded mt-1">
                    <div class="h-2 bg-rose-500 rounded" style="width: {{ $pendingLeaves > 0 ? min(100, ($pendingLeaves/($approvedLeaves+$pendingLeaves))*100) : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Employees & Top Earners -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-10">
    
    <!-- Recent Employees -->
    <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">
            Recently Added Employees
        </h3>

        <div class="space-y-3">
            @forelse($recentEmployees as $employee)
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                <div>
                    <p class="font-medium text-slate-800">{{ $employee->first_name }} {{ $employee->last_name }}</p>
                    <p class="text-xs text-slate-500">{{ $employee->designation }}</p>
                </div>
                <span class="text-xs px-2 py-1 bg-emerald-100 text-emerald-800 rounded">{{ $employee->employee_code }}</span>
            </div>
            @empty
            <p class="text-slate-500 text-sm">No recent employees</p>
            @endforelse
        </div>
    </div>

    <!-- Top Earners -->
    <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">
            Top Earners
        </h3>

        <div class="space-y-3">
            @forelse($topEarners as $employee)
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                <div>
                    <p class="font-medium text-slate-800">{{ $employee->first_name }} {{ $employee->last_name }}</p>
                    <p class="text-xs text-slate-500">{{ $employee->designation }}</p>
                </div>
                <span class="font-semibold text-emerald-600">रु {{ number_format($employee->salaryStructure->basic_salary, 0) }}</span>
            </div>
            @empty
            <p class="text-slate-500 text-sm">No salary data available</p>
            @endforelse
        </div>
    </div>

</div>

@endsection