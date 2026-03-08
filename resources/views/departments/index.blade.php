@extends('layouts.app')

@section('title', 'Department Management')

@section('content')

<!-- Page Header -->
<div class="mb-8">
    <h2 class="text-3xl font-bold text-slate-800 dark:text-slate-200">Department Management 🏢</h2>
    <p class="text-slate-500 dark:text-slate-400 mt-2">Manage all departments, roles, and employee assignments</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg">
        <p class="text-sm opacity-90">Total Departments</p>
        <h3 class="text-4xl font-bold mt-2">{{ $totalDepartments }}</h3>
        <div class="flex items-center justify-between mt-4">
            <span class="text-sm opacity-80">Active</span>
            <span class="text-sm bg-white/20 px-2 py-1 rounded-full">{{ $departments->where('is_active', true)->count() }}</span>
        </div>
    </div>
    
    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg">
        <p class="text-sm opacity-90">Total Employees</p>
        <h3 class="text-4xl font-bold mt-2">{{ $totalEmployees }}</h3>
        <div class="flex items-center justify-between mt-4">
            <span class="text-sm opacity-80">By Department</span>
            <span class="text-sm bg-white/20 px-2 py-1 rounded-full">View</span>
        </div>
    </div>
    
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg">
        <p class="text-sm opacity-90">Roles Available</p>
        <h3 class="text-4xl font-bold mt-2">{{ $totalRoles }}</h3>
        <div class="flex items-center justify-between mt-4">
            <span class="text-sm opacity-80">Across all depts</span>
            <a href="#add-department" class="text-sm bg-white/20 px-2 py-1 rounded-full hover:bg-white/30">+ Add</a>
        </div>
    </div>
    
    <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-lg">
        <p class="text-sm opacity-90">HOD Positions</p>
        <h3 class="text-4xl font-bold mt-2">{{ $hodPositions }}</h3>
        <div class="flex items-center justify-between mt-4">
            <span class="text-sm opacity-80">Filled</span>
            <span class="text-sm bg-white/20 px-2 py-1 rounded-full">{{ $hodPositions }}/{{ $totalDepartments }}</span>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="flex flex-wrap gap-3 mb-6">
    <a href="{{ route('departments.create') }}" 
       class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition-all flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add New Department
    </a>
    
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="w-full p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg text-emerald-700 dark:text-emerald-400">
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="w-full p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400">
        {{ session('error') }}
    </div>
    @endif
</div>

<!-- Departments Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($departments as $department)
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-2xl">{{ $department->icon ?? '🏢' }}</span>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200">{{ $department->name }}</h3>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 px-2 py-1 rounded">
                                    {{ $department->code }}
                                </span>
                                <span class="text-xs px-2 py-1 rounded {{ getCategoryClass($department->category) }}">
                                    {{ ucfirst($department->category) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $department->description }}</p>
                </div>
                <div class="relative group">
                    <button class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-xl z-10 hidden group-hover:block">
                        <a href="{{ route('departments.edit', $department->id) }}" 
                           class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                            Edit Department
                        </a>
                        <a href="{{ route('departments.manage', $department->id) }}" 
                           class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                            Manage Staff
                        </a>
                        <form action="{{ route('departments.destroy', $department->id) }}" method="POST" class="border-t border-slate-200 dark:border-slate-700">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    onclick="return confirm('Are you sure you want to delete this department?')"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30">
                                Delete Department
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Head of Department</span>
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                        {{ $department->head_of_department ?? 'Not Assigned' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Employees</span>
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                        {{ $department->employees_count }}
                    </span>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Available Roles</span>
                    <span class="text-xs text-slate-500">
                        {{ is_array($department->roles) ? count($department->roles) : 0 }} roles
                    </span>
                </div>
                <div class="flex flex-wrap gap-1">
                    @if(is_array($department->roles) && count($department->roles) > 0)
                        @foreach(array_slice($department->roles, 0, 3) as $role)
                            <span class="text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 px-2 py-1 rounded">
                                {{ $role }}
                            </span>
                        @endforeach
                        @if(count($department->roles) > 3)
                            <span class="text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 px-2 py-1 rounded">
                                +{{ count($department->roles) - 3 }}
                            </span>
                        @endif
                    @else
                        <span class="text-xs text-slate-400">No roles defined</span>
                    @endif
                </div>
            </div>
            
            <div class="flex justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('departments.show', $department->id) }}" 
                   class="text-sm text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-medium">
                    View Details →
                </a>
                <a href="{{ route('departments.manage', $department->id) }}" 
                   class="px-3 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-sm">
                    Manage Staff
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Empty State -->
@if($departments->count() == 0)
<div class="p-12 text-center bg-white dark:bg-slate-800 rounded-2xl shadow">
    <div class="mx-auto w-24 h-24 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-4">
        <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
    </div>
    <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">No Departments Found</h3>
    <p class="text-slate-500 dark:text-slate-400 mb-4">Add your first department to get started</p>
    <a href="{{ route('departments.create') }}" 
       class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition-all">
        Create First Department
    </a>
</div>
@endif

@endsection

@php
// Helper function for category classes (defined in view for simplicity)
function getCategoryClass($category) {
    $classes = [
        'academic' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
        'administrative' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400',
        'support' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
        'technical' => 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400',
        'operations' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400'
    ];
    return $classes[$category] ?? 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400';
}
@endphp