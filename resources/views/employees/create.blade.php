@extends('layouts.app')

@section('title', 'Add New Employee')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
                <li class="breadcrumb-item active">Add New Employee</li>
            </ol>
        </nav>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-user-plus me-2"></i>
                Add New Employee
            </h4>
            <p class="mb-0 small mt-1">Fill in basic information. Other details can be added later.</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" id="employeeForm">
                @csrf

                <!-- Error Alert -->
                @if($errors->any())
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-circle me-2"></i>Please fix the following errors:</h6>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Progress Steps -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-center">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                1
                            </div>
                            <div class="mt-1 small">Basic Info</div>
                        </div>
                        <div class="flex-grow-1 mx-2">
                            <hr class="border-primary">
                        </div>
                        <div class="text-center">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                2
                            </div>
                            <div class="mt-1 small">Employment</div>
                        </div>
                        <div class="flex-grow-1 mx-2">
                            <hr class="border-secondary">
                        </div>
                        <div class="text-center">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                3
                            </div>
                            <div class="mt-1 small">Bank Details</div>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Basic Information -->
                <div class="step-section mb-4" id="step1">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-user me-2"></i>Basic Information
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">Employee Code</label>
                            <input type="text" name="employee_code" class="form-control" 
                                   value="{{ old('employee_code', $employeeCode) }}" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">First Name</label>
                            <input type="text" name="first_name" class="form-control" 
                                   value="{{ old('first_name') }}" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Middle Name <small class="text-muted">(optional)</small></label>
                            <input type="text" name="middle_name" class="form-control" 
                                   value="{{ old('middle_name') }}">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Last Name</label>
                            <input type="text" name="last_name" class="form-control" 
                                   value="{{ old('last_name') }}" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Mobile Number</label>
                            <input type="tel" name="mobile_number" class="form-control" pattern="[0-9]{10}" maxlength="10" value="{{ old('mobile_number') }}" required>
                            @error('mobile_number')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Gender</label>
                            <select name="gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth <small class="text-muted">(optional)</small></label>
                            <input type="date" name="date_of_birth" class="form-control" 
                                   value="{{ old('date_of_birth') }}">
                        </div>
                        
                        <div class="col-md-12">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showStep(2)">
                                Next: Employment Details <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Employment Details -->
                <div class="step-section mb-4" id="step2" style="display: none;">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-briefcase me-2"></i>Employment Details
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" 
                                   value="{{ old('joining_date', date('Y-m-d')) }}" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Employee Type</label>
                            <select name="employee_type" class="form-control" required>
                                <option value="">Select Type</option>
                                <option value="permanent" {{ old('employee_type') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                                <option value="contract" {{ old('employee_type') == 'contract' ? 'selected' : '' }}>Contract</option>
                                <option value="temporary" {{ old('employee_type') == 'temporary' ? 'selected' : '' }}>Temporary</option>
                                <option value="probation" {{ old('employee_type') == 'probation' ? 'selected' : '' }}>Probation</option>
                                <option value="part-time" {{ old('employee_type') == 'part-time' ? 'selected' : '' }}>Part-time</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Employment Status</label>
                            <select name="employment_status" class="form-control" id="employment_status_field" onchange="togglePasswordField()">
                                <option value="active" {{ old('employment_status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('employment_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="on-leave" {{ old('employment_status') == 'on-leave' ? 'selected' : '' }}>On Leave</option>
                            </select>
                        </div>

                        @if(auth()->user()->isAdmin())
                        <div class="col-md-4">
                            <label class="form-label">User Role <small class="text-muted">(dashboard access)</small></label>
                            <select name="role" class="form-control">
                                <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="hr" {{ old('role') == 'hr' ? 'selected' : '' }}>HR</option>
                                <option value="accountant" {{ old('role') == 'accountant' ? 'selected' : '' }}>Accountant</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                            </select>
                        </div>
                        @endif

                        <!-- Password Field (Only for Active Employees) -->
                        <div class="col-md-4" id="password_field_container" style="display: none;">
                            <label class="form-label required" for="password">Password <small class="text-muted">(for login)</small></label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" 
                                       placeholder="Enter password" minlength="8">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Min 8 characters</small>
                        </div>

                        <!-- Password Confirmation Field (Only for Active Employees) -->
                        <div class="col-md-4" id="password_confirm_field_container" style="display: none;">
                            <label class="form-label required" for="password_confirmation">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                                       placeholder="Confirm password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordConfirmVisibility()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Designation</label>
                            <input type="text" name="designation" class="form-control" 
                                   value="{{ old('designation') }}" required placeholder="e.g., Software Developer">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Qualification <small class="text-muted">(optional)</small></label>
                            <input type="text" name="qualification" class="form-control" 
                                   value="{{ old('qualification') }}" placeholder="e.g., Bachelor in Computer Science">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Experience (Years) <small class="text-muted">(optional)</small></label>
                            <input type="number" name="experience_years" class="form-control" min="0" 
                                   value="{{ old('experience_years', 0) }}">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Basic Salary <small class="text-muted">(optional)</small></label>
                            <input type="number" name="basic_salary" class="form-control" min="0" step="0.01"
                                   value="{{ old('basic_salary') }}" placeholder="Enter basic salary">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">OT Rate (per hr) <small class="text-muted">(optional)</small></label>
                            <input type="number" name="overtime_rate" class="form-control" min="0" step="0.01"
                                   value="{{ old('overtime_rate') }}" placeholder="Enter overtime rate">
                        </div>
                        
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showStep(1)">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="showStep(3)">
                                    Next: Bank Details <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Bank Details -->
                <div class="step-section mb-4" id="step3" style="display: none;">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-university me-2"></i>Bank Details
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Bank Name</label>
                            <select name="bank_name" class="form-control" required>
                                <option value="">Select Bank</option>
                                <option value="Nabil Bank" {{ old('bank_name') == 'Nabil Bank' ? 'selected' : '' }}>Nabil Bank</option>
                                <option value="NIC Asia Bank" {{ old('bank_name') == 'NIC Asia Bank' ? 'selected' : '' }}>NIC Asia Bank</option>
                                <option value="Himalayan Bank" {{ old('bank_name') == 'Himalayan Bank' ? 'selected' : '' }}>Himalayan Bank</option>
                                <option value="Global IME Bank" {{ old('bank_name') == 'Global IME Bank' ? 'selected' : '' }}>Global IME Bank</option>
                                <option value="Prabhu Bank" {{ old('bank_name') == 'Prabhu Bank' ? 'selected' : '' }}>Prabhu Bank</option>
                                <option value="NMB Bank" {{ old('bank_name') == 'NMB Bank' ? 'selected' : '' }}>NMB Bank</option>
                                <option value="Other" {{ old('bank_name') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label required">Account Number</label>
                            <input type="text" name="account_number" class="form-control" 
                                   value="{{ old('account_number') }}" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label required">Account Holder Name</label>
                            <input type="text" name="account_holder_name" class="form-control" 
                                   value="{{ old('account_holder_name') }}" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label required">Branch Name</label>
                            <input type="text" name="branch_name" class="form-control" 
                                   value="{{ old('branch_name') }}" required>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showStep(2)">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="showStep(4)">
                                    Next: Additional Info <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Additional Information (Optional) -->
                <div class="step-section mb-4" id="step4" style="display: none;">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-info-circle me-2"></i>Additional Information (Optional)
                    </h5>
                    
                    <!-- Departments -->
                    <div class="mb-4">
                        <label class="form-label">Departments (Optional)</label>
                        <div class="row">
                            @foreach($departments as $department)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="departments[]" 
                                           value="{{ $department->id }}" 
                                           id="dept_{{ $department->id }}"
                                           {{ in_array($department->id, old('departments', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dept_{{ $department->id }}">
                                        {{ $department->name }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Other Optional Fields -->
                    <div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Issued District</label>
        <select name="citizenship_issued_district" class="form-control">
            <option value="">Select District</option>
            @foreach(config('nepal.districts') as $d)
                <option value="{{ $d }}" {{ old('citizenship_issued_district') == $d ? 'selected' : '' }}>{{ $d }}</option>
            @endforeach
        </select>
        @error('citizenship_issued_district')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">District</label>
        <select name="district" class="form-control">
            <option value="">Select District</option>
            @foreach(config('nepal.districts') as $d)
                <option value="{{ $d }}" {{ old('district') == $d ? 'selected' : '' }}>{{ $d }}</option>
            @endforeach
        </select>
        @error('district')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

        <div class="col-md-4">
            <label class="form-label">Ward Number</label>
            <input type="text" name="ward_number" class="form-control" value="{{ old('ward_number') }}">
            @error('ward_number')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Emergency Contact -->
        <div class="col-md-4">
            <label class="form-label">Emergency Contact Name</label>
            <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name') }}">
            @error('emergency_contact_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Emergency Contact Phone</label>
            <input type="tel" name="emergency_contact_phone" class="form-control" pattern="[0-9]{10}" maxlength="10" value="{{ old('emergency_contact_phone') }}">
            @error('emergency_contact_phone')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Relation</label>
            <input type="text" name="emergency_contact_relation" class="form-control" value="{{ old('emergency_contact_relation') }}">
            @error('emergency_contact_relation')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alternate Phone</label>
                            <input type="tel" name="alternate_phone" class="form-control" pattern="[0-9]{10}" maxlength="10" value="{{ old('alternate_phone') }}">
                            @error('alternate_phone')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Profile Image</label>
                            <input type="file" name="profile_image" class="form-control" accept="image/*">
                            <small class="text-muted">Optional - Max: 2MB</small>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showStep(3)">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </button>
                            <div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>Save Employee
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Submit Button (for testing) -->
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                       id="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Active Employee
                                </label>
                            </div>
                            <small class="text-muted">Required fields are marked with <span class="text-danger">*</span></small>
                        </div>
                        <div>
                            <button type="reset" class="btn btn-secondary me-2">
                                <i class="fas fa-redo me-2"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary" id="quickSubmitBtn">
                                <i class="fas fa-save me-2"></i>Save Employee
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
.step-section {
    padding: 20px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background-color: #f8f9fa;
}
.form-label.required::after {
    content: " *";
    color: #dc3545;
}
</style>
@endpush

@push('scripts')
<script>
let currentStep = 1;

function showStep(step) {
    // Hide all steps
    document.querySelectorAll('.step-section').forEach(div => {
        div.style.display = 'none';
    });
    
    // Show selected step
    document.getElementById('step' + step).style.display = 'block';
    currentStep = step;
    
    // Update progress indicators
    updateProgress(step);
}

function updateProgress(step) {
    const steps = document.querySelectorAll('.rounded-circle');
    const lines = document.querySelectorAll('.flex-grow-1 hr');
    
    steps.forEach((circle, index) => {
        if (index + 1 <= step) {
            circle.classList.remove('bg-secondary');
            circle.classList.add('bg-primary');
        } else {
            circle.classList.remove('bg-primary');
            circle.classList.add('bg-secondary');
        }
    });
    
    lines.forEach((line, index) => {
        if (index + 1 < step) {
            line.classList.remove('border-secondary');
            line.classList.add('border-primary');
        } else {
            line.classList.remove('border-primary');
            line.classList.add('border-secondary');
        }
    });
}

// Toggle password fields based on employment status
function togglePasswordField() {
    const status = document.getElementById('employment_status_field').value;
    const passwordContainer = document.getElementById('password_field_container');
    const passwordConfirmContainer = document.getElementById('password_confirm_field_container');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    
    if (status === 'active') {
        passwordContainer.style.display = 'block';
        passwordConfirmContainer.style.display = 'block';
        passwordInput.setAttribute('required', 'required');
        passwordConfirmInput.setAttribute('required', 'required');
    } else {
        passwordContainer.style.display = 'none';
        passwordConfirmContainer.style.display = 'none';
        passwordInput.removeAttribute('required');
        passwordConfirmInput.removeAttribute('required');
        passwordInput.value = '';
        passwordConfirmInput.value = '';
    }
}

// Toggle password visibility
function togglePasswordVisibility() {
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

// Toggle password confirmation visibility
function togglePasswordConfirmVisibility() {
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

// Quick validation before submit
document.getElementById('employeeForm').addEventListener('submit', function(e) {
    const requiredFields = this.querySelectorAll('[required]');
    let missingFields = [];
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            missingFields.push(field.previousElementSibling?.textContent?.trim() || field.name);
        }
    });

    // Check password match
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirmation');
    if (password && passwordConfirm && password.getAttribute('required') && password.value !== passwordConfirm.value) {
        missingFields.push('Passwords do not match');
    }
    
    if (missingFields.length > 0) {
        e.preventDefault();
        alert('⚠️ Please fix these errors:\n\n' + missingFields.join('\n'));
        
        // Show step 1
        showStep(1);
        
        // Focus on first missing field
        if (requiredFields[0]) {
            requiredFields[0].focus();
        }
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    showStep(1);
    togglePasswordField();
});
</script>
@endpush
@endsection