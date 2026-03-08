@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
                <li class="breadcrumb-item"><a href="{{ route('employees.show', $employee->id) }}">{{ $employee->full_name }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-user-edit me-2"></i>
                Edit Employee Details
            </h4>
            <p class="mb-0 mt-1 small">Update only the fields you need to change</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employees.update', $employee->id) }}" enctype="multipart/form-data" id="employeeForm">
                @csrf
                @method('PUT')

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" id="employeeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" 
                                data-bs-target="#basic" type="button">Basic Info</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="personal-tab" data-bs-toggle="tab" 
                                data-bs-target="#personal" type="button">Personal Details</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="employment-tab" data-bs-toggle="tab" 
                                data-bs-target="#employment" type="button">Employment</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bank-tab" data-bs-toggle="tab" 
                                data-bs-target="#bank" type="button">Bank Details</button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="employeeTabsContent">
                    
                    <!-- Tab 1: Basic Information -->
                    <div class="tab-pane fade show active" id="basic" role="tabpanel">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Leave fields as they are if you don't want to change them
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Employee Code</label>
                                <input type="text" name="employee_code" class="form-control" 
                                       value="{{ old('employee_code', $employee->employee_code) }}">
                                @error('employee_code')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" 
                                       value="{{ old('first_name', $employee->first_name) }}">
                                @error('first_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Middle Name <small class="text-muted">(optional)</small></label>
                                <input type="text" name="middle_name" class="form-control" 
                                       value="{{ old('middle_name', $employee->middle_name) }}">
                                @error('middle_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" 
                                       value="{{ old('last_name', $employee->last_name) }}">
                                @error('last_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" 
                                       value="{{ old('email', $employee->email) }}">
                                @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Mobile Number</label>
                                  <input type="tel" name="mobile_number" class="form-control" pattern="[0-9]{10}" maxlength="10" value="{{ old('mobile_number', $employee->mobile_number) }}">

                                @error('mobile_number')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Departments</label>
                                <div class="row">
                                    @foreach($departments as $department)
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="departments[]" 
                                                   value="{{ $department->id }}" 
                                                   id="dept_{{ $department->id }}"
                                                   {{ in_array($department->id, old('departments', $employee->departments->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="dept_{{ $department->id }}">
                                                {{ $department->name }}
                                            </label>
                                        </div>
                                        <div class="ms-4 mt-1">
                                            <input type="text" name="department_roles[{{ $department->id }}]" 
                                                   class="form-control form-control-sm" 
                                                   placeholder="Role in {{ $department->name }}"
                                                   value="{{ old("department_roles.{$department->id}", $employee->departments->find($department->id)->pivot->role ?? '') }}">
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Personal Details -->
                    <div class="tab-pane fade" id="personal" role="tabpanel">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Update only the fields that need to be changed
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $employee->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" 
                                       value="{{ old('date_of_birth', $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : '') }}">
                                @error('date_of_birth')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Marital Status</label>
                                <select name="marital_status" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="single" {{ old('marital_status', $employee->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                                    <option value="married" {{ old('marital_status', $employee->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                                    <option value="divorced" {{ old('marital_status', $employee->marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                    <option value="widowed" {{ old('marital_status', $employee->marital_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Blood Group</label>
                                <select name="blood_group" class="form-control">
                                    <option value="">Select Blood Group</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group)
                                        <option value="{{ $group }}" {{ old('blood_group', $employee->blood_group) == $group ? 'selected' : '' }}>
                                            {{ $group }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Nepal Specific Fields -->
                            <div class="col-md-4">
                                <label class="form-label">Citizenship Number</label>
                                <input type="text" name="citizenship_number" class="form-control" 
                                       value="{{ old('citizenship_number', $employee->citizenship_number) }}">
                                @error('citizenship_number')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Citizenship Issue Date</label>
                                <input type="date" name="citizenship_issue_date" class="form-control" 
                                       value="{{ old('citizenship_issue_date', $employee->citizenship_issue_date ? $employee->citizenship_issue_date->format('Y-m-d') : '') }}">
                            </div>
                            
                            <div class="col-md-4">
<select name="citizenship_issued_district" class="form-control">
    <option value="">Select District</option>
    @foreach(config('nepal.districts') as $d)
        <option value="{{ $d }}" {{ old('citizenship_issued_district', $employee->citizenship_issued_district) == $d ? 'selected' : '' }}>{{ $d }}</option>
    @endforeach
</select>
</select>citizenship_issued_district) }}">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">PAN Number</label>
                                <input type="text" name="pan_number" class="form-control" 
                                       value="{{ old('pan_number', $employee->pan_number) }}">
                            </div>
                            
                            <!-- Address -->
                            <div class="col-md-6">
                                <label class="form-label">Current Address</label>
                                <textarea name="current_address" class="form-control" rows="2">{{ old('current_address', $employee->current_address) }}</textarea>
                                @error('current_address')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Permanent Address</label>
                                <textarea name="permanent_address" class="form-control" rows="2">{{ old('permanent_address', $employee->permanent_address) }}</textarea>
                                @error('permanent_address')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">District</label>
    <select name="district" class="form-control">
        <option value="">Select District</option>
        @foreach(config('nepal.districts') as $d)
            <option value="{{ $d }}" {{ old('district', $employee->district) == $d ? 'selected' : '' }}>{{ $d }}</option>
        @endforeach
    </select>
                                       value="{{ old('district', $employee->district) }}">
                                @error('district')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Municipality</label>
                                <input type="text" name="municipality" class="form-control" 
                                       value="{{ old('municipality', $employee->municipality) }}">
                                @error('municipality')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Ward Number</label>
                                <input type="text" name="ward_number" class="form-control" 
                                       value="{{ old('ward_number', $employee->ward_number) }}">
                            </div>
                            
                            <!-- Emergency Contact -->
                            <div class="col-md-4">
                                <label class="form-label">Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" class="form-control" 
                                       value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}">
                                @error('emergency_contact_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Emergency Contact Phone</label>
                                <input type="tel" name="emergency_contact_phone" class="form-control" pattern="[0-9]{10}" maxlength="10" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}">
                                @error('emergency_contact_phone')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Relation</label>
                                <input type="text" name="emergency_contact_relation" class="form-control" 
                                       value="{{ old('emergency_contact_relation', $employee->emergency_contact_relation) }}">
                                @error('emergency_contact_relation')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Employment Details -->
                    <div class="tab-pane fade" id="employment" role="tabpanel">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Update employment information as needed
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Joining Date</label>
                                <input type="date" name="joining_date" class="form-control" 
                                       value="{{ old('joining_date', $employee->joining_date ? $employee->joining_date->format('Y-m-d') : '') }}">
                                @error('joining_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Employee Type</label>
                                <select name="employee_type" class="form-control">
                                    <option value="">Select Type</option>
                                    @foreach(['permanent', 'contract', 'temporary', 'probation', 'part-time'] as $type)
                                        <option value="{{ $type }}" {{ old('employee_type', $employee->employee_type) == $type ? 'selected' : '' }}>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Employment Status</label>
                                <select name="employment_status" class="form-control" id="employment_status_field_edit" onchange="togglePasswordFieldEdit()">
                                    <option value="">Select Status</option>
                                    @foreach(['active', 'inactive', 'on-leave', 'suspended', 'terminated'] as $status)
                                        <option value="{{ $status }}" {{ old('employment_status', $employee->employment_status) == $status ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('-', ' ', $status)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if(auth()->user()->isAdmin())
                            <div class="col-md-4">
                                <label class="form-label">User Role <small class="text-muted">(dashboard access)</small></label>
                                <select name="role" class="form-control">
                                    @foreach(['employee','hr','accountant','admin','manager'] as $r)
                                        <option value="{{ $r }}" {{ old('role', $employee->user->role ?? 'employee') == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <!-- Password Field (Only for Active Employees without an account) -->
                            @if(!$employee->user)
                            <div class="col-md-4" id="password_field_container_edit" style="display: none;">
                                <label class="form-label required" for="password">New Password <small class="text-muted">(optional to keep current)</small></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control" 
                                           placeholder="Enter new password (leave blank to keep current)" minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibilityEdit()">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Min 8 characters</small>
                            </div>

                            <!-- Password Confirmation Field (Only for Active Employees) -->
                            <div class="col-md-4" id="password_confirm_field_container_edit" style="display: none;">
                                <label class="form-label required" for="password_confirmation">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                                           placeholder="Confirm password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordConfirmVisibilityEdit()">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            @endif
                            
                            <div class="col-md-4">
                                <label class="form-label">Contract End Date <small class="text-muted">(optional)</small></label>
                                <input type="date" name="contract_end_date" class="form-control" 
                                       value="{{ old('contract_end_date', $employee->contract_end_date ? $employee->contract_end_date->format('Y-m-d') : '') }}">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Designation</label>
                                <input type="text" name="designation" class="form-control" 
                                       value="{{ old('designation', $employee->designation) }}">
                                @error('designation')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Experience (Years)</label>
                                <input type="number" name="experience_years" class="form-control" min="0" 
                                       value="{{ old('experience_years', $employee->experience_years) }}">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Basic Salary</label>
                                <input type="number" name="basic_salary" class="form-control" min="0" step="0.01"
                                       value="{{ old('basic_salary', $employee->salaryStructure->basic_salary ?? '') }}">
                                @error('basic_salary')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">OT Rate (per hr)</label>
                                <input type="number" name="overtime_rate" class="form-control" min="0" step="0.01"
                                       value="{{ old('overtime_rate', $employee->salaryStructure->overtime_rate ?? '') }}">
                                @error('overtime_rate')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Qualification</label>
                                <input type="text" name="qualification" class="form-control" 
                                       value="{{ old('qualification', $employee->qualification) }}">
                                @error('qualification')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Institution Name</label>
                                <input type="text" name="institution_name" class="form-control" 
                                       value="{{ old('institution_name', $employee->institution_name) }}">
                                @error('institution_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Tab 4: Bank Details -->
                    <div class="tab-pane fade" id="bank" role="tabpanel">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Update bank information as needed
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Alternate Phone</label>
                                <input type="tel" name="alternate_phone" class="form-control" pattern="[0-9]{10}" maxlength="10" value="{{ old('alternate_phone', $employee->alternate_phone) }}">
                                @error('alternate_phone')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Bank Name</label>
                                <select name="bank_name" class="form-control">
                                    <option value="">Select Bank</option>
                                    @php
                                        $nepaliBanks = [
                                            'Nabil Bank', 'NIC Asia Bank', 'Standard Chartered Bank',
                                            'Himalayan Bank', 'Global IME Bank', 'Prabhu Bank',
                                            'Machhapuchchhre Bank', 'Sunrise Bank', 'NMB Bank',
                                            'Kumari Bank', 'Sanima Bank', 'Prime Bank', 'Civil Bank',
                                            'Century Bank', 'Agriculture Development Bank',
                                            'Nepal Bangladesh Bank', 'Nepal Investment Bank',
                                            'Everest Bank', 'Laxmi Bank', 'Siddhartha Bank'
                                        ];
                                    @endphp
                                    @foreach($nepaliBanks as $bank)
                                        <option value="{{ $bank }}" {{ old('bank_name', $employee->bank_name) == $bank ? 'selected' : '' }}>
                                            {{ $bank }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Account Number</label>
                                <input type="text" name="account_number" class="form-control" 
                                       value="{{ old('account_number', $employee->account_number) }}">
                                @error('account_number')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Account Holder Name</label>
                                <input type="text" name="account_holder_name" class="form-control" 
                                       value="{{ old('account_holder_name', $employee->account_holder_name) }}">
                                @error('account_holder_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Branch Name</label>
                                <input type="text" name="branch_name" class="form-control" 
                                       value="{{ old('branch_name', $employee->branch_name) }}">
                                @error('branch_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Profile Image</label>
                                <input type="file" name="profile_image" class="form-control" accept="image/*">
                                <small class="text-muted">Max: 2MB. Allowed: jpg, jpeg, png, gif</small>
                                @if($employee->profile_image)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $employee->profile_image) }}" 
                                             alt="Profile" class="img-thumbnail" width="100">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="remove_profile_image" value="1" id="remove_profile_image">
                                            <label class="form-check-label" for="remove_profile_image">
                                                Remove current image
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $employee->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                       id="is_active" {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active Employee
                                </label>
                            </div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary me-2" onclick="resetForm()">
                                <i class="fas fa-redo me-2"></i>Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Employee
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.nav-tabs .nav-link {
    font-weight: 500;
}
.nav-tabs .nav-link.active {
    font-weight: 600;
}
.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}
.alert-info {
    background-color: #f0f9ff;
    border-color: #b6e0fe;
    color: #05516e;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tabs
    const triggerTabList = document.querySelectorAll('#employeeTabs button')
    triggerTabList.forEach(triggerEl => {
        const tabTrigger = new bootstrap.Tab(triggerEl)
        triggerEl.addEventListener('click', event => {
            event.preventDefault()
            tabTrigger.show()
        })
    });

    // Store original form values for reset functionality
    const form = document.getElementById('employeeForm');
    const originalFormData = new FormData(form);
});

// Reset form to original values
function resetForm() {
    if (confirm('Are you sure you want to reset all changes? This will restore all fields to their original values.')) {
        location.reload();
    }
}

// Toggle password fields based on employment status (edit view)
function togglePasswordFieldEdit() {
    const status = document.getElementById('employment_status_field_edit')?.value;
    const passwordContainer = document.getElementById('password_field_container_edit');
    const passwordConfirmContainer = document.getElementById('password_confirm_field_container_edit');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    
    if (status === 'active' && passwordContainer) {
        passwordContainer.style.display = 'block';
        passwordConfirmContainer.style.display = 'block';
    } else if (passwordContainer) {
        passwordContainer.style.display = 'none';
        passwordConfirmContainer.style.display = 'none';
        if (passwordInput) passwordInput.value = '';
        if (passwordConfirmInput) passwordConfirmInput.value = '';
    }
}

// Toggle password visibility (edit view)
function togglePasswordVisibilityEdit() {
    const passwordInput = document.getElementById('password');
    const button = event.target.closest('button');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        button.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        passwordInput.type = 'password';
        button.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

// Toggle password confirmation visibility (edit view)
function togglePasswordConfirmVisibilityEdit() {
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const button = event.target.closest('button');
    
    if (passwordConfirmInput.type === 'password') {
        passwordConfirmInput.type = 'text';
        button.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        passwordConfirmInput.type = 'password';
        button.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

// Optional: Add confirmation for form submission
document.getElementById('employeeForm').addEventListener('submit', function(e) {
    // Check password match if passwords are filled
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirmation');
    const status = document.getElementById('employment_status_field_edit')?.value;
    
    if (status === 'active' && password && passwordConfirm) {
        if (password.value && password.value !== passwordConfirm.value) {
            e.preventDefault();
            alert('⚠️ Passwords do not match. Please check and try again.');
            return false;
        }
    }

    // Check if any field has been changed
    const currentFormData = new FormData(this);
    let hasChanges = false;
    
    // Compare current values with original values
    // This is a simplified check - you might want to implement more thorough checking
    for (let [key, value] of currentFormData.entries()) {
        // Skip file inputs and certain fields
        if (key !== '_token' && key !== '_method' && key !== 'profile_image') {
            if (typeof value === 'string' && value.trim() !== '') {
                hasChanges = true;
                break;
            }
        }
    }
    
    if (!hasChanges) {
        e.preventDefault();
        if (confirm('No changes detected. Do you still want to submit?')) {
            e.target.submit();
        }
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePasswordFieldEdit();
});
</script>
@endpush
@endsection
