@extends('layouts.app')

@section('title', $employee->full_name . ' - Employee Details')

@section('content')
<div class="container-fluid px-4">
    <!-- Header with Breadcrumb and Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
                <li class="breadcrumb-item active">{{ $employee->full_name }}</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cog me-2"></i>Quick Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('employees.edit', $employee->id) }}">
                        <i class="fas fa-edit me-2"></i>Edit Employee
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Profile
                    </a></li>
                    <li><a class="dropdown-item" href="#">
                        <i class="fas fa-file-pdf me-2"></i>Export to PDF
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    @if($employee->is_active)
                        <li><a class="dropdown-item text-warning" href="#" 
                               onclick="toggleEmployeeStatus({{ $employee->id }}, 'inactive')">
                            <i class="fas fa-user-slash me-2"></i>Deactivate
                        </a></li>
                    @else
                        <li><a class="dropdown-item text-success" href="#" 
                               onclick="toggleEmployeeStatus({{ $employee->id }}, 'active')">
                            <i class="fas fa-user-check me-2"></i>Activate
                        </a></li>
                    @endif
                    <li><a class="dropdown-item text-danger" href="#" 
                           onclick="confirmDelete({{ $employee->id }})">
                        <i class="fas fa-trash me-2"></i>Delete Employee
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Profile Card -->
    <div class="row mb-4">
        <!-- Left Column - Profile Info -->
        <div class="col-lg-4 col-xl-3">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <!-- Profile Image -->
                    <div class="position-relative mb-3">
                        @if($employee->profile_image)
                            <img src="{{ asset('storage/' . $employee->profile_image) }}" 
                                 alt="{{ $employee->full_name }}" 
                                 class="rounded-circle shadow" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center shadow" 
                                 style="width: 150px; height: 150px;">
                                <span class="display-4 text-white">
                                    {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        <div class="position-absolute bottom-0 end-0">
                            <span class="badge bg-{{ $employee->is_active ? 'success' : 'danger' }} p-2">
                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Basic Info -->
                    <h4 class="card-title mb-1">{{ $employee->full_name }}</h4>
                    <p class="text-muted mb-2">{{ $employee->designation }}</p>
                    <p class="text-muted mb-3">{{ $employee->employee_code }}</p>
                    
                    <!-- Quick Stats -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="bg-light rounded p-2">
                                <small class="text-muted d-block">Employee Type</small>
                                <strong class="text-primary">{{ ucfirst($employee->employee_type) }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-2">
                                <small class="text-muted d-block">Status</small>
                                <strong class="text-primary">{{ ucfirst(str_replace('-', ' ', $employee->employment_status)) }}</strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="text-start">
                        <div class="mb-2">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <a href="mailto:{{ $employee->email }}" class="text-decoration-none">
                                {{ $employee->email }}
                            </a>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <a href="tel:{{ $employee->mobile_number }}" class="text-decoration-none">
                                {{ $employee->mobile_number }}
                            </a>
                        </div>
                        @if($employee->alternate_phone)
                        <div class="mb-3">
                            <i class="fas fa-phone-alt text-primary me-2"></i>
                            <a href="tel:{{ $employee->alternate_phone }}" class="text-decoration-none">
                                {{ $employee->alternate_phone }}
                            </a>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Edit Button -->
                    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </a>
                </div>
            </div>

            <!-- Department Card -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-building me-2"></i>Departments & Roles</h6>
                </div>
                <div class="card-body">
                    @if($employee->departments->count() > 0)
                        @foreach($employee->departments as $department)
                            <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong>{{ $department->name }}</strong>
                                    <span class="badge bg-info">{{ $department->pivot->role ?? 'Member' }}</span>
                                </div>
                                @if($department->description)
                                    <p class="small text-muted mb-0">{{ $department->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No departments assigned</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Details -->
        <div class="col-lg-8 col-xl-9">
            <!-- Details Tabs -->
            <div class="card">
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personal">
                                <i class="fas fa-user me-2"></i>Personal
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#employment">
                                <i class="fas fa-briefcase me-2"></i>Employment
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bank">
                                <i class="fas fa-university me-2"></i>Bank Details
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents">
                                <i class="fas fa-file-alt me-2"></i>Documents
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Personal Details Tab -->
                        <div class="tab-pane fade show active" id="personal">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Personal Information</h6>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Gender</small>
                                        <span class="fw-medium">{{ ucfirst($employee->gender) }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Date of Birth</small>
                                        <span class="fw-medium">{{ $employee->date_of_birth->format('M d, Y') }}</span>
                                        <small class="text-muted"> (Age: {{ $employee->date_of_birth->age }} years)</small>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Marital Status</small>
                                        <span class="fw-medium">{{ ucfirst($employee->marital_status) }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Blood Group</small>
                                        <span class="fw-medium">{{ $employee->blood_group ?? 'Not specified' }}</span>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Citizenship Details</h6>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Citizenship Number</small>
                                        <span class="fw-medium">{{ $employee->citizenship_number }}</span>
                                    </div>
                                    @if($employee->citizenship_issue_date)
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Issue Date</small>
                                        <span class="fw-medium">{{ $employee->citizenship_issue_date->format('M d, Y') }}</span>
                                    </div>
                                    @endif
                                    @if($employee->citizenship_issued_district)
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Issued District</small>
                                        <span class="fw-medium">{{ $employee->citizenship_issued_district }}</span>
                                    </div>
                                    @endif
                                    @if($employee->pan_number)
                                    <div class="mb-3">
                                        <small class="text-muted d-block">PAN Number</small>
                                        <span class="fw-medium">{{ $employee->pan_number }}</span>
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="col-12">
                                    <hr class="my-4">
                                    <h6 class="text-muted mb-3">Address Information</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="card h-100">
                                                <div class="card-header bg-light py-2">
                                                    <small class="fw-medium">Current Address</small>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-1">{{ $employee->current_address }}</p>
                                                    <div class="text-muted small">
                                                        {{ $employee->municipality }}, Ward {{ $employee->ward_number }}<br>
                                                        {{ $employee->district }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card h-100">
                                                <div class="card-header bg-light py-2">
                                                    <small class="fw-medium">Permanent Address</small>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-1">{{ $employee->permanent_address }}</p>
                                                    <div class="text-muted small">
                                                        {{ $employee->municipality }}, Ward {{ $employee->ward_number }}<br>
                                                        {{ $employee->district }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <hr class="my-4">
                                    <h6 class="text-muted mb-3">Emergency Contact</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <small class="text-muted d-block">Contact Name</small>
                                                <span class="fw-medium">{{ $employee->emergency_contact_name }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <small class="text-muted d-block">Phone Number</small>
                                                <span class="fw-medium">{{ $employee->emergency_contact_phone }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <small class="text-muted d-block">Relationship</small>
                                                <span class="fw-medium">{{ $employee->emergency_contact_relation }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Details Tab -->
                        <div class="tab-pane fade" id="employment">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Employment Details</h6>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Joining Date</small>
                                        <span class="fw-medium">{{ $employee->joining_date->format('M d, Y') }}</span>
                                        <small class="text-muted"> ({{ $employee->joining_date->diffForHumans() }})</small>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Employee Type</small>
                                        <span class="badge bg-info">{{ ucfirst($employee->employee_type) }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Designation</small>
                                        <span class="fw-medium">{{ $employee->designation }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Basic Salary</small>
                                        <span class="fw-medium">
                                            @if($employee->salaryStructure)
                                                रु {{ number_format($employee->salaryStructure->basic_salary, 2) }}
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">OT Rate (per hr)</small>
                                        <span class="fw-medium">
                                            @if($employee->salaryStructure)
                                                रु {{ number_format($employee->salaryStructure->overtime_rate ?? 0, 2) }}
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </span>
                                    </div>
                                    @if($employee->contract_end_date)
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Contract End Date</small>
                                        <span class="fw-medium {{ $employee->contract_end_date->isPast() ? 'text-danger' : '' }}">
                                            {{ $employee->contract_end_date->format('M d, Y') }}
                                        </span>
                                        @if($employee->contract_end_date->isFuture())
                                            <small class="text-muted"> ({{ $employee->contract_end_date->diffForHumans() }})</small>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Education & Experience</h6>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Qualification</small>
                                        <span class="fw-medium">{{ $employee->qualification }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Institution</small>
                                        <span class="fw-medium">{{ $employee->institution_name }}</span>
                                    </div>
                                    @if($employee->experience_years)
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Experience</small>
                                        <span class="fw-medium">{{ $employee->experience_years }} years</span>
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="col-12">
                                    <hr class="my-4">
                                    <h6 class="text-muted mb-3">Employment Timeline</h6>
                                    <div class="timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-primary"></div>
                                            <div class="timeline-content">
                                                <small class="text-muted">Joined</small>
                                                <h6 class="mb-1">{{ $employee->joining_date->format('M d, Y') }}</h6>
                                                <p class="text-muted mb-0">Started as {{ $employee->designation }}</p>
                                            </div>
                                        </div>
                                        @if($employee->contract_end_date && $employee->employee_type == 'contract')
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-warning"></div>
                                            <div class="timeline-content">
                                                <small class="text-muted">Contract End</small>
                                                <h6 class="mb-1">{{ $employee->contract_end_date->format('M d, Y') }}</h6>
                                                <p class="text-muted mb-0">
                                                    {{ $employee->contract_end_date->isFuture() ? 'Ends' : 'Ended' }} 
                                                    {{ $employee->contract_end_date->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Details Tab -->
                        <div class="tab-pane fade" id="bank">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Bank Information</h6>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Bank Name</small>
                                        <span class="fw-medium">{{ $employee->bank_name }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Account Number</small>
                                        <span class="fw-medium">{{ $employee->account_number }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Account Holder</small>
                                        <span class="fw-medium">{{ $employee->account_holder_name }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Branch Name</small>
                                        <span class="fw-medium">{{ $employee->branch_name }}</span>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Additional Information</h6>
                                    @if($employee->alternate_phone)
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Alternate Phone</small>
                                        <span class="fw-medium">{{ $employee->alternate_phone }}</span>
                                    </div>
                                    @endif
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Profile Status</small>
                                        <span class="badge bg-{{ $employee->is_active ? 'success' : 'danger' }}">
                                            {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Last Updated</small>
                                        <span class="fw-medium">{{ $employee->updated_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                </div>
                                
                                @if($employee->notes)
                                <div class="col-12">
                                    <hr class="my-4">
                                    <h6 class="text-muted mb-3">Notes</h6>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <p class="mb-0">{{ $employee->notes }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Documents Tab -->
                        <div class="tab-pane fade" id="documents">
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fas fa-folder-open fa-3x text-muted"></i>
                                </div>
                                <h5 class="text-muted">Document Management</h5>
                                <p class="text-muted mb-4">Upload and manage employee documents</p>
                                <button class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>Upload Documents
                                </button>
                                <button class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-folder-plus me-2"></i>Add Document Category
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-calendar-plus me-1"></i>
                                Created: {{ $employee->created_at->format('M d, Y') }}
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fas fa-sync-alt me-1"></i>
                                Last Updated: {{ $employee->updated_at->format('M d, Y h:i A') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
        margin-bottom: 1.5rem;
    }

    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        padding: 0.75rem 1.25rem;
        transition: all 0.3s;
    }

    .nav-tabs .nav-link:hover {
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }

    .nav-tabs .nav-link.active {
        color: #0d6efd;
        background-color: transparent;
        border-bottom: 3px solid #0d6efd;
    }

    .badge {
        font-size: 0.75em;
        font-weight: 500;
        padding: 0.35em 0.65em;
    }

    /* Timeline styling */
    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .timeline-marker {
        position: absolute;
        left: -2rem;
        top: 0.25rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 0 3px var(--bs-primary);
    }

    .timeline-content {
        padding-left: 1rem;
    }

    .timeline-marker.bg-warning {
        box-shadow: 0 0 0 3px var(--bs-warning);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-header-tabs .nav-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .timeline {
            padding-left: 1.5rem;
        }
        
        .timeline::before {
            left: 0.25rem;
        }
        
        .timeline-marker {
            left: -1.5rem;
        }
    }

    /* Print styles */
    @media print {
        .breadcrumb, .dropdown, .btn, .nav-tabs, .card-footer {
            display: none !important;
        }
        
        .card {
            border: none;
            box-shadow: none;
        }
        
        .tab-content .tab-pane {
            display: block !important;
            opacity: 1 !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
        document.getElementById(`delete-form-${id}`).submit();
    }
}

function toggleEmployeeStatus(id, status) {
    if (confirm(`Are you sure you want to ${status === 'active' ? 'activate' : 'deactivate'} this employee?`)) {
        // You can implement AJAX call here or use a form
        fetch(`/employees/${id}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating status');
            }
        });
    }
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Store current tab in localStorage for better UX
    const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]')
    tabEls.forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', event => {
            localStorage.setItem('activeTab', event.target.getAttribute('data-bs-target'))
        })
    })

    // Activate stored tab on page load
    const activeTab = localStorage.getItem('activeTab')
    if (activeTab) {
        const tabTrigger = document.querySelector(`[data-bs-target="${activeTab}"]`)
        if (tabTrigger) {
            new bootstrap.Tab(tabTrigger).show()
        }
    }
});
</script>

<!-- Hidden delete form -->
<form id="delete-form-{{ $employee->id }}" action="{{ route('employees.destroy', $employee->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endpush
@endsection