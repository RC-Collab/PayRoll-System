{{-- resources/views/attendance/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Attendance Management System')

@section('content')

<div class="mb-8">
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">🎯 Attendance Management</h1>
            <p class="text-slate-500 mt-2">Complete attendance tracking with the day-wise system</p>
        </div>
        <div class="flex gap-3">
            <!-- Quick Actions -->
            <div class="relative group">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                    📝 Quick Actions
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-slate-200 hidden group-hover:block z-10">
                    <div class="p-2">
                        <a href="#" onclick="showQuickMarkModal()" class="flex items-center gap-2 p-3 rounded-lg hover:bg-slate-50">
                            <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">✅</span>
                            <div>
                                <p class="font-medium text-slate-800">Quick Mark Attendance</p>
                                <p class="text-xs text-slate-500">Mark attendance for employees</p>
                            </div>
                        </a>
                        <a href="#" onclick="showSettingsModal()" class="flex items-center gap-2 p-3 rounded-lg hover:bg-slate-50">
                            <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">⚙️</span>
                            <div>
                                <p class="font-medium text-slate-800">Attendance Settings</p>
                                <p class="text-xs text-slate-500">Configure rules & settings</p>
                            </div>
                        </a>
                        <a href="{{ route('attendance.holidays') }}" class="flex items-center gap-2 p-3 rounded-lg hover:bg-slate-50">
                            <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">🎉</span>
                            <div>
                                <p class="font-medium text-slate-800">Manage Holidays</p>
                                <p class="text-xs text-slate-500">Configure holidays</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Holiday Alert -->
@if($todayHoliday ?? false)
<div class="mb-6">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <span class="text-yellow-400 text-xl">🎉</span>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Today is a Holiday!</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p><strong>{{ $todayHoliday->name }}</strong> - {{ ucfirst($todayHoliday->type) }} Holiday</p>
                    @if($todayHoliday->description)
                    <p class="mt-1">{{ $todayHoliday->description }}</p>
                    @endif
                    <p class="mt-2">No attendance will be marked for today. Employees will not be penalized.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Quick Stats - 4 CARDS -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl shadow border-l-4 border-green-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-slate-500">Present</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $monthlyStats['day_wise']['present'] ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-green-100 rounded-lg">
                ✅
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-2">Total: {{ $monthlyStats['day_wise']['total'] ?? 0 }} days</p>
    </div>
    
    <div class="bg-white p-6 rounded-2xl shadow border-l-4 border-red-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-slate-500">Absent</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $monthlyStats['day_wise']['absent'] ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-red-100 rounded-lg">
                ❌
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-2">Late: {{ $monthlyStats['day_wise']['late'] ?? 0 }} times</p>
    </div>
    
    <div class="bg-white p-6 rounded-2xl shadow border-l-4 border-yellow-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-slate-500">Holidays</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $monthlyStats['day_wise']['holidays'] ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-yellow-100 rounded-lg">
                🎉
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-2">
            <a href="{{ route('attendance.holidays') }}" class="text-indigo-600 hover:text-indigo-800">
                View holidays →
            </a>
        </p>
    </div>
    
    <div class="bg-white p-6 rounded-2xl shadow border-l-4 border-purple-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-slate-500">Working Days</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $monthlyStats['day_wise']['working_days'] ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-purple-100 rounded-lg">
                📊
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-2">Excluding holidays & weekends</p>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-6 rounded-2xl shadow mb-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Date</label>
            <input type="date" id="dateFilter" class="w-full px-4 py-2 border border-slate-300 rounded-lg" 
                   max="{{ now()->format('Y-m-d') }}"
                   value="{{ request('date', now()->format('Y-m-d')) }}">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Employee</label>
            <select id="employeeFilter" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                <option value="all">All Employees</option>
                @foreach($allEmployees as $emp)
                    <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>
                        {{ $emp->full_name }} ({{ $emp->employee_code }})
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Department</label>
            <select id="departmentFilter" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                <option value="all">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="flex items-end">
            <button id="applyFilter" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                🔍 Search
            </button>
        </div>

        <div class="flex items-end">
            <button onclick="showSettingsModal()" class="w-full px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700">
                ⚙️ Settings
            </button>
        </div>

    </div>
    
    <!-- Attendance Type Tabs -->
    <div class="flex border-b border-slate-200">
        <button id="dayWiseTab" class="px-6 py-3 font-medium text-sm border-b-2 border-indigo-500 text-indigo-600">
            📅 Day-wise Attendance
        </button>
    </div>
</div>

<!-- Day-wise Attendance Section -->
<div id="dayWiseSection" class="attendance-section">
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">📅 Day-wise Attendance</h2>
                <p class="text-slate-500">Daily check-in/check-out attendance records</p>
            </div>
            <button onclick="showQuickMarkModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                📝 Mark Attendance
            </button>
        </div>
        
        @if($attendances->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Check In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Check Out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Hours</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($attendances as $record)
                    @php
                        $employee = $record->employee;
                    @endphp
                    <tr class="hover:bg-slate-50"
                        data-employee-id="{{ $employee->id }}"
                        data-date="{{ $record->date->format('Y-m-d') }}"
                        data-status="{{ $record->status }}"
                        data-check-in="{{ optional($record->check_in)->format('H:i') }}"
                        data-check-out="{{ optional($record->check_out)->format('H:i') }}"
                        data-notes="{{ e($record->notes) }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($employee->profile_image)
                                        <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $employee->profile_image) }}" alt="">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-indigo-800 font-semibold">{{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-slate-900">
                                        <a href="{{ route('attendance.history', $employee->id) }}" class="hover:text-indigo-600">
                                            {{ $employee->full_name }}
                                        </a>
                                    </div>
                                    <div class="text-sm text-slate-500">{{ $employee->designation }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-slate-900">{{ $record->date->format('d M Y') }}</div>
                            <div class="text-sm text-slate-500">{{ $record->date->format('l') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($record->status == 'present') bg-green-100 text-green-800
                                @elseif($record->status == 'absent') bg-red-100 text-red-800
                                @elseif($record->status == 'leave') bg-blue-100 text-blue-800
                                @elseif($record->status == 'half_day') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($record->check_in)
                                <div class="text-sm text-slate-900">
                                    {{ \Carbon\Carbon::parse($record->check_in)->format('h:i A') }}
                                </div>
                                @if($record->is_late)
                                    <div class="text-xs text-red-600">Late: {{ $record->late_minutes }} min</div>
                                @endif
                            @else
                                <span class="text-sm text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($record->check_out)
                                <div class="text-sm text-slate-900">
                                    {{ \Carbon\Carbon::parse($record->check_out)->format('h:i A') }}
                                </div>
                            @else
                                <span class="text-sm text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($record->total_hours)
                                <div class="text-sm text-slate-900">
                                    {{ number_format($record->regular_hours, 1) }}h
                                    @if($record->overtime > 0)
                                        + OT {{ number_format($record->overtime, 1) }}h
                                    @endif
                                </div>
                                @if($record->overtime_minutes > 0)
                                    <div class="text-xs text-green-600">
                                        (OT raw: {{ floor($record->overtime_minutes / 60) }}h {{ $record->overtime_minutes % 60 }}m)
                                    </div>
                                @endif
                            @else
                                <span class="text-sm text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex gap-2">
                                @if(auth()->user()->isAdmin() || auth()->user()->isHr())
                                <button onclick="editDayAttendance(this, {{ $record->id }})" class="text-indigo-600 hover:text-indigo-900">
                                    ✏️
                                </button>
                        @endif
                                <a href="{{ route('attendance.history', $employee->id) }}" class="text-slate-600 hover:text-slate-900">
                                    📊
                                </a>
                                @if(auth()->user()->isAdmin() || auth()->user()->isHr())
                                <form method="POST" action="{{ route('attendance.destroy', $record->id) }}" onsubmit="return confirm('Delete this attendance?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">🗑️</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($attendances->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $attendances->links() }}
        </div>
        @endif
        @else
        <div class="p-12 text-center">
            <div class="mx-auto w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-6">
                📅
            </div>
            <h3 class="text-lg font-semibold text-slate-700 mb-2">No Day-wise Attendance Records</h3>
            <p class="text-slate-500">No day-wise attendance records found for {{ $currentMonth->format('F Y') }}</p>
        </div>
        @endif
    </div>
</div>

<!-- Quick Mark Modal -->
<div id="quickMarkModal" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl">
        <div class="p-6 border-b border-slate-200">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-slate-800">📝 Mark Day-wise Attendance</h3>
                <button onclick="closeQuickMarkModal()" class="text-slate-400 hover:text-slate-600">
                    ✕
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <form id="quickMarkForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Employee</label>
                        <select name="employee_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            <option value="">Select Employee</option>
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Date</label>
                        <input type="date" name="date" required 
                               max="{{ now()->format('Y-m-d') }}"
                               value="{{ date('Y-m-d') }}"
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                        <select name="type" required class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="leave">Leave</option>
                            <option value="half_day">Half Day</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Check In Time</label>
                        <input type="time" name="check_in" 
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Check Out Time</label>
                        <input type="time" name="check_out" 
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Notes</label>
                        <input type="text" name="notes" 
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-200">
                    <button type="button" onclick="closeQuickMarkModal()" 
                            class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="button" onclick="submitQuickMark()" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Mark Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Settings Modal -->
<div id="settingsModal" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-slate-200 sticky top-0 bg-white">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-slate-800">⚙️ Attendance Settings</h3>
                <button onclick="closeSettingsModal()" class="text-slate-400 hover:text-slate-600">
                    ✕
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Tabs for Settings -->
            <div class="flex border-b border-slate-200 mb-6">
                <button onclick="switchTab('holidays')" class="px-4 py-2 font-medium text-sm border-b-2 border-indigo-500 text-indigo-600" id="holidaysTab">
                    🎉 Manage Holidays
                </button>
                <button onclick="switchTab('workingdays')" class="px-4 py-2 font-medium text-sm text-slate-500 hover:text-slate-700" id="workingdaysTab">
                    📊 Custom Working Days
                </button>
            </div>

            <!-- Holidays Tab -->
            <div id="holidaysContent" class="tab-content">
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-slate-800 mb-4">Add Holiday</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Date</label>
                            <input type="date" id="holidayDate" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Holiday Name</label>
                            <input type="text" id="holidayName" placeholder="e.g., Dashain" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Type</label>
                            <select id="holidayType" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                                <option value="national">National</option>
                                <option value="regional">Regional</option>
                                <option value="company">Company</option>
                                <option value="optional">Optional</option>
                            </select>
                        </div>
                    </div>
                    <button onclick="addHoliday()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        + Add Holiday
                    </button>
                </div>

                <div>
                    <h4 class="text-lg font-semibold text-slate-800 mb-4">Holidays</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Holiday Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Type</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="holidaysList" class="divide-y divide-slate-200">
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                                        Loading holidays...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Working Days Tab -->
            <div id="workingdaysContent" class="tab-content hidden">
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-slate-800 mb-4">Select Working Days</h4>
                    <p class="text-sm text-slate-600 mb-4">Choose which days are working days in your organization</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
                        @php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $defaultWorkingDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                        @endphp
                        @foreach($days as $day)
                            <div class="flex items-center">
                                <input type="checkbox" id="day_{{ strtolower($day) }}" value="{{ $day }}" 
                                       {{ in_array($day, $defaultWorkingDays) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-slate-300" onchange="updateWorkingDays()">
                                <label for="day_{{ strtolower($day) }}" class="ml-3 font-medium text-slate-700">
                                    {{ $day }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-6 border-t border-slate-200">
                    <button onclick="saveWorkingDays()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        ✓ Save Working Days
                    </button>
                    <button onclick="closeSettingsModal()" class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Settings Modal Functions
function showQuickMarkModal() {
    document.getElementById('quickMarkModal').classList.remove('hidden');
    document.getElementById('quickMarkModal').classList.add('flex');
}

function closeQuickMarkModal() {
    document.getElementById('quickMarkModal').classList.add('hidden');
    document.getElementById('quickMarkModal').classList.remove('flex');
}

function editDayAttendance(el, id) {
    // pull data from row attributes and prefill the quick-mark form
    const row = el.closest('tr');
    if (!row) return;
    showQuickMarkModal();

    const form = document.getElementById('quickMarkForm');
    form.employee_id.value = row.dataset.employeeId;
    form.date.value = row.dataset.date;
    form.type.value = row.dataset.status;
    form.check_in.value = row.dataset.checkIn || '';
    form.check_out.value = row.dataset.checkOut || '';
    form.notes.value = row.dataset.notes || '';
}

function showSettingsModal() {
    document.getElementById('settingsModal').classList.remove('hidden');
    document.getElementById('settingsModal').classList.add('flex');
    loadHolidays();
    loadWorkingDaysConfig();
}

function closeSettingsModal() {
    document.getElementById('settingsModal').classList.add('hidden');
    document.getElementById('settingsModal').classList.remove('flex');
}

// --------- attendance filter handling ----------
function applyAttendanceFilter() {
    const date = document.getElementById('dateFilter').value;
    const employee = document.getElementById('employeeFilter').value;
    const department = document.getElementById('departmentFilter').value;

    const params = new URLSearchParams();
    if (date) params.set('date', date);
    if (employee && employee !== 'all') params.set('employee', employee);
    if (department && department !== 'all') params.set('department', department);

    const url = window.location.pathname + '?' + params.toString();
    window.location.href = url;
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('applyFilter').addEventListener('click', function(e) {
        e.preventDefault();
        applyAttendanceFilter();
    });
    ['dateFilter','employeeFilter','departmentFilter'].forEach(id => {
        document.getElementById(id)?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyAttendanceFilter();
            }
        });
    });
});

function switchTab(tab) {
    // Hide all tabs
    document.getElementById('holidaysContent').classList.add('hidden');
    document.getElementById('workingdaysContent').classList.add('hidden');
    document.getElementById('holidaysTab').classList.remove('border-indigo-500', 'text-indigo-600');
    document.getElementById('workingdaysTab').classList.remove('border-indigo-500', 'text-indigo-600');
    document.getElementById('holidaysTab').classList.add('text-slate-500');
    document.getElementById('workingdaysTab').classList.add('text-slate-500');
    
    // Show selected tab
    if (tab === 'holidays') {
        document.getElementById('holidaysContent').classList.remove('hidden');
        document.getElementById('holidaysTab').classList.add('border-indigo-500', 'text-indigo-600');
        document.getElementById('holidaysTab').classList.remove('text-slate-500');
    } else {
        document.getElementById('workingdaysContent').classList.remove('hidden');
        document.getElementById('workingdaysTab').classList.add('border-indigo-500', 'text-indigo-600');
        document.getElementById('workingdaysTab').classList.remove('text-slate-500');
    }
}

function loadHolidays() {
    const currentMonth = document.getElementById('dateFilter').value || new Date().toISOString().slice(0, 7);
    
    fetch(`/api/settings/holidays?month=${currentMonth}`, { credentials: 'include' })
        .then(response => response.json())
        .then(data => {
            const holidaysList = document.getElementById('holidaysList');
            if (data.data && data.data.length > 0) {
                const rows = data.data.map(holiday => `
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap text-sm">${new Date(holiday.date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm">${holiday.name}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm"><span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded text-xs">${holiday.type}</span></td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                            <button onclick="deleteHoliday(${holiday.id})" class="text-red-600 hover:text-red-800">Delete</button>
                        </td>
                    </tr>
                `).join('');
                holidaysList.innerHTML = rows;
            } else {
                holidaysList.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-slate-500 text-sm">
                            No holidays configured yet
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading holidays:', error);
            document.getElementById('holidaysList').innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-4 text-center text-red-500 text-sm">
                        Error loading holidays
                    </td>
                </tr>
            `;
        });
}

function addHoliday() {
    const date = document.getElementById('holidayDate').value;
    const name = document.getElementById('holidayName').value;
    const type = document.getElementById('holidayType').value;
    
    if (!date || !name) {
        alert('Please fill in date and holiday name');
        return;
    }
    
    fetch('/api/settings/holidays', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            date: date,
            name: name,
            type: type
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Holiday added successfully!');
            document.getElementById('holidayDate').value = '';
            document.getElementById('holidayName').value = '';
            // reload the page so statistics (working days, holiday count) update immediately
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to add holiday'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add holiday');
    });
}

function deleteHoliday(id) {
    if (!confirm('Are you sure you want to delete this holiday?')) {
        return;
    }
    
    fetch(`/api/settings/holidays/${id}`, {
        method: 'DELETE',
        credentials: 'include',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // on deletion also refresh page to update stats
            location.reload();
        } else {
            alert('Error deleting holiday');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete holiday');
    });
}

// quick mark submission logic
function submitQuickMark() {
    const form = document.getElementById('quickMarkForm');
    const formData = new FormData(form);
    const payload = {
        employee_id: formData.get('employee_id'),
        date: formData.get('date'),
        type: formData.get('type'),
        check_in: formData.get('check_in'),
        check_out: formData.get('check_out'),
        notes: formData.get('notes'),
    };

    fetch('/api/attendance/quick-mark', {
        method: 'POST',
        credentials: 'include', // include cookies even if host/address differs
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Attendance marked successfully');
            closeQuickMarkModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to mark attendance'));
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Failed to mark attendance');
    });
}

function loadWorkingDaysConfig() {
    fetch('/api/settings/working-days', {
        method: 'GET',
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.data) {
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            days.forEach(day => {
                const checkbox = document.getElementById('day_' + day.toLowerCase());
                if (checkbox) {
                    checkbox.checked = data.data[day] || false;
                }
            });
        }
    })
    .catch(error => {
        console.error('Error loading working days:', error);
    });
}

function updateWorkingDays() {
    // Just update the checkboxes; save will be called on button click
}

function saveWorkingDays() {
    const workingDays = [];
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    days.forEach(day => {
        const checkbox = document.getElementById('day_' + day.toLowerCase());
        if (checkbox && checkbox.checked) {
            workingDays.push(day);
        }
    });
    
    if (workingDays.length === 0) {
        alert('Please select at least one working day');
        return;
    }
    
    fetch('/api/settings/working-days', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            working_days: workingDays
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Working days saved successfully!');
            closeSettingsModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save working days'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save working days');
    });
}
</script>
@endpush

<style>
.attendance-section {
    display: block;
}
</style>