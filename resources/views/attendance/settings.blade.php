@extends('layouts.app')

@section('title', 'Attendance Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-800">⚙️ Attendance Settings</h1>
        <p class="text-slate-500">Configure attendance rules and settings for employees</p>
    </div>

    <!-- Settings Tabs -->
    <div class="bg-white rounded-xl shadow mb-6">
        <div class="border-b border-slate-200">
            <nav class="flex">
                <button id="employeeSettingsTab" 
                        class="px-6 py-4 font-medium text-sm border-b-2 border-indigo-500 text-indigo-600">
                    👤 Employee Settings
                </button>
                <button id="departmentSettingsTab" 
                        class="px-6 py-4 font-medium text-sm text-slate-500 hover:text-slate-700">
                    🏢 Department Settings
                </button>
                <button id="generalSettingsTab" 
                        class="px-6 py-4 font-medium text-sm text-slate-500 hover:text-slate-700">
                    ⚙️ General Settings
                </button>
            </nav>
        </div>
    </div>

    <!-- Employee Settings -->
    <div id="employeeSettings" class="settings-section">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Employee List -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Select Employee</h3>
                    <div class="mb-4">
                        <input type="text" 
                               id="employeeSearch" 
                               placeholder="Search employees..."
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                    </div>
                    <div class="space-y-2 max-h-[500px] overflow-y-auto">
                        @foreach($employees as $employee)
                        <button onclick="loadEmployeeSettings({{ $employee->id }})" 
                                class="w-full p-3 text-left border border-slate-200 rounded-lg hover:bg-slate-50 employee-item"
                                data-employee-id="{{ $employee->id }}">
                            <div class="flex items-center gap-3">
                                @if($employee->profile_image)
                                    <img src="{{ asset('storage/' . $employee->profile_image) }}" 
                                         alt="{{ $employee->full_name }}"
                                         class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-sm font-semibold text-indigo-800">
                                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-slate-800">{{ $employee->full_name }}</div>
                                    <div class="text-sm text-slate-500">{{ $employee->designation }}</div>
                                </div>
                            </div>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Settings Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4" id="selectedEmployeeName">Employee Settings</h3>
                    <p class="text-slate-500 mb-6" id="selectedEmployeeInfo">Select an employee to configure their attendance settings</p>
                    
                    <form id="attendanceSettingsForm">
                        @csrf
                        <input type="hidden" id="employeeId" name="employee_id">
                        
<!-- Attendance Type (always day-wise) -->
                        <div class="mb-8">
                            <h4 class="font-medium text-slate-700 mb-4">🎯 Attendance Type</h4>
                            <p class="text-sm text-slate-500">
                                Day-wise attendance (check-in/check-out) is the only supported mode.
                            </p>
                        </div>

                        <!-- Day-wise Settings -->
                        <div id="dayWiseSettings" class="mb-8">
                            <h4 class="font-medium text-slate-700 mb-4">📅 Day-wise Settings</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Start Time</label>
                                    <input type="time" 
                                           id="startTime" 
                                           name="start_time"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg"
                                           value="09:00">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">End Time</label>
                                    <input type="time" 
                                           id="endTime" 
                                           name="end_time"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg"
                                           value="17:00">
                                </div>
                                <div class="col-span-full">
                                    <p class="text-xs text-slate-500 mt-1">
                                        Specify the standard shift start/end for this employee. Overtime
                                        will be calculated automatically relative to the end time, and
                                        check‑in/out times are rounded to the nearest half‑hour
                                        (15 min tolerance).
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Late Threshold (minutes)</label>
                                    <input type="number" 
                                           id="lateThreshold" 
                                           name="late_threshold_minutes"
                                           min="0"
                                           value="15"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Half Day Threshold (hours)</label>
                                    <input type="number" 
                                           id="halfDayThreshold" 
                                           name="half_day_threshold_hours"
                                           min="0"
                                           step="0.5"
                                           value="4"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                                </div>
                            </div>
                        </div>

                        <!-- period-wise settings removed; only day-wise supported -->

                        <!-- Flexible Schedule (For both types) -->
                        <div class="mb-8">
                            <h4 class="font-medium text-slate-700 mb-4">🗓️ Working Days & Schedule</h4>
                            
                            <!-- Flexible Schedule Toggle -->
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           id="flexibleSchedule" 
                                           name="flexible_schedule"
                                           value="1"
                                           class="mr-2"
                                           onchange="toggleFlexibleSchedule()">
                                    <span>Use Different Schedule for Each Day</span>
                                </label>
                            </div>
                            
                            <!-- Default Schedule (when flexible is off) -->
                            <div id="defaultScheduleSection">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Working Days</label>
                                    <div class="flex flex-wrap gap-3">
                                        @php
                                            $days = [
                                                ['value' => 1, 'label' => 'Monday'],
                                                ['value' => 2, 'label' => 'Tuesday'],
                                                ['value' => 3, 'label' => 'Wednesday'],
                                                ['value' => 4, 'label' => 'Thursday'],
                                                ['value' => 5, 'label' => 'Friday'],
                                                ['value' => 6, 'label' => 'Saturday'],
                                                ['value' => 0, 'label' => 'Sunday'],
                                            ];
                                        @endphp
                                        @foreach($days as $day)
                                            <label class="flex items-center">
                                                <input type="checkbox" 
                                                       name="working_days[]" 
                                                       value="{{ $day['value'] }}"
                                                       class="mr-2 working-day-checkbox" 
                                                       {{ in_array($day['value'], [1,2,3,4,5]) ? 'checked' : '' }}>
                                                <span>{{ $day['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Custom Schedule (when flexible is on) -->
                            <div id="customScheduleSection" class="hidden">
                                <div class="space-y-4">
                                    @foreach($days as $day)
                                    <div class="border border-slate-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <label class="flex items-center">
                                                <input type="checkbox" 
                                                       name="custom_working_days[]" 
                                                       value="{{ $day['value'] }}"
                                                       class="mr-2 custom-working-day-checkbox"
                                                       {{ in_array($day['value'], [1,2,3,4,5]) ? 'checked' : '' }}
                                                       onchange="toggleCustomDaySchedule({{ $day['value'] }})">
                                                <span class="font-medium">{{ $day['label'] }}</span>
                                            </label>
                                        </div>
                                        
                                        <div id="customDaySchedule{{ $day['value'] }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-sm text-slate-600 mb-1">Start Time</label>
                                                <input type="time" 
                                                       name="custom_schedule[{{ $day['value'] }}][start]"
                                                       class="w-full px-3 py-2 border border-slate-300 rounded"
                                                       value="09:00">
                                            </div>
                                            <div>
                                                <label class="block text-sm text-slate-600 mb-1">End Time</label>
                                                <input type="time" 
                                                       name="custom_schedule[{{ $day['value'] }}][end]"
                                                       class="w-full px-3 py-2 border border-slate-300 rounded"
                                                       value="17:00">
                                            </div>
                                            <div class="period-only">
                                                <label class="block text-sm text-slate-600 mb-1">Period Count</label>
                                                <input type="number" 
                                                       name="custom_schedule[{{ $day['value'] }}][periods]"
                                                       min="1" max="12"
                                                       class="w-full px-3 py-2 border border-slate-300 rounded"
                                                       value="8">
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <!-- Hidden input to store custom schedule JSON -->
                                <input type="hidden" id="customScheduleJson" name="custom_schedule_json">
                            </div>
                        </div>

                        <!-- Advanced Settings -->
                        <div class="mb-8">
                            <h4 class="font-medium text-slate-700 mb-4">⚡ Advanced Settings</h4>
                            <div class="space-y-4">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           id="autoCalculateHours" 
                                           name="auto_calculate_hours"
                                           value="1"
                                           class="mr-2" checked>
                                    <span>Auto-calculate working hours</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           id="enableOvertime" 
                                           name="enable_overtime"
                                           value="1"
                                           class="mr-2"
                                           onchange="toggleOvertimeRate()">
                                    <span>Enable overtime calculation</span>
                                </label>
                                <div id="overtimeRateSection" class="ml-6 hidden">
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Overtime Rate (per hour)</label>
                                    <input type="number" 
                                           id="overtimeRate" 
                                           name="overtime_rate"
                                           min="0"
                                           step="0.01"
                                           class="w-full md:w-48 px-4 py-2 border border-slate-300 rounded-lg">
                                </div>
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           id="enableEarlyDeparture" 
                                           name="enable_early_departure"
                                           value="1"
                                           class="mr-2"
                                           onchange="toggleEarlyDeparture()">
                                    <span>Track early departure</span>
                                </label>
                                <div id="earlyDepartureSection" class="ml-6 hidden">
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Early Departure Threshold (minutes)</label>
                                    <input type="number" 
                                           id="earlyDepartureMinutes" 
                                           name="early_departure_minutes"
                                           min="0"
                                           value="30"
                                           class="w-full md:w-48 px-4 py-2 border border-slate-300 rounded-lg">
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-3 pt-6 border-t border-slate-200">
                            <button type="button" 
                                    onclick="resetSettingsForm()"
                                    class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                                Reset
                            </button>
                            <button type="button" 
                                    onclick="saveSettings()"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Settings (Hidden by default) -->
    <div id="departmentSettings" class="settings-section hidden">
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">🏢 Department Settings</h3>
            <p class="text-slate-500 mb-6">Apply attendance settings to all employees in a department</p>
            
            <form id="departmentSettingsForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Select Department</label>
                        <select id="bulkDepartment" 
                                name="department_id"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            <option value="">Choose department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- attendance type removed; always day-wise -->
                </div>
                
                <div class="mb-6">
                    <h4 class="font-medium text-slate-700 mb-4">Settings to Apply</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Start Time</label>
                            <input type="time" 
                                   name="start_time"
                                   class="w-full px-4 py-2 border border-slate-300 rounded-lg"
                                   value="09:00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">End Time</label>
                            <input type="time" 
                                   name="end_time"
                                   class="w-full px-4 py-2 border border-slate-300 rounded-lg"
                                   value="17:00">
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" 
                            onclick="resetBulkSettings()"
                            class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                        Clear
                    </button>
                    <button type="button" 
                            onclick="saveBulkSettings()"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Apply to Department
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- period settings removed; day-wise only -->

    <!-- General Settings (Hidden by default) -->
    <div id="generalSettings" class="settings-section hidden">
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">⚙️ General Settings</h3>
            <p class="text-slate-500 mb-6">System-wide attendance configuration</p>
            
            <form id="generalSettingsForm">
                @csrf
                <div class="space-y-6">
                    <!-- Auto Attendance Rules -->
                    <div>
                        <h4 class="font-medium text-slate-700 mb-4">🔄 Auto Attendance Rules</h4>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="auto_mark_absent" value="1" class="mr-2">
                                <span>Auto-mark absent after 3 consecutive days without check-in</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="auto_calculate_ot" value="1" class="mr-2" checked>
                                <span>Auto-calculate overtime for late check-outs</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="enable_weekly_off" value="1" class="mr-2" checked>
                                <span>Enable weekly off (Sundays) for all employees</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Holiday Configuration -->
                    <div>
                        <h4 class="font-medium text-slate-700 mb-4">🎉 Holiday Configuration</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Public Holidays Per Year</label>
                                <input type="number" name="public_holidays" min="0" max="30" 
                                       value="12" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Carry Forward Leave Days</label>
                                <input type="number" name="carry_forward_days" min="0" max="30" 
                                       value="5" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reporting -->
                    <div>
                        <h4 class="font-medium text-slate-700 mb-4">📊 Reporting</h4>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="send_daily_report" value="1" class="mr-2">
                                <span>Send daily attendance report to HR</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="send_monthly_report" value="1" class="mr-2" checked>
                                <span>Send monthly attendance report to department heads</span>
                            </label>
                            <div class="ml-6">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Report Day (of month)</label>
                                <input type="number" name="report_day" min="1" max="28" 
                                       value="5" class="w-full md:w-32 px-4 py-2 border border-slate-300 rounded-lg">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Integration -->
                    <div>
                        <h4 class="font-medium text-slate-700 mb-4">🔗 Integration Settings</h4>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="sync_with_biometric" value="1" class="mr-2">
                                <span>Sync with biometric devices</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="enable_api_access" value="1" class="mr-2">
                                <span>Enable API access for mobile app</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                        <button type="button" 
                                onclick="resetGeneralSettings()"
                                class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                            Reset to Defaults
                        </button>
                        <button type="button" 
                                onclick="saveGeneralSettings()"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Save General Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Tab management
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tabs
    const tabs = {
        employeeSettingsTab: 'employeeSettings',
        departmentSettingsTab: 'departmentSettings', 
        generalSettingsTab: 'generalSettings'
    };
    
    Object.entries(tabs).forEach(([tabId, sectionId]) => {
        const tab = document.getElementById(tabId);
        if (tab) {
            tab.addEventListener('click', function() {
                // Update tabs
                document.querySelectorAll('button[id$="Tab"]').forEach(t => {
                    t.classList.remove('border-indigo-500', 'text-indigo-600');
                    t.classList.add('text-slate-500');
                });
                this.classList.add('border-indigo-500', 'text-indigo-600');
                this.classList.remove('text-slate-500');
                
                // Show selected section
                document.querySelectorAll('.settings-section').forEach(section => {
                    section.classList.add('hidden');
                });
                document.getElementById(sectionId).classList.remove('hidden');
            });
        }
    });
    
    // Initialize with first tab active
    document.getElementById('employeeSettingsTab').classList.add('border-indigo-500', 'text-indigo-600');
    document.getElementById('employeeSettings').classList.remove('hidden');
    
    // Load first employee's settings by default
    setTimeout(() => {
        const firstEmployee = document.querySelector('.employee-item');
        if (firstEmployee) {
            const employeeId = firstEmployee.dataset.employeeId;
            console.log('Loading first employee ID:', employeeId);
            loadEmployeeSettings(employeeId);
        } else {
            console.warn('No employees found to load settings');
            document.getElementById('selectedEmployeeName').textContent = 'No Employees Found';
            document.getElementById('selectedEmployeeInfo').textContent = 'Please add employees first';
        }
    }, 100);
});

// Employee search
document.getElementById('employeeSearch')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    document.querySelectorAll('.employee-item').forEach(item => {
        const employeeName = item.textContent.toLowerCase();
        item.style.display = employeeName.includes(searchTerm) ? 'block' : 'none';
    });
});

// period-related helpers removed; only day-wise logic remains

// Toggle flexible schedule
function toggleFlexibleSchedule() {
    const isFlexible = document.getElementById('flexibleSchedule').checked;
    
    if (isFlexible) {
        document.getElementById('defaultScheduleSection').classList.add('hidden');
        document.getElementById('customScheduleSection').classList.remove('hidden');
        updateCustomSchedule();
    } else {
        document.getElementById('defaultScheduleSection').classList.remove('hidden');
        document.getElementById('customScheduleSection').classList.add('hidden');
    }
}

// Toggle custom day schedule
function toggleCustomDaySchedule(dayValue) {
    const checkbox = document.querySelector(`[name="custom_working_days[]"][value="${dayValue}"]`);
    const scheduleDiv = document.getElementById(`customDaySchedule${dayValue}`);
    
    if (checkbox.checked) {
        scheduleDiv.classList.remove('hidden');
    } else {
        scheduleDiv.classList.add('hidden');
    }
    
    updateCustomSchedule();
}

// Update custom schedule JSON
function updateCustomSchedule() {
    const customSchedule = {};
    const checkboxes = document.querySelectorAll('.custom-working-day-checkbox:checked');
    
    checkboxes.forEach(checkbox => {
        const dayValue = checkbox.value;
        const startInput = document.querySelector(`[name="custom_schedule[${dayValue}][start]"]`);
        const endInput = document.querySelector(`[name="custom_schedule[${dayValue}][end]"]`);
        const periodsInput = document.querySelector(`[name="custom_schedule[${dayValue}][periods]"]`);
        
        if (startInput && endInput) {
            customSchedule[dayValue] = {
                start: startInput.value,
                end: endInput.value,
                periods: periodsInput ? periodsInput.value : 8
            };
        }
    });
    
    document.getElementById('customScheduleJson').value = JSON.stringify(customSchedule);
}

// Toggle overtime periods
function toggleOvertimePeriods() {
    document.getElementById('overtimePeriodsSection').classList.toggle('hidden', 
        !document.getElementById('enableOvertimePeriods').checked);
}

// Toggle overtime rate
function toggleOvertimeRate() {
    document.getElementById('overtimeRateSection').classList.toggle('hidden', 
        !document.getElementById('enableOvertime').checked);
}

// Toggle early departure
function toggleEarlyDeparture() {
    document.getElementById('earlyDepartureSection').classList.toggle('hidden', 
        !document.getElementById('enableEarlyDeparture').checked);
}

// Load employee settings
function loadEmployeeSettings(employeeId) {
    console.log('Loading settings for employee:', employeeId);
    
    // Show loading state
    document.getElementById('selectedEmployeeName').textContent = 'Loading...';
    document.getElementById('selectedEmployeeInfo').textContent = 'Loading settings...';
    
    fetch(`/attendance/settings/${employeeId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Settings data:', data);
        
        if (data.error) {
            throw new Error(data.message || data.error);
        }
        
        const employee = data.employee;
        const settings = data.settings;
        
        // Update employee info
        document.getElementById('employeeId').value = employee.id;
        document.getElementById('selectedEmployeeName').textContent = `${employee.full_name} - Settings`;
        
        const deptName = employee.departments && employee.departments.length > 0 
            ? employee.departments[0].name 
            : 'No Department';
        document.getElementById('selectedEmployeeInfo').textContent = 
            `${employee.designation} | ${deptName}`;
        
        // Highlight selected employee
        document.querySelectorAll('.employee-item').forEach(item => {
            item.classList.remove('bg-indigo-50', 'border-indigo-300', 'border-2');
            if (item.dataset.employeeId == employeeId) {
                item.classList.add('bg-indigo-50', 'border-indigo-300', 'border-2');
            }
        });
        
        // Populate settings
        populateSettingsForm(settings);
        
    })
    .catch(error => {
        console.error('Error loading settings:', error);
        
        // Show error but keep form
        document.getElementById('selectedEmployeeName').textContent = 'Error Loading Settings';
        document.getElementById('selectedEmployeeInfo').textContent = 
            'Failed to load settings. Please try again.';
        
        // Set default values
        setDefaultSettings();
    });
}

// Populate settings form
function populateSettingsForm(settings) {
    console.log('Populating settings:', settings);
    
    // Basic settings
    document.getElementById('startTime').value = settings.start_time || '09:00';
    document.getElementById('endTime').value = settings.end_time || '17:00';
    document.getElementById('lateThreshold').value = settings.late_threshold_minutes || 15;
    document.getElementById('halfDayThreshold').value = settings.half_day_threshold_hours || 4;
    
    // Day‑wise only; no attendance type or period settings to populate

    
    // Working days
    const workingDays = settings.working_days || [1, 2, 3, 4, 5];
    document.querySelectorAll('.working-day-checkbox').forEach(checkbox => {
        const value = parseInt(checkbox.value);
        checkbox.checked = workingDays.includes(value);
    });
    
    // Flexible schedule
    document.getElementById('flexibleSchedule').checked = settings.flexible_schedule || false;
    toggleFlexibleSchedule();
    
    // Custom schedule
    if (settings.custom_schedule && Object.keys(settings.custom_schedule).length > 0) {
        Object.entries(settings.custom_schedule).forEach(([day, schedule]) => {
            const startInput = document.querySelector(`[name="custom_schedule[${day}][start]"]`);
            const endInput = document.querySelector(`[name="custom_schedule[${day}][end]"]`);
            const periodsInput = document.querySelector(`[name="custom_schedule[${day}][periods]"]`);
            const checkbox = document.querySelector(`[name="custom_working_days[]"][value="${day}"]`);
            
            if (startInput) startInput.value = schedule.start || '09:00';
            if (endInput) endInput.value = schedule.end || '17:00';
            if (periodsInput) periodsInput.value = schedule.periods || 8;
            if (checkbox) {
                checkbox.checked = true;
                toggleCustomDaySchedule(day);
            }
        });
        updateCustomSchedule();
    }
    
    // Advanced settings
    document.getElementById('autoCalculateHours').checked = settings.auto_calculate_hours !== false;
    document.getElementById('enableOvertime').checked = settings.enable_overtime || false;
    document.getElementById('overtimeRate').value = settings.overtime_rate || '';
    document.getElementById('enableEarlyDeparture').checked = settings.enable_early_departure || false;
    document.getElementById('earlyDepartureMinutes').value = settings.early_departure_minutes || 30;
    
    toggleOvertimeRate();
    toggleEarlyDeparture();
    
    // period schedule removed (day‑wise only)

}

}

// Save settings
function saveSettings() {
    const employeeId = document.getElementById('employeeId').value;
    if (!employeeId) {
        alert('Please select an employee first');
        return;
    }
    
    // Get form data
    const formData = new FormData(document.getElementById('attendanceSettingsForm'));
    
    // Convert to JSON
    const data = {
        employee_id: employeeId,
        start_time: formData.get('start_time') || null,
        end_time: formData.get('end_time') || null,
        late_threshold_minutes: formData.get('late_threshold_minutes') || 15,
        half_day_threshold_hours: formData.get('half_day_threshold_hours') || 4,
        
        // Flexible schedule
        flexible_schedule: formData.get('flexible_schedule') === '1',
        custom_schedule: JSON.parse(formData.get('custom_schedule_json') || '{}'),
        
        // Working days
        working_days: Array.from(formData.getAll('working_days[]')).map(Number),
        
        // Advanced settings
        auto_calculate_hours: formData.get('auto_calculate_hours') === '1',
        enable_overtime: formData.get('enable_overtime') === '1',
        overtime_rate: formData.get('enable_overtime') === '1' ? formData.get('overtime_rate') : null,
        enable_early_departure: formData.get('enable_early_departure') === '1',
        early_departure_minutes: formData.get('enable_early_departure') === '1' ? formData.get('early_departure_minutes') : null,
        
        _token: '{{ csrf_token() }}'
    };
    
    console.log('Saving settings:', data);
    
    fetch('/attendance/settings/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Save response:', data);
        if (data.success) {
            alert('✅ Settings saved successfully!');
            // Reload the settings to show updated values
            loadEmployeeSettings(employeeId);
        } else {
            alert('❌ Error: ' + (data.message || 'Unknown error saving settings'));
        }
    })
    .catch(error => {
        console.error('Error saving settings:', error);
        alert('❌ Failed to save settings. Please try again.');
    });
}

// Set default settings
function setDefaultSettings() {
    // Basic settings
    document.getElementById('startTime').value = '09:00';
    document.getElementById('endTime').value = '17:00';
    document.getElementById('lateThreshold').value = 15;
    document.getElementById('halfDayThreshold').value = 4;
    
    // default is day‑wise; nothing to toggle or initialize for periods

    
    // Working days
    document.querySelectorAll('.working-day-checkbox').forEach(checkbox => {
        const value = parseInt(checkbox.value);
        checkbox.checked = [1, 2, 3, 4, 5].includes(value);
    });
    
    // Flexible schedule
    document.getElementById('flexibleSchedule').checked = false;
    toggleFlexibleSchedule();
    
    // Advanced settings
    document.getElementById('autoCalculateHours').checked = true;
    document.getElementById('enableOvertime').checked = false;
    document.getElementById('overtimeRate').value = '';
    document.getElementById('enableEarlyDeparture').checked = false;
    document.getElementById('earlyDepartureMinutes').value = 30;
    
    toggleOvertimeRate();
    toggleEarlyDeparture();
    
    // no period schedule to generate

}

// Reset settings form
function resetSettingsForm() {
    if (confirm('Reset all settings for this employee to defaults?')) {
        setDefaultSettings();
    }
}

// Save bulk settings
function saveBulkSettings() {
    const departmentId = document.getElementById('bulkDepartment').value;
    if (!departmentId) {
        alert('Please select a department');
        return;
    }
    
    const data = {
        department_id: departmentId,
        start_time: document.querySelector('#departmentSettingsForm input[name="start_time"]').value || null,
        end_time: document.querySelector('#departmentSettingsForm input[name="end_time"]').value || null,
        _token: '{{ csrf_token() }}'
    };
    
    console.log('Saving bulk settings:', data);
    
    fetch('/attendance/settings/bulk-update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Bulk save response:', data);
        if (data.success) {
            alert(`✅ ${data.message}`);
            resetBulkSettings();
        } else {
            alert('❌ Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Failed to save bulk settings');
    });
}

// General settings functions
function saveGeneralSettings() {
    // This is a demo function
    alert('✅ General settings saved successfully! (Demo feature)');
}

function resetGeneralSettings() {
    if (confirm('Reset all general settings to defaults?')) {
        document.getElementById('generalSettingsForm').reset();
        // Set default values
        document.querySelector('#generalSettingsForm input[name="public_holidays"]').value = 12;
        document.querySelector('#generalSettingsForm input[name="carry_forward_days"]').value = 5;
        document.querySelector('#generalSettingsForm input[name="report_day"]').value = 5;
        document.querySelector('#generalSettingsForm input[name="auto_calculate_ot"]').checked = true;
        document.querySelector('#generalSettingsForm input[name="send_monthly_report"]').checked = true;
        document.querySelector('#generalSettingsForm input[name="enable_weekly_off"]').checked = true;
    }
}

function resetBulkSettings() {
    document.getElementById('departmentSettingsForm').reset();
    document.getElementById('bulkDepartment').value = '';
    document.querySelector('#departmentSettingsForm input[name="start_time"]').value = '09:00';
    document.querySelector('#departmentSettingsForm input[name="end_time"]').value = '17:00';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Attendance settings page loaded');
    // Initialize toggle sections
    toggleOvertimePeriods();
    toggleOvertimeRate();
    toggleEarlyDeparture();
});
</script>
@endpush

<style>
.settings-section {
    display: block;
}
.hidden {
    display: none;
}
.period-only {
    display: none;
}
</style>
@endsection