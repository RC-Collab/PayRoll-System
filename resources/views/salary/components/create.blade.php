@extends('layouts.app')

@section('title', 'Add Salary Component')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Add Salary Component</h1>
        <a href="{{ route('salary.components.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('salary.components.store') }}">
                @csrf
                
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
                                <option value="">Select Type</option>
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
                            <select name="calculation_type" class="form-control" id="calculationType" required>
                                <option value="">Select Calculation Type</option>
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage of Basic Salary</option>
                                <option value="formula">Formula Based</option>
                                <option value="attendance_based">Attendance Based</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3" id="fixedAmountSection" style="display: none;">
                            <label class="form-label">Fixed Amount (रु) *</label>
                            <input type="number" name="fixed_amount" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="mb-3" id="percentageSection" style="display: none;">
                            <label class="form-label">Percentage (%) *</label>
                            <input type="number" name="percentage" class="form-control" step="0.01" min="0" max="100">
                        </div>
                        <div class="mb-3" id="formulaSection" style="display: none;">
                            <label class="form-label">Formula *</label>
                            <input type="text" name="formula" class="form-control" placeholder="e.g., basic_salary * 0.1">
                            <small class="text-muted">Use variables like: basic_salary, present_days, absent_days</small>
                        </div>
                        <div class="row" id="attendanceSection" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Attendance Field *</label>
                                    <select name="attendance_field" class="form-control">
                                        <option value="">Select Field</option>
                                        <option value="late_minutes">Late Minutes</option>
                                        <option value="absent_days">Absent Days</option>
                                        <option value="present_days">Present Days</option>
                                        <option value="overtime_hours">Overtime Hours</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Rate (रु) *</label>
                                    <input type="number" name="attendance_rate" class="form-control" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Applicable To *</label>
                            <select name="applicable_to" class="form-control" id="applicableTo" required>
                                <option value="all">All Employees</option>
                                <option value="department">Specific Departments</option>
                                <option value="employee">Specific Employees</option>
                                <option value="designation">Specific Designations</option>
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
                            <label class="form-label">Departments</label>
                            <select name="applicable_ids[]" class="form-control" multiple>
                                @foreach($departments as $dept)
                                    <option value="department_{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Employees</label>
                            <select name="applicable_ids[]" class="form-control" multiple>
                                @foreach($employees as $emp)
                                    <option value="employee_{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Designations</label>
                            <input type="text" name="applicable_ids[]" class="form-control" placeholder="Enter designations separated by comma">
                            <small class="text-muted">e.g., Manager, Developer, Accountant</small>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="reset" class="btn btn-secondary">Reset</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Component
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calcType = document.getElementById('calculationType');
    const applicableTo = document.getElementById('applicableTo');
    
    function updateCalculationFields() {
        const type = calcType.value;
        document.getElementById('fixedAmountSection').style.display = type === 'fixed' ? 'block' : 'none';
        document.getElementById('percentageSection').style.display = type === 'percentage' ? 'block' : 'none';
        document.getElementById('formulaSection').style.display = type === 'formula' ? 'block' : 'none';
        document.getElementById('attendanceSection').style.display = type === 'attendance_based' ? 'block' : 'none';
    }
    
    function updateApplicability() {
        const applyType = applicableTo.value;
        document.getElementById('specificSection').style.display = applyType === 'all' ? 'none' : 'block';
    }
    
    calcType.addEventListener('change', updateCalculationFields);
    applicableTo.addEventListener('change', updateApplicability);
    
    updateCalculationFields();
    updateApplicability();
});
</script>
@endpush 