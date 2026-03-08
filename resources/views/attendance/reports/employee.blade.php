@extends('layouts.app')

@section('title', 'Employee Attendance Report - ' . $employee->full_name)

@section('content')

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-start">
            <div>
                @if(\Route::has('attendance.index'))
                <a href="{{ route('attendance.index') }}" class="text-indigo-600 hover:text-indigo-800 mb-4 inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Attendance
                </a>
                @endif
                <h1 class="text-3xl font-bold text-slate-800">📊 Employee Attendance Report</h1>
                <p class="text-slate-500">{{ $employee->full_name }} - {{ $month }} {{ $year }}</p>
            </div>
            <button onclick="window.print()" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700">
                🖨️ Print Report
            </button>
        </div>
    </div>

    <!-- Employee Info Card -->
    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-center gap-4">
                @if($employee->profile_image)
                    <img src="{{ asset('storage/' . $employee->profile_image) }}" 
                         alt="{{ $employee->full_name }}"
                         class="w-16 h-16 rounded-full object-cover">
                @else
                    <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-xl font-semibold text-indigo-800">
                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                        </span>
                    </div>
                @endif
                <div>
                    <h2 class="text-xl font-bold text-slate-800">{{ $employee->full_name }}</h2>
                    <p class="text-slate-600">{{ $employee->designation }}</p>
                    <p class="text-sm text-slate-500">{{ $employee->employee_code }}</p>
                </div>
            </div>
            
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-600">Department:</span>
                    <span class="font-medium text-slate-800">
                        {{ $employee->departments->first()->name ?? '—' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Attendance Type:</span>
                    <span class="font-medium text-slate-800 capitalize">
                        {{ str_replace('_', ' ', $attendanceType) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Working Hours:</span>
                    <span class="font-medium text-slate-800">
                        @if($settings->start_time && $settings->end_time)
                            {{ \Carbon\Carbon::parse($settings->start_time)->format('h:i A') }} - 
                            {{ \Carbon\Carbon::parse($settings->end_time)->format('h:i A') }}
                        @else
                            Not Set
                        @endif
                    </span>
                </div>
            </div>
            
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-600">Late Threshold:</span>
                    <span class="font-medium text-slate-800">{{ $settings->late_threshold_minutes ?? 15 }} minutes</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Half Day:</span>
                    <span class="font-medium text-slate-800">{{ $settings->half_day_threshold_hours ?? 4 }} hours</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Working Days:</span>
                    <span class="font-medium text-slate-800">
                        @if($settings->working_days)
                            {{ count($settings->working_days) }} days/week
                        @else
                            5 days/week
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        @if($attendanceType === 'day_wise')
            <div class="bg-white rounded-xl shadow p-6 text-center border-l-4 border-green-500">
                <div class="text-3xl font-bold text-slate-800 mb-2">{{ $stats['present'] ?? 0 }}</div>
                <p class="text-slate-600">Present Days</p>
                <p class="text-sm text-slate-500 mt-1">{{ $stats['total_days'] ?? 0 }} total days</p>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6 text-center border-l-4 border-red-500">
                <div class="text-3xl font-bold text-slate-800 mb-2">{{ $stats['absent'] ?? 0 }}</div>
                <p class="text-slate-600">Absent Days</p>
                <p class="text-sm text-slate-500 mt-1">{{ $stats['total_days'] ?? 0 }} total days</p>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6 text-center border-l-4 border-blue-500">
                <div class="text-3xl font-bold text-slate-800 mb-2">{{ $stats['leave'] ?? 0 }}</div>
                <p class="text-slate-600">Leave Days</p>
                <p class="text-sm text-slate-500 mt-1">Authorized leave</p>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6 text-center border-l-4 border-yellow-500">
                @php
                    $attendanceRate = $stats['total_days'] > 0 
                        ? round(($stats['present'] / $stats['total_days']) * 100, 1) 
                        : 0;
                @endphp
                <div class="text-3xl font-bold text-slate-800 mb-2">{{ $attendanceRate }}%</div>
                <p class="text-slate-600">Attendance Rate</p>
                <p class="text-sm text-slate-500 mt-1">Overall percentage</p>
            </div>
        @else
            <!-- Period-wise stats -->
            <div class="bg-white rounded-xl shadow p-6 text-center border-l-4 border-green-500">
                <div class="text-3xl font-bold text-slate-800 mb-2">{{ $stats['present_periods'] ?? 0 }}</div>
                <p class="text-slate-600">Present Periods</p>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6 text-center border-l-4 border-red-500">
                <div class="text-3xl font-bold text-slate-800 mb-2">{{ $stats['absent_periods'] ?? 0 }}</div>
                <p class="text-slate-600">Absent Periods</p>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6 text-center border-l-4 border-blue-500">
                <div class="text-3xl font-bold text-slate-800 mb-2">{{ $stats['total_periods'] ?? 0 }}</div>
                <p class="text-slate-600">Total Periods</p>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6 text-center border-l-4 border-yellow-500">
                <div class="text-3xl font-bold text-slate-800 mb-2">{{ $stats['attendance_percentage'] ?? 0 }}%</div>
                <p class="text-slate-600">Attendance Rate</p>
            </div>
        @endif
    </div>

    <!-- Detailed Attendance Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-800">📅 Detailed Attendance Record</h3>
            <p class="text-sm text-slate-500">Attendance records for {{ $month }} {{ $year }}</p>
        </div>
        
        @if($attendance->count() > 0)
            @if($attendanceType === 'day_wise')
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Day</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Check In</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Check Out</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Total Hours</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Late/OT</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($attendance as $record)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                {{ $record->date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $record->date->format('l') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ 
                                    $record->status === 'present' ? 'bg-green-100 text-green-800' :
                                    ($record->status === 'absent' ? 'bg-red-100 text-red-800' :
                                    ($record->status === 'leave' ? 'bg-blue-100 text-blue-800' :
                                    ($record->status === 'half_day' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')))
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                @if($record->check_in)
                                    {{ \Carbon\Carbon::parse($record->check_in)->format('h:i A') }}
                                    @if($record->is_late)
                                        <br><span class="text-xs text-red-600">Late: {{ $record->late_minutes }}m</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                @if($record->check_out)
                                    {{ \Carbon\Carbon::parse($record->check_out)->format('h:i A') }}
                                    @if($record->early_departure_minutes > 0)
                                        <br><span class="text-xs text-yellow-600">Early: {{ $record->early_departure_minutes }}m</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                @if($record->total_hours)
                                    {{ number_format($record->total_hours, 1) }}h
                                    @if($record->overtime_minutes > 0)
                                        <br><span class="text-xs text-green-600">OT: {{ floor($record->overtime_minutes / 60) }}h</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($record->is_late)
                                    <div class="text-red-600">Late: {{ $record->late_minutes }}m</div>
                                @endif
                                @if($record->overtime_minutes > 0)
                                    <div class="text-green-600">OT: {{ $record->overtime_minutes }}m</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $record->notes ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <!-- Period-wise table -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Day</th>
                            @for($i = 1; $i <= 8; $i++)
                                <th class="px-3 py-3 text-center text-sm font-medium text-slate-500 uppercase">P{{ $i }}</th>
                            @endfor
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Present</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Absent</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Percentage</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($attendance as $record)
                        @php
                            $percentage = $record->getAttendancePercentage();
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                {{ $record->date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $record->date->format('l') }}
                            </td>
                            @for($i = 1; $i <= 8; $i++)
                                @php
                                    $periodField = "p{$i}";
                                    $status = $record->$periodField;
                                    $bgColor = [
                                        'P' => 'bg-green-100 text-green-800',
                                        'A' => 'bg-red-100 text-red-800',
                                        'L' => 'bg-blue-100 text-blue-800',
                                        'H' => 'bg-purple-100 text-purple-800',
                                        '-' => 'bg-slate-100 text-slate-800',
                                    ][$status] ?? 'bg-slate-100 text-slate-800';
                                @endphp
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-medium {{ $bgColor }}">
                                        {{ $status }}
                                    </span>
                                </td>
                            @endfor
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-700">
                                {{ $record->total_present }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-700">
                                {{ $record->total_absent }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-20 bg-slate-200 rounded-full h-2 mr-3">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <span class="text-sm font-semibold {{ $percentage >= 75 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $percentage }}%
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $record->notes ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        @else
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-slate-700">No attendance records found</h3>
            <p class="mt-1 text-sm text-slate-500">No attendance data available for the selected period.</p>
        </div>
        @endif
    </div>

    <!-- Summary Section -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">📋 Summary & Analysis</h3>
        
        @if($attendanceType === 'day_wise')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-slate-700 mb-3">Attendance Overview</h4>
                <div class="space-y-3">
                    @php
                        $totalDays = $stats['total_days'] ?? 0;
                        $presentDays = $stats['present'] ?? 0;
                        $absentDays = $stats['absent'] ?? 0;
                        $leaveDays = $stats['leave'] ?? 0;
                        $lateCount = $stats['late'] ?? 0;
                        $avgHours = $stats['average_hours'] ?? 0;
                    @endphp
                    
                    <div class="flex justify-between">
                        <span class="text-slate-600">Total Working Days:</span>
                        <span class="font-medium">{{ $totalDays }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Present Days:</span>
                        <span class="font-medium text-green-600">{{ $presentDays }} ({{ $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0 }}%)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Absent Days:</span>
                        <span class="font-medium text-red-600">{{ $absentDays }} ({{ $totalDays > 0 ? round(($absentDays / $totalDays) * 100, 1) : 0 }}%)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Leave Days:</span>
                        <span class="font-medium text-blue-600">{{ $leaveDays }} ({{ $totalDays > 0 ? round(($leaveDays / $totalDays) * 100, 1) : 0 }}%)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Late Arrivals:</span>
                        <span class="font-medium text-yellow-600">{{ $lateCount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Average Working Hours:</span>
                        <span class="font-medium">{{ number_format($avgHours, 1) }} hours/day</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Total Late Minutes:</span>
                        <span class="font-medium">{{ $stats['total_late_minutes'] ?? 0 }} minutes</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Total Overtime:</span>
                        <span class="font-medium text-green-600">{{ floor(($stats['total_overtime_minutes'] ?? 0) / 60) }}h {{ ($stats['total_overtime_minutes'] ?? 0) % 60 }}m</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="font-medium text-slate-700 mb-3">Attendance Chart</h4>
                <div style="height: 200px;">
                    <canvas id="summaryChart"></canvas>
                </div>
            </div>
        </div>
        @else
        <!-- Period-wise summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-slate-700 mb-3">Period Attendance Overview</h4>
                <div class="space-y-3">
                    @php
                        $totalPeriods = $stats['total_periods'] ?? 0;
                        $presentPeriods = $stats['present_periods'] ?? 0;
                        $absentPeriods = $stats['absent_periods'] ?? 0;
                        $attendanceRate = $stats['attendance_percentage'] ?? 0;
                    @endphp
                    
                    <div class="flex justify-between">
                        <span class="text-slate-600">Total Periods:</span>
                        <span class="font-medium">{{ $totalPeriods }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Present Periods:</span>
                        <span class="font-medium text-green-600">{{ $presentPeriods }} ({{ $totalPeriods > 0 ? round(($presentPeriods / $totalPeriods) * 100, 1) : 0 }}%)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Absent Periods:</span>
                        <span class="font-medium text-red-600">{{ $absentPeriods }} ({{ $totalPeriods > 0 ? round(($absentPeriods / $totalPeriods) * 100, 1) : 0 }}%)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Attendance Rate:</span>
                        <span class="font-medium {{ $attendanceRate >= 75 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $attendanceRate }}%
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Working Days:</span>
                        <span class="font-medium">{{ $stats['total_days'] ?? 0 }} days</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Average Periods/Day:</span>
                        <span class="font-medium">
                            {{ $stats['total_days'] > 0 ? round($totalPeriods / $stats['total_days'], 1) : 0 }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="font-medium text-slate-700 mb-3">Performance Rating</h4>
                <div class="flex items-center justify-center h-full">
                    @php
                        $rating = $attendanceRate >= 90 ? 'Excellent' :
                                 ($attendanceRate >= 80 ? 'Good' :
                                 ($attendanceRate >= 70 ? 'Satisfactory' : 'Needs Improvement'));
                        $ratingColor = $attendanceRate >= 90 ? 'text-green-600 bg-green-100' :
                                      ($attendanceRate >= 80 ? 'text-blue-600 bg-blue-100' :
                                      ($attendanceRate >= 70 ? 'text-yellow-600 bg-yellow-100' : 'text-red-600 bg-red-100'));
                    @endphp
                    <div class="text-center">
                        <div class="text-6xl font-bold mb-2 {{ $ratingColor }} w-32 h-32 rounded-full flex items-center justify-center mx-auto">
                            {{ $attendanceRate }}%
                        </div>
                        <div class="text-xl font-semibold mt-4 {{ $ratingColor }} px-4 py-2 rounded-lg">
                            {{ $rating }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Footer -->
    <div class="mt-8 pt-6 border-t border-slate-200 text-center text-sm text-slate-500">
        <p>Report generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>
        <p class="mt-1">This is an official attendance report of {{ config('app.name') }}</p>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($attendanceType === 'day_wise')
    // Day-wise summary chart
    const ctx = document.getElementById('summaryChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent', 'Leave', 'Half Day'],
            datasets: [{
                data: [
                    {{ $stats['present'] ?? 0 }},
                    {{ $stats['absent'] ?? 0 }},
                    {{ $stats['leave'] ?? 0 }},
                    {{ $stats['half_day'] ?? 0 }}
                ],
                backgroundColor: [
                    '#10B981',
                    '#EF4444',
                    '#3B82F6',
                    '#F59E0B'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    @else
    // Period-wise summary chart
    const ctx = document.getElementById('summaryChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Present Periods', 'Absent Periods'],
            datasets: [{
                data: [
                    {{ $stats['present_periods'] ?? 0 }},
                    {{ $stats['absent_periods'] ?? 0 }}
                ],
                backgroundColor: ['#10B981', '#EF4444'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    @endif
});
</script>
@endpush

@endsection