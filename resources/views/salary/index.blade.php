@extends('layouts.app')

@section('title', 'Salary Management')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Salary Management</h1>
        <div>
            <span class="badge bg-primary me-2">{{ $currentMonth }}</span>
            @if(!$monthEnded)
                <span class="badge bg-warning text-dark">Month still open</span>
            @endif

            <!-- Settings Dropdown -->
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-cog me-2"></i>Settings
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Salary Components</h6></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('salary.components.index') }}">
                            <i class="fas fa-puzzle-piece me-2"></i>Components
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('salary.allowances.index') }}">
                            <i class="fas fa-plus-circle me-2 text-success"></i>Allowances
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('salary.deductions.index') }}">
                            <i class="fas fa-minus-circle me-2 text-danger"></i>Deductions
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Tax Configuration</h6></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('salary.tax-settings.index') }}">
                            <i class="fas fa-percent me-2 text-info"></i>Tax Settings
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('salary.formulas.index') }}">
                            <i class="fas fa-calculator me-2 text-warning"></i>Formulas
                        </a>
                    </li>
                </ul>
            </div>

            <a href="{{ route('salary.history') }}" class="btn btn-outline-info me-2">
                <i class="fas fa-history me-2"></i>History
            </a>
            
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#calculateModal" 
                    @if(!$monthEnded) disabled @endif>
                <i class="fas fa-calculator me-2"></i>Calculate Salary
            </button>
        </div> <!-- end header right controls -->
    </div> <!-- end header row -->

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Employees</h6>
                            <h3 class="mb-0">{{ $stats['totalEmployees'] }}</h3>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Paid This Month</h6>
                            <h3 class="mb-0">{{ $stats['paidThisMonth'] }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Pending</h6>
                            <h3 class="mb-0">{{ $stats['pendingSalaryCount'] }}</h3>
                        </div>
                        <i class="fas fa-clock fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Payroll</h6>
                            <h3 class="mb-0">रु {{ number_format($stats['currentMonthPayroll'], 2) }}</h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards (New) -->
    @if($stats['totalAbsentDeductions'] > 0 || $stats['totalManualFines'] > 0)
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-2">Total Absent Deductions</h6>
                            <h4 class="text-warning mb-0">रु {{ number_format($stats['totalAbsentDeductions'], 2) }}</h4>
                        </div>
                        <i class="fas fa-user-clock fa-3x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-2">Total Manual Fines</h6>
                            <h4 class="text-danger mb-0">रु {{ number_format($stats['totalManualFines'], 2) }}</h4>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-3x text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('salary.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-control">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Name or Employee Code" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <input type="month" name="month" class="form-control" 
                           value="{{ $currentMonth }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>Apply
                        </button>
                        <a href="{{ route('salary.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Employees Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Employee Salaries - {{ $currentMonth }}</h5>
                <div class="btn-toolbar">
                    <!-- toolbar intentionally left blank; individual actions available per row -->
                </div>
            </div>
        </div>
        <div class="card-body">
            <form id="bulkForm">
                @csrf
                <input type="hidden" id="currentMonth" value="{{ $currentMonth }}">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll(this)" @if(!$monthEnded) disabled @endif>
                                </th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Basic Salary</th>
                                <th>Allowances</th>
                                <th>Deductions</th>
                                <th>Net Salary</th>
                                <th>Absent Days</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                            @php
                                $salary = $employee->monthlySalaries->first();
                                $absentDays = $salary ? ($salary->attendance_summary['absent_days'] ?? 0) : 0;
                                $status = $salary->payment_status ?? 'pending';
                                $statusClass = [
                                    'paid' => 'success',
                                    'calculated' => 'info',
                                    'pending' => 'warning'
                                ][$status] ?? 'secondary';
                                
                                // Calculate absent deduction amount
                                $basicSalary = $employee->salaryStructure->basic_salary ?? 0;
                                $absentDeduction = $basicSalary > 0 ? ($basicSalary / 30 * $absentDays) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" 
                                           onchange="updateBulkButton()" @if(!$monthEnded) disabled @endif>
                                </td>
                                <td>
                                    <strong>{{ $employee->full_name }}</strong>
                                    <div class="small text-muted">{{ $employee->employee_code }}</div>
                                    @if($employee->marital_status)
                                        <span class="badge bg-info" title="Married">👫 Married</span>
                                    @else
                                        <span class="badge bg-secondary" title="Unmarried">👤 Unmarried</span>
                                    @endif
                                </td>
                                <td>
                                    @foreach($employee->departments as $dept)
                                        <span class="badge bg-info">{{ $dept->name }}</span>
                                    @endforeach
                                </td>
                                <td>रु {{ number_format($basicSalary, 2) }}</td>
                                <td>
                                    @if($salary)
                                        <span class="text-success">+ रु {{ number_format($salary->total_allowances, 2) }}</span>
                                        @if($salary->manual_bonus > 0)
                                            <span class="badge bg-success" title="{{ $salary->bonus_reason }}">+Bonus</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($salary)
                                        <span class="text-danger">- रु {{ number_format($salary->total_deductions, 2) }}</span>
                                        @if($salary->manual_fine > 0)
                                            <span class="badge bg-danger" title="{{ $salary->fine_reason }}">Fine</span>
                                        @endif
                                        @if($salary->absent_deduction > 0)
                                            <span class="badge bg-warning" title="Absent Deduction">Absent</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($salary)
                                        <strong class="text-primary">रु {{ number_format($salary->net_salary, 2) }}</strong>
                                    @else
                                        <span class="text-muted">Not calculated</span>
                                    @endif
                                </td>
                                <td>
                                    @if($absentDays > 0)
                                        <span class="badge bg-warning">{{ $absentDays }} days</span>
                                        <small class="text-muted d-block">
                                            Deduction: रु {{ number_format($absentDeduction, 2) }}
                                        </small>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($salary)
                                        <span class="badge bg-{{ $statusClass }}">{{ ucfirst($status) }}</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($monthEnded)
                                        <div class="btn-group" role="group">
                                            @if($salary)
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="openFineModal({{ $salary->id }}, '{{ $employee->full_name }}')"
                                                        title="Apply Fine">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="openBonusModal({{ $salary->id }}, '{{ $employee->full_name }}')"
                                                        title="Apply Bonus">
                                                    <i class="fas fa-gift"></i>
                                                </button>
                                                <a href="{{ route('salary.edit', $salary->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('salary.payslip', $salary->id) }}" class="btn btn-sm btn-outline-info" title="Payslip">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            @endif
                                            {{-- calculate/recalculate button always available for uncalculated or pending rows --}}
                                            @if(!$salary || ($salary && $salary->payment_status == 'pending'))
                                                <form method="POST" action="{{ route('salary.calculate') }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="month" value="{{ $currentMonth }}">
                                                    <input type="hidden" name="calculate_for" value="employee">
                                                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-primary" 
                                                            title="{{ $salary ? 'Recalculate' : 'Calculate Salary' }}">
                                                        <i class="fas fa-calculator"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            {{-- payout button only if salary exists and pending --}}
                                            @if($salary && $salary->payment_status !== 'paid')
                                                <a href="{{ route('salary.payout.form', $salary->id) }}" 
                                                    class="btn btn-sm btn-success mb-1" title="Payout">
                                                    <i class="fas fa-money-check-alt"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Month not closed</span>
                                    @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5>No employees found</h5>
                                    <p class="text-muted">No active employees match your criteria</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
            
            @if($employees->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $employees->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Include Modals only when month closed -->
@if($monthEnded)
    @include('salary.partials.fine-bonus-modal')
    @include('salary.partials.calculate-modal')
@endif
@endsection

@push('scripts')
<script>
// Select/Deselect All
function selectAll() {
    const checkboxes = document.querySelectorAll('input[name="employee_ids[]"]');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => cb.checked = !allChecked);
    if (selectAllCheckbox) selectAllCheckbox.checked = !allChecked;
    updateBulkButton();
}

function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll('input[name="employee_ids[]"]');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkButton();
}

function updateBulkButton() {
    const checkedCount = document.querySelectorAll('input[name="employee_ids[]"]:checked').length;
    const bulkBtn = document.querySelector('button[onclick="openBulkFineModal()"]');
    if (bulkBtn) {
        bulkBtn.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Bulk Fine (${checkedCount})`;
    }
}

// Fine Modal Functions
function openFineModal(salaryId, employeeName) {
    document.getElementById('fine_salary_id').value = salaryId;
    document.getElementById('fine_employee_name').value = employeeName;
    document.getElementById('display_employee_name').textContent = employeeName;
    document.getElementById('fine_amount').value = '';
    document.getElementById('fine_reason').value = '';
    new bootstrap.Modal(document.getElementById('fineModal')).show();
}

function openBonusModal(salaryId, employeeName) {
    document.getElementById('bonus_salary_id').value = salaryId;
    document.getElementById('bonus_employee_name').value = employeeName;
    document.getElementById('display_bonus_employee').textContent = employeeName;
    document.getElementById('bonus_amount').value = '';
    document.getElementById('bonus_reason').value = '';
    new bootstrap.Modal(document.getElementById('bonusModal')).show();
}

function openBulkFineModal() {
    const checkboxes = document.querySelectorAll('input[name="employee_ids[]"]:checked');
    if (checkboxes.length === 0) {
        toastr.warning('Please select at least one employee');
        return;
    }
    
    const employeeIds = [];
    const employeeNames = [];
    checkboxes.forEach(cb => {
        employeeIds.push(cb.value);
        const row = cb.closest('tr');
        const nameCell = row.querySelector('td:nth-child(2) strong');
        const name = nameCell ? nameCell.textContent : 'Unknown';
        employeeNames.push(name);
    });
    
    document.getElementById('bulk_employee_ids').value = JSON.stringify(employeeIds);
    document.getElementById('bulk_month').value = document.getElementById('currentMonth').value;
    
    const listHtml = employeeNames.map(name => 
        `<div class="mb-1"><i class="fas fa-user me-2"></i>${name}</div>`
    ).join('');
    document.getElementById('selectedEmployeesList').innerHTML = listHtml;
    
    new bootstrap.Modal(document.getElementById('bulkFineModal')).show();
}

// Toastr Configuration
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 3000
};
</script>
@endpush