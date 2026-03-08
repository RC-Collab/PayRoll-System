@extends('layouts.app')

@section('title', 'Create New Department')

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

<!-- Page Header -->
<div class="mb-8">
    <h2 class="text-3xl font-bold text-slate-800 dark:text-slate-200">Create New Department</h2>
    <p class="text-slate-500 dark:text-slate-400 mt-2">Add a new department to your organization</p>
</div>

<!-- Create Form -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow p-6">
    <form action="{{ route('departments.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Department Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Department Name *
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       required
                       class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Department Code -->
            <div>
                <label for="code" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Department Code *
                </label>
                <input type="text" 
                       id="code" 
                       name="code" 
                       value="{{ old('code') }}"
                       required
                       placeholder="e.g., ACAD, HR, IT"
                       class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <!-- Description -->
        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Description
            </label>
            <textarea id="description" 
                      name="description" 
                      rows="3"
                      class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Category -->
            <div>
                <label for="category" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Category *
                </label>
                <select id="category" 
                        name="category" 
                        required
                        class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Category</option>
                    <option value="academic" {{ old('category') == 'academic' ? 'selected' : '' }}>Academic</option>
                    <option value="administrative" {{ old('category') == 'administrative' ? 'selected' : '' }}>Administrative</option>
                    <option value="support" {{ old('category') == 'support' ? 'selected' : '' }}>Support Services</option>
                    <option value="technical" {{ old('category') == 'technical' ? 'selected' : '' }}>Technical</option>
                    <option value="operations" {{ old('category') == 'operations' ? 'selected' : '' }}>Operations</option>
                </select>
                @error('category')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Head of Department -->
            <div>
                <label for="head_of_department_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Head of Department (Optional)
                </label>
                <select id="head_of_department_id" 
                        name="head_of_department_id"
                        class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Head of Department</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('head_of_department_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->full_name }} - {{ $employee->designation }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-slate-500">If selected, employee will be automatically added to department as HOD</p>
            </div>
        </div>
        
        <!-- Icon -->
        <div class="mb-6">
            <label for="icon" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Icon (Optional)
            </label>
            <div class="flex items-center gap-3">
                <input type="text" 
                       id="icon" 
                       name="icon" 
                       value="{{ old('icon') }}"
                       placeholder="Enter emoji or leave empty"
                       class="flex-1 px-4 py-3 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <div class="text-sm text-slate-500">
                    <div class="flex gap-2">
                        <button type="button" onclick="document.getElementById('icon').value = '🏢'" class="text-2xl hover:scale-125 transition">🏢</button>
                        <button type="button" onclick="document.getElementById('icon').value = '🎓'" class="text-2xl hover:scale-125 transition">🎓</button>
                        <button type="button" onclick="document.getElementById('icon').value = '👥'" class="text-2xl hover:scale-125 transition">👥</button>
                        <button type="button" onclick="document.getElementById('icon').value = '💰'" class="text-2xl hover:scale-125 transition">💰</button>
                        <button type="button" onclick="document.getElementById('icon').value = '💻'" class="text-2xl hover:scale-125 transition">💻</button>
                    </div>
                </div>
            </div>
            <p class="mt-1 text-sm text-slate-500">Select an emoji or type your own</p>
        </div>
        
        <!-- Roles -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Department Roles (Optional)
            </label>
            <div class="border border-slate-300 dark:border-slate-600 rounded-lg p-4">
                <div class="mb-3">
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">Add common roles for this department:</p>
                    <div class="flex flex-wrap gap-2 mb-3">
                        <button type="button" onclick="addCommonRole('Manager')" 
                                class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-sm hover:bg-blue-200">
                            + Manager
                        </button>
                        <button type="button" onclick="addCommonRole('Supervisor')" 
                                class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-sm hover:bg-blue-200">
                            + Supervisor
                        </button>
                        <button type="button" onclick="addCommonRole('Senior')" 
                                class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-sm hover:bg-blue-200">
                            + Senior
                        </button>
                        <button type="button" onclick="addCommonRole('Junior')" 
                                class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-sm hover:bg-blue-200">
                            + Junior
                        </button>
                        <button type="button" onclick="addCommonRole('Assistant')" 
                                class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-sm hover:bg-blue-200">
                            + Assistant
                        </button>
                    </div>
                </div>
                
                <div id="rolesContainer" class="flex flex-wrap gap-2 mb-3">
                    <!-- Roles will be added here -->
                </div>
                
                <div class="flex gap-2">
                    <input type="text" 
                           id="roleInput" 
                           placeholder="Enter custom role name"
                           class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded">
                    <button type="button" 
                            onclick="addRole()"
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg">
                        Add Role
                    </button>
                </div>
                
                <input type="hidden" id="roles" name="roles[]">
            </div>
        </div>
        
        <!-- Status -->
        <div class="mb-6">
            <label class="flex items-center gap-3">
                <input type="checkbox" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', true) ? 'checked' : '' }}
                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    Active Department
                </span>
            </label>
        </div>
        
        <!-- Submit Buttons -->
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700">
            <a href="{{ route('departments.index') }}" 
               class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700">
                Cancel
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg">
                Create Department
            </button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
let roles = [];

function addCommonRole(role) {
    if (!roles.includes(role)) {
        roles.push(role);
        renderRoles();
        updateHiddenInput();
    }
}

function addRole() {
    const input = document.getElementById('roleInput');
    const role = input.value.trim();
    
    if (role && !roles.includes(role)) {
        roles.push(role);
        renderRoles();
        updateHiddenInput();
        input.value = '';
    }
}

function removeRole(index) {
    roles.splice(index, 1);
    renderRoles();
    updateHiddenInput();
}

function renderRoles() {
    const container = document.getElementById('rolesContainer');
    if (roles.length === 0) {
        container.innerHTML = '<p class="text-slate-400 text-sm">No roles added yet</p>';
        return;
    }
    
    container.innerHTML = roles.map((role, index) => `
        <span class="inline-flex items-center gap-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full text-sm">
            ${role}
            <button type="button" onclick="removeRole(${index})" class="text-blue-500 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </span>
    `).join('');
}

function updateHiddenInput() {
    // Create multiple hidden inputs for each role
    const hiddenContainer = document.getElementById('roles');
    hiddenContainer.innerHTML = '';
    
    roles.forEach(role => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'roles[]';
        input.value = role;
        hiddenContainer.appendChild(input);
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    renderRoles();
});
</script>
@endsection