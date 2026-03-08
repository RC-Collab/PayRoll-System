@extends('layouts.app')

@section('title', 'Attendance History - ' . $employee->full_name)

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
                <h1 class="text-3xl font-bold text-slate-800">📜 Attendance History</h1>
                <p class="text-slate-500">{{ $employee->full_name }} - Complete attendance record</p>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('attendance.employeeReport', [$employee->id, 'month' => 'all', 'year' => date('Y')]) }}" 
                   class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    📊 Full Report
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700">
                    🖨️ Print
                </button>
            </div>
        </div>
    </div>

    <!-- Employee Info -->
    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <div class="flex items-center gap-6">
            @if($employee->profile_image)
                <img src="{{ asset('storage/' . $employee->profile_image) }}" 
                     alt="{{ $employee->full_name }}"
                     class="w-20 h-20 rounded-full object-cover">
            @else
                <div class="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-2xl font-semibold text-indigo-800">
                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                    </span>
                </div>
            @endif
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-slate-800">{{ $employee->full_name }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3">
                    <div>
                        <p class="text-sm text-slate-500">Employee Code</p>
                        <p class="font-medium">{{ $employee->employee_code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Designation</p>
                        <p class="font-medium">{{ $employee->designation }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Department</p>
                        <p class="font-medium">{{ $employee->departments->first()->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Attendance Type</p>
                        <p class="font-medium capitalize">Day-wise</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance History -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-800">Complete Attendance History</h3>
            <p class="text-sm text-slate-500">All attendance records for {{ $employee->full_name }}</p>
        </div>
        
        @if($attendances->count() > 0)
            <!-- Day-wise history -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Day</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Check In</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Check Out</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Hours</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Late/OT</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-slate-500 uppercase">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($attendances as $record)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                {{ $record->date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $record->date->format('D') }}
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
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                @if($record->total_hours)
                                    {{ number_format($record->total_hours, 1) }}h
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($record->is_late)
                                    <div class="text-red-600 text-xs">Late: {{ $record->late_minutes }}m</div>
                                @endif
                                @if($record->overtime_minutes > 0)
                                    <div class="text-green-600 text-xs">OT: {{ $record->overtime_minutes }}m</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 max-w-xs truncate">
                                {{ $record->notes ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $attendances->links() }}
            </div>
        @else
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-slate-700">No attendance history found</h3>
            <p class="mt-1 text-sm text-slate-500">No attendance records available for this employee.</p>
        </div>
        @endif
    </div>
</div>
@endsection