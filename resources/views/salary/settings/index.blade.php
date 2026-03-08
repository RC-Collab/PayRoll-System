@extends('layouts.app')

@section('title', 'Salary Settings')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Salary Settings</h1>
        <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Salary
        </a>
    </div>

    <!-- Settings Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="settingsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button">
                        <i class="fas fa-cog me-2"></i>Basic Settings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="components-tab" data-bs-toggle="tab" data-bs-target="#components" type="button">
                        <i class="fas fa-puzzle-piece me-2"></i>Custom Components
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button">
                        <i class="fas fa-clock me-2"></i>Attendance Rules
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="formulas-tab" data-bs-toggle="tab" data-bs-target="#formulas" type="button">
                        <i class="fas fa-calculator me-2"></i>Formulas
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="settingsTabContent">
                
                <!-- Basic Settings Tab -->
                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                    <form method="POST" action="{{ route('salary.settings.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Company Information</h5>
                                <div class="mb-3">
                                    <label class="form-label">Company Name *</label>
                                    <input type="text" name="company_name" class="form-control" 
                                           value="{{ $settings->company_name ?? '' }}" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Working Days per Month</label>
                                    <input type="number" name="working_days_per_month" class="form-control" 
                                           value="{{ $settings->working_days_per_month ?? 26 }}" step="0.01" min="1" max="31">
                                    <small class="text-muted">Standard: 26 days</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Working Hours per Day</label>
                                    <input type="number" name="working_hours_per_day" class="form-control" 
                                           value="{{ $settings->working_hours_per_day ?? 8 }}" step="0.01" min="1" max="24">
                                    <small class="text-muted">Standard: 8 hours</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="mb-3">Standard Deductions</h5>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="enable_provident_fund" 
                                           id="enable_pf" value="1" {{ ($settings->enable_provident_fund ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_pf">Enable Provident Fund</label>
                                </div>
                                
                                <div class="mb-3" id="pf_percentage_section" style="{{ ($settings->enable_provident_fund ?? false) ? '' : 'display: none;' }}">
                                    <label class="form-label">PF Percentage (%)</label>
                                    <input type="number" name="provident_fund_percentage" class="form-control" 
                                           value="{{ $settings->provident_fund_percentage ?? 10 }}" step="0.01" min="0" max="100">
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="enable_income_tax" 
                                           id="enable_tax" value="1" {{ ($settings->enable_income_tax ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_tax">Enable Income Tax</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Attendance Rules Tab -->
                <div class="tab-pane fade" id="attendance" role="tabpanel">
                    <form method="POST" action="{{ route('salary.settings.store') }}">
                        @csrf
                        
                        <h5 class="mb-3">Attendance-Based Deductions</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="enable_late_deduction" 
                                                   id="enable_late" value="1" {{ ($settings->enable_late_deduction ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="enable_late"><strong>Late Arrival Deduction</strong></label>
                                        </div>
                                    </div>
                                    <div class="card-body" id="late_deduction_section" style="{{ ($settings->enable_late_deduction ?? false) ? '' : 'display: none;' }}">
                                        <div class="mb-3">
                                            <label class="form-label">Deduction per Late Hour (रु)</label>
                                            <input type="number" name="late_deduction_per_hour" class="form-control" 
                                                   value="{{ $settings->late_deduction_per_hour ?? 0 }}" step="0.01" min="0">
                                            <small class="text-muted">Set 0 to use hourly rate calculation</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="enable_absent_deduction" 
                                                   id="enable_absent" value="1" {{ ($settings->enable_absent_deduction ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="enable_absent"><strong>Absent Deduction</strong></label>
                                        </div>
                                    </div>
                                    <div class="card-body" id="absent_deduction_section" style="{{ ($settings->enable_absent_deduction ?? false) ? '' : 'display: none;' }}">
                                        <div class="mb-3">
                                            <label class="form-label">Deduction per Absent Day (रु)</label>
                                            <input type="number" name="absent_deduction_per_day" class="form-control" 
                                                   value="{{ $settings->absent_deduction_per_day ?? 0 }}" step="0.01" min="0">
                                            <small class="text-muted">Set 0 to use daily rate calculation</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="enable_leave_deduction" 
                                                   id="enable_leave" value="1" {{ ($settings->enable_leave_deduction ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="enable_leave"><strong>Leave Deduction</strong></label>
                                        </div>
                                    </div>
                                    <div class="card-body" id="leave_deduction_section" style="{{ ($settings->enable_leave_deduction ?? false) ? '' : 'display: none;' }}">
                                        <div class="mb-3">
                                            <label class="form-label">Deduction per Leave Day (रु)</label>
                                            <input type="number" name="leave_deduction_per_day" class="form-control" 
                                                   value="{{ $settings->leave_deduction_per_day ?? 0 }}" step="0.01" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="enable_overtime" 
                                                   id="enable_overtime" value="1" {{ ($settings->enable_overtime ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="enable_overtime"><strong>Overtime Payment</strong></label>
                                        </div>
                                    </div>
                                    <div class="card-body" id="overtime_section" style="{{ ($settings->enable_overtime ?? false) ? '' : 'display: none;' }}">
                                        <div class="mb-3">
                                            <label class="form-label">Overtime Rate per Hour (रु)</label>
                                            <input type="number" name="overtime_rate_per_hour" class="form-control" 
                                                   value="{{ $settings->overtime_rate_per_hour ?? 0 }}" step="0.01" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Custom Components Tab -->
                <div class="tab-pane fade" id="components" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5>Custom Salary Components</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addComponentModal">
                            <i class="fas fa-plus me-2"></i>Add Component
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Calculation</th>
                                    <th>Amount</th>
                                    <th>Applicability</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customComponents as $component)
                                <tr>
                                    <td>
                                        <strong>{{ $component->name }}</strong>
                                        @if($component->description)
                                            <small class="d-block text-muted">{{ Str::limit($component->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $component->type == 'allowance' ? 'success' : ($component->type == 'deduction' ? 'danger' : 'info') }}">
                                            {{ ucfirst($component->type) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($component->calculation_type) }}</td>
                                    <td>
                                        @if($component->calculation_type == 'fixed')
                                            रु {{ number_format($component->fixed_amount) }}
                                        @elseif($component->calculation_type == 'percentage')
                                            {{ $component->percentage_amount }}%
                                        @else
                                            Formula
                                        @endif
                                    </td>
                                    <td>
                                        @if($component->apply_to_all)
                                            <span class="badge bg-info">All Employees</span>
                                        @else
                                            <span class="badge bg-secondary">Specific</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $component->is_active ? 'success' : 'danger' }}">
                                            {{ $component->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" data-bs-target="#editComponentModal{{ $component->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-{{ $component->is_active ? 'warning' : 'success' }} toggle-status"
                                                    data-id="{{ $component->id }}">
                                                <i class="fas fa-{{ $component->is_active ? 'times' : 'check' }}"></i>
                                            </button>
                                            <form method="POST" action="{{ route('salary.components.destroy', $component->id) }}" 
                                                  class="d-inline" onsubmit="return confirm('Delete this component?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-puzzle-piece fa-3x text-muted mb-3"></i>
                                        <h5>No custom components</h5>
                                        <p class="text-muted">Add your first custom component</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Formulas Tab -->
                <div class="tab-pane fade" id="formulas" role="tabpanel">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Use formulas like: <code>basic_salary * 0.1</code> or <code>(basic_salary + allowances) * 0.05</code>
                    </div>
                    
                    <form id="formulaForm">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Formula Name</label>
                                <input type="text" class="form-control" id="formulaName" placeholder="e.g., Insurance Calculation">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Variable Name</label>
                                <input type="text" class="form-control" id="formulaVariable" placeholder="e.g., insurance_calc">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-primary w-100" onclick="addFormula()">
                                    <i class="fas fa-plus me-2"></i>Add Formula
                                </button>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label">Formula</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="formulaExpression" 
                                           placeholder="e.g., basic_salary * {percentage}">
                                    <button type="button" class="btn btn-outline-secondary" onclick="showVariables()">
                                        <i class="fas fa-code"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Use variables in curly braces like {basic_salary}</small>
                            </div>
                        </div>
                    </form>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Available Variables</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <ul class="list-unstyled mb-0">
                                                <li><code>{basic_salary}</code></li>
                                                <li><code>{dearness_allowance}</code></li>
                                                <li><code>{house_rent}</code></li>
                                                <li><code>{medical_allowance}</code></li>
                                                <li><code>{overtime_hours}</code></li>
                                            </ul>
                                        </div>
                                        <div class="col-6">
                                            <ul class="list-unstyled mb-0">
                                                <li><code>{present_days}</code></li>
                                                <li><code>{absent_days}</code></li>
                                                <li><code>{late_hours}</code></li>
                                                <li><code>{working_days}</code></li>
                                                <li><code>{gross_salary}</code></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Saved Formulas</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush" id="formulasList">
                                        @foreach($formulas as $formula)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $formula->name }}</strong>
                                                <small class="d-block text-muted">{{ $formula->variable_name }}</small>
                                            </div>
                                            <span class="badge bg-info">{{ $formula->formula }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Component Modal -->
<div class="modal fade" id="addComponentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Custom Component</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('salary.components.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Component Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type *</label>
                                <select name="type" class="form-control" required>
                                    <option value="allowance">Allowance</option>
                                    <option value="deduction">Deduction</option>
                                    <option value="bonus">Bonus</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Calculation Type *</label>
                                <select name="calculation_type" class="form-control" id="calcType" required>
                                    <option value="fixed">Fixed Amount</option>
                                    <option value="percentage">Percentage of Basic Salary</option>
                                    <option value="formula">Formula Based</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3" id="fixedAmountSection">
                                <label class="form-label">Fixed Amount (रु)</label>
                                <input type="number" name="fixed_amount" class="form-control" step="0.01" min="0">
                            </div>
                            <div class="mb-3" id="percentageSection" style="display: none;">
                                <label class="form-label">Percentage (%)</label>
                                <input type="number" name="percentage_amount" class="form-control" step="0.01" min="0" max="100">
                            </div>
                            <div class="mb-3" id="formulaSection" style="display: none;">
                                <label class="form-label">Formula</label>
                                <input type="text" name="formula" class="form-control" placeholder="e.g., basic_salary * 0.05">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Applicability</label>
                                <select name="apply_to_all" class="form-control" id="applyToAll">
                                    <option value="1">All Employees</option>
                                    <option value="0">Specific Employees/Departments</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" checked>
                                <label class="form-check-label" for="isActive">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="specificSection" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Applicable Departments</label>
                                <select name="applicable_departments[]" class="form-control" multiple>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Applicable Employees</label>
                                <select name="applicable_employees[]" class="form-control" multiple>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Component</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sections based on calculation type
    const calcType = document.getElementById('calcType');
    const applyToAll = document.getElementById('applyToAll');
    
    function updateCalculationFields() {
        const type = calcType.value;
        document.getElementById('fixedAmountSection').style.display = type === 'fixed' ? 'block' : 'none';
        document.getElementById('percentageSection').style.display = type === 'percentage' ? 'block' : 'none';
        document.getElementById('formulaSection').style.display = type === 'formula' ? 'block' : 'none';
    }
    
    function updateApplicability() {
        const applyAll = applyToAll.value === '1';
        document.getElementById('specificSection').style.display = applyAll ? 'none' : 'block';
    }
    
    calcType.addEventListener('change', updateCalculationFields);
    applyToAll.addEventListener('change', updateApplicability);
    
    updateCalculationFields();
    updateApplicability();
    
    // Toggle status for components
    document.querySelectorAll('.toggle-status').forEach(button => {
        button.addEventListener('click', function() {
            const componentId = this.dataset.id;
            fetch(`/salary/components/${componentId}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });
    
    // Toggle settings sections
    const toggles = ['enable_pf', 'enable_late', 'enable_absent', 'enable_leave', 'enable_overtime'];
    
    toggles.forEach(toggleId => {
        const toggle = document.getElementById(toggleId);
        if (toggle) {
            toggle.addEventListener('change', function() {
                const sectionId = toggleId + '_section';
                const section = document.getElementById(sectionId);
                if (section) {
                    section.style.display = this.checked ? 'block' : 'none';
                }
            });
            
            // Trigger on load
            toggle.dispatchEvent(new Event('change'));
        }
    });
});

function addFormula() {
    const name = document.getElementById('formulaName').value;
    const variable = document.getElementById('formulaVariable').value;
    const expression = document.getElementById('formulaExpression').value;
    
    if (!name || !variable || !expression) {
        alert('Please fill all fields');
        return;
    }
    
    // Here you would send this to the server via AJAX
    fetch('/salary/formulas/store', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            name: name,
            variable_name: variable,
            formula: expression
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error saving formula');
        }
    });
}

function showVariables() {
    alert('Click on variables to insert them into formula field');
}
</script>
@endpush