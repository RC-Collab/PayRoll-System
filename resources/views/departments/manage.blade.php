@extends('layouts.app')

@section('title', 'Manage Department Staff - ' . $department->name)

@section('content')

<!-- Back Button -->
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="{{ route('departments.show', $department->id) }}" 
           class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Department
        </a>
        <div class="flex items-center gap-2">
            <span class="text-2xl">{{ $department->icon ?? '👥' }}</span>
            <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-200">{{ $department->name }}</h1>
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 rounded text-xs">
                Manage Staff
            </span>
        </div>
    </div>
</div>

<!-- Messages -->
@if(session('success'))
<div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg text-emerald-700 dark:text-emerald-400">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400">
    {{ session('error') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Current Staff -->
    <div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200">Current Staff</h2>
                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-full text-sm">
                    {{ $currentStaff->count() }} employees
                </span>
            </div>
            
            @if($currentStaff->count() > 0)
            <div class="space-y-4">
                @foreach($currentStaff as $employee)
                <div class="p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-750">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            @if($employee->profile_image)
                                <img src="{{ asset('storage/' . $employee->profile_image) }}" 
                                     alt="{{ $employee->full_name }}"
                                     class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                                    {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h3 class="font-semibold text-slate-800 dark:text-slate-200">{{ $employee->full_name }}</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $employee->designation }}</p>
                            </div>
                        </div>
                        
                        <!-- HOD Badge -->
                        @if($department->head_of_department == $employee->full_name)
                        <span class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 rounded text-xs">
                            HOD
                        </span>
                        @endif
                    </div>
                    
                    <!-- Role Selection -->
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Role:</span>
                        <form action="{{ route('departments.updateRole', [$department->id, $employee->id]) }}" 
                              method="POST" class="flex-1">
                            @csrf
                            @method('PUT')
                            <select name="role" 
                                    onchange="this.form.submit()"
                                    class="w-full px-3 py-1 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg text-sm">
                                <option value="">Select Role</option>
                                @if($departmentRoles && count($departmentRoles) > 0)
                                    @foreach($departmentRoles as $role)
                                        <option value="{{ $role }}" {{ $employee->pivot->role == $role ? 'selected' : '' }}>
                                            {{ $role }}
                                        </option>
                                    @endforeach
                                @endif
                                <option value="Head of Department" {{ $employee->pivot->role == 'Head of Department' ? 'selected' : '' }}>
                                    Head of Department
                                </option>
                                <option value="Other" {{ !in_array($employee->pivot->role, array_merge($departmentRoles ?? [], ['Head of Department'])) ? 'selected' : '' }}>
                                    Other
                                </option>
                            </select>
                            
                            @if(!in_array($employee->pivot->role, array_merge($departmentRoles ?? [], ['Head of Department'])))
                            <div class="mt-2">
                                <input type="text" 
                                       name="custom_role" 
                                       value="{{ $employee->pivot->role }}"
                                       placeholder="Enter custom role"
                                       class="w-full px-3 py-1 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg text-sm">
                                <button type="submit" class="mt-1 text-xs text-blue-600 hover:text-blue-800">
                                    Update Custom Role
                                </button>
                            </div>
                            @endif
                        </form>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-700">
                        <!-- Remove Button -->
                        <form action="{{ route('departments.removeEmployee', [$department->id, $employee->id]) }}" 
                              method="POST" 
                              onsubmit="return confirm('Remove {{ $employee->full_name }} from this department?')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-3 py-1 text-sm border border-red-300 text-red-500 hover:bg-red-50 rounded-lg"
                                    {{ $department->head_of_department == $employee->full_name ? 'disabled' : '' }}>
                                Remove
                            </button>
                        </form>
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
                <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">No Staff Members</h3>
                <p class="text-slate-500 dark:text-slate-400 mb-4">No employees in this department yet</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Right Column -->
    <div class="space-y-8">
        <!-- Available Staff -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200">Available Staff</h2>
                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-full text-sm">
                    {{ $availableStaff->count() }} available
                </span>
            </div>
            
            @if($availableStaff->count() > 0)
            <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                @foreach($availableStaff as $employee)
                <div class="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-750">
                    <div class="flex items-center gap-3">
                        @if($employee->profile_image)
                            <img src="{{ asset('storage/' . $employee->profile_image) }}" 
                                 alt="{{ $employee->full_name }}"
                                 class="w-8 h-8 rounded-full object-cover">
                        @else
                            <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-sm font-semibold">
                                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="font-medium text-slate-800 dark:text-slate-200">{{ $employee->full_name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $employee->designation }}</p>
                        </div>
                    </div>
                    
                    <!-- Add to Department Form -->
                    <form action="{{ route('departments.addEmployee', $department->id) }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                        
                        <select name="role" class="text-xs px-2 py-1 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded">
                            <option value="">Select Role</option>
                            @if($departmentRoles && count($departmentRoles) > 0)
                                @foreach($departmentRoles as $role)
                                    <option value="{{ $role }}">{{ $role }}</option>
                                @endforeach
                            @endif
                            <option value="Member">Member</option>
                        </select>
                        
                        <button type="submit" 
                                class="px-3 py-1 bg-emerald-500 hover:bg-emerald-600 text-white rounded text-sm">
                            Add
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-4">
                <p class="text-slate-500 dark:text-slate-400">No available staff found</p>
            </div>
            @endif
        </div>

        <!-- Manage Roles -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow p-6">
            <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-4">Manage Roles</h2>
            
            <!-- Current Roles -->
            <div class="mb-6">
                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Current Department Roles</h3>
                @if($departmentRoles && count($departmentRoles) > 0)
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($departmentRoles as $role)
                    <div class="flex items-center gap-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full text-sm">
                        {{ $role }}
                        <form action="{{ route('departments.removeRole', $department->id) }}" 
                              method="POST" 
                              onsubmit="return confirm('Remove role: {{ $role }}?')"
                              class="inline">
                            @csrf
                            <input type="hidden" name="role_name" value="{{ $role }}">
                            <button type="submit" class="text-blue-500 hover:text-blue-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">No roles defined yet</p>
                @endif
            </div>
            
            <!-- Add New Role Form -->
            <div>
                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Add New Role</h3>
                <form action="{{ route('departments.addRole', $department->id) }}" method="POST" class="flex gap-2">
                    @csrf
                    <input type="text" 
                           name="role_name" 
                           placeholder="Enter role name"
                           required
                           class="flex-1 px-3 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded text-sm">
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm">
                        Add
                    </button>
                </form>
                
                <!-- Quick Add Common Roles -->
                <div class="mt-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Quick add:</p>
                    <div class="flex flex-wrap gap-1">
                        @php
                            $commonRoles = ['Manager', 'Supervisor', 'Senior', 'Junior', 'Assistant', 'Lead', 'Coordinator', 'Specialist'];
                        @endphp
                        @foreach($commonRoles as $commonRole)
                        <form action="{{ route('departments.addRole', $department->id) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="role_name" value="{{ $commonRole }}">
                            <button type="submit" 
                                    class="px-2 py-1 text-xs bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded">
                                + {{ $commonRole }}
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection