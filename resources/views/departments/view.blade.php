@extends('layouts.app')

@section('title', $department->name . ' - Department Details')

@section('content')

<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('departments.index') }}" 
       class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-300">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Departments
    </a>
</div>

<!-- Messages -->
@if(session('success'))
<div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg text-emerald-700 dark:text-emerald-400">
    {{ session('success') }}
</div>
@endif

<!-- Department Header -->
<div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-8 text-white mb-8 shadow-lg">
    <div class="flex items-start justify-between">
        <div>
            <div class="flex items-center gap-4 mb-4">
                <span class="text-4xl">{{ $department->icon ?? '🏢' }}</span>
                <div>
                    <h1 class="text-3xl font-bold">{{ $department->name }}</h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="px-3 py-1 bg-white/20 rounded-full text-sm">{{ $department->code }}</span>
                        <span class="px-3 py-1 bg-white/20 rounded-full text-sm capitalize">{{ $department->category }}</span>
                        <span class="px-3 py-1 bg-white/20 rounded-full text-sm">{{ $department->employees->count() }} employees</span>
                        @if($department->is_active)
                            <span class="px-3 py-1 bg-green-500/30 rounded-full text-sm">Active</span>
                        @else
                            <span class="px-3 py-1 bg-red-500/30 rounded-full text-sm">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
            <p class="text-lg opacity-90 max-w-3xl">{{ $department->description ?? 'No description provided.' }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('departments.manage', $department->id) }}" 
               class="px-4 py-2 bg-white text-blue-600 hover:bg-blue-50 rounded-lg font-semibold transition-colors">
                Manage Staff
            </a>
            <a href="{{ route('departments.edit', $department->id) }}" 
               class="px-4 py-2 border border-white/30 text-white hover:bg-white/10 rounded-lg transition-colors">
                Edit Department
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column: Employees -->
    <div class="lg:col-span-2">
        <!-- Employees Section -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200">Employees in this Department</h2>
                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-full text-sm">
                    {{ $employees->count() }} employees
                </span>
            </div>
            
            @if($employees->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($employees as $employee)
                <div class="p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-750">
                    <div class="flex items-center gap-3">
                        @if($employee->profile_image)
                            <img src="{{ asset('storage/' . $employee->profile_image) }}" 
                                 alt="{{ $employee->full_name }}"
                                 class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                            </div>
                        @endif
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-800 dark:text-slate-200">{{ $employee->full_name }}</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded text-xs">
                                    {{ $employee->pivot->role ?? 'Member' }}
                                </span>
                                <span class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded text-xs">
                                    {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">{{ $employee->email }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $employee->designation }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <div class="mx-auto w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">No Employees Found</h3>
                <p class="text-slate-500 dark:text-slate-400 mb-4">No employees are currently assigned to this department</p>
                <a href="{{ route('departments.manage', $department->id) }}" 
                   class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition-colors">
                    Add Staff
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Right Column: Department Info -->
    <div>
        <!-- Department Head -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-4">Head of Department</h2>
            @if($department->head_of_department)
                <!-- Find HOD employee -->
                @php
                    $hodEmployee = $employees->first(function($emp) use ($department) {
                        return str_contains(strtolower($emp->full_name), strtolower($department->head_of_department)) ||
                               $emp->pivot->role === 'Head of Department';
                    });
                @endphp
                
                <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-700 rounded-xl">
                    @if($hodEmployee && $hodEmployee->profile_image)
                        <img src="{{ asset('storage/' . $hodEmployee->profile_image) }}" 
                             alt="{{ $department->head_of_department }}"
                             class="w-16 h-16 rounded-full object-cover">
                    @else
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xl font-bold">
                            {{ substr($department->head_of_department, 0, 2) }}
                        </div>
                    @endif
                    <div>
                        <h3 class="font-semibold text-slate-800 dark:text-slate-200">{{ $department->head_of_department }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Head of Department</p>
                        @if($hodEmployee)
                            <p class="text-sm text-blue-600 dark:text-blue-400 mt-2">{{ $hodEmployee->email }}</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-slate-500 dark:text-slate-400">No Head of Department assigned</p>
                    <a href="{{ route('departments.edit', $department->id) }}" 
                       class="text-sm text-blue-600 dark:text-blue-400 hover:underline mt-2 inline-block">
                        Assign HOD
                    </a>
                </div>
            @endif
        </div>

        <!-- Department Roles -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200">Available Roles</h2>
                <a href="{{ route('departments.manage', $department->id) }}" 
                   class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    Add Role
                </a>
            </div>
            
            @if(is_array($department->roles) && count($department->roles) > 0)
            <div class="space-y-3">
                @foreach($department->roles as $role)
                @php
                    $assignedCount = $employees->filter(function($emp) use ($role) {
                        return $emp->pivot->role === $role;
                    })->count();
                @endphp
                <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $role }}</span>
                    <span class="px-2 py-1 bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-400 rounded text-xs">
                        {{ $assignedCount }} assigned
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-4">
                <p class="text-slate-500 dark:text-slate-400">No roles defined for this department</p>
                <a href="{{ route('departments.edit', $department->id) }}" 
                   class="text-sm text-blue-600 dark:text-blue-400 hover:underline mt-2 inline-block">
                    Add Roles
                </a>
            </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow p-6">
            <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="{{ route('departments.manage', $department->id) }}" 
                   class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 rounded-lg transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-slate-800 dark:text-slate-200">Manage Staff</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Add/remove employees</p>
                    </div>
                </a>
                
                <a href="{{ route('departments.edit', $department->id) }}" 
                   class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 rounded-lg transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-slate-800 dark:text-slate-200">Edit Department</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Update department details</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Department Section -->
@if($employees->count() == 0)
<div class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-700">
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-2">Danger Zone</h3>
        <p class="text-red-600 dark:text-red-400 mb-4">Once you delete a department, there is no going back. Please be certain.</p>
        <form action="{{ route('departments.destroy', $department->id) }}" method="POST" 
              onsubmit="return confirm('Are you absolutely sure you want to delete this department? This action cannot be undone.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg">
                Delete Department
            </button>
        </form>
    </div>
</div>
@endif

@endsection