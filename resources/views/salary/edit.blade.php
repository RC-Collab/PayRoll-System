@extends('layouts.app')

@section('title', 'Edit Salary')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Edit Salary</h1>
        <div>
            <span class="text-muted me-3">{{ $salary->employee->full_name }} | {{ \Carbon\Carbon::parse($salary->salary_month)->format('F Y') }}</span>
            <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('salary.update', $salary->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <h5 class="mb-3">Basic Information</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Basic Salary (रु) *</label>
                                    <input type="number" name="basic_salary" class="form-control" 
                                           value="{{ old('basic_salary', $salary->basic_salary) }}" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Status *</label>
                                    <select name="payment_status" class="form-control" required>
                                        <option value="pending" {{ $salary->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="calculated" {{ $salary->payment_status == 'calculated' ? 'selected' : '' }}>Calculated</option>
                                        <option value="paid" {{ $salary->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="hold" {{ $salary->payment_status == 'hold' ? 'selected' : '' }}>Hold</option>
                                        <option value="cancelled" {{ $salary->payment_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <input type="text" name="payment_method" class="form-control"
                                           value="{{ old('payment_method', $salary->payment_method) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="payment_bank" class="form-control"
                                           value="{{ old('payment_bank', $salary->payment_bank) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Cheque Number</label>
                                    <input type="text" name="cheque_number" class="form-control"
                                           value="{{ old('cheque_number', $salary->cheque_number) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Paid Amount (रु)</label>
                                    <input type="number" name="paid_amount" class="form-control" step="0.01"
                                           value="{{ old('paid_amount', $salary->paid_amount ?? $salary->net_salary) }}">
                                </div>
                            </div>
                        </div>

                        <!-- University/College Allowances (Nepal Standard) -->
                        <h5 class="mb-3">Allowances</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Dearness Allowance (रु)</label>
                                    <input type="number" name="dearness_allowance" class="form-control" 
                                           value="{{ old('dearness_allowance', $salary->dearness_allowance) }}" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">House Rent Allowance (रु)</label>
                                    <input type="number" name="house_rent_allowance" class="form-control" 
                                           value="{{ old('house_rent_allowance', $salary->house_rent_allowance) }}" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Medical Allowance (रु)</label>
                                    <input type="number" name="medical_allowance" class="form-control" 
                                           value="{{ old('medical_allowance', $salary->medical_allowance) }}" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Overtime Hours</label>
                                    <input type="number" name="overtime_hours" class="form-control" 
                                           value="{{ old('overtime_hours', $salary->overtime_hours) }}" step="0.5">
                                </div>
                            </div>
                        </div>

                        <!-- Deductions -->
                        <h5 class="mb-3">Deductions</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Provident Fund (रु)</label>
                                    <input type="number" name="provident_fund" class="form-control" 
                                           value="{{ old('provident_fund', $salary->provident_fund) }}" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Citizen Investment (रु)</label>
                                    <input type="number" name="citizen_investment" class="form-control" 
                                           value="{{ old('citizen_investment', $salary->citizen_investment) }}" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Income Tax (रु)</label>
                                    <input type="number" name="income_tax" class="form-control" 
                                           value="{{ old('income_tax', $salary->income_tax) }}" step="0.01">
                                    <small class="text-muted">Calculated as per Nepal Tax Slab FY 2080/81</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Penalty Leave Deduction (रु)</label>
                                    <input type="number" name="penalty_leave_deduction" class="form-control" 
                                           value="{{ old('penalty_leave_deduction', $salary->penalty_leave_deduction ?? 0) }}" step="0.01">
                                    <small class="text-muted">Deduction for unauthorized leaves</small>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Adjustments -->
                        @php
                            use Illuminate\Support\Facades\Schema;
                        @endphp
                        <h5 class="mb-3">Manual Adjustments</h5>
                        <div class="row mb-4">
                            @if(Schema::hasColumn('monthly_salaries','late_deduction'))
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Late Fine (रु)</label>
                                    <input type="number" name="late_deduction" class="form-control" 
                                           value="{{ old('late_deduction', $salary->late_deduction) }}" step="0.01">
                                    <small class="text-muted">Enter manually if employee is habitually late</small>
                                </div>
                            </div>
                            @endif

                            @if(Schema::hasColumn('monthly_salaries','bonus_amount'))
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Bonus (रु)</label>
                                    <input type="number" name="bonus_amount" class="form-control" 
                                           value="{{ old('bonus_amount', $salary->bonus_amount) }}" step="0.01">
                                </div>
                            </div>
                            @endif

                            @if(Schema::hasColumn('monthly_salaries','insurance_amount'))
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Insurance / Other Deduction (रु)</label>
                                    <input type="number" name="insurance_amount" class="form-control" 
                                           value="{{ old('insurance_amount', $salary->insurance_amount) }}" step="0.01">
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Remarks -->
                        <div class="mb-4">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $salary->remarks) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="reset" class="btn btn-secondary">Reset</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Salary
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Salary Summary -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Salary Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Employee:</strong><br>
                        {{ $salary->employee->full_name }}<br>
                        <small class="text-muted">{{ $salary->employee->employee_code }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Month:</strong><br>
                        {{ \Carbon\Carbon::parse($salary->salary_month)->format('F Y') }}
                    </div>
                    
                    <hr>
                    
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Basic Salary:</span>
                            <span>रु {{ number_format($salary->basic_salary, 2) }}</span>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Total Allowances:</span>
                            <span class="text-success">+ रु {{ number_format($salary->total_allowances, 2) }}</span>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Total Deductions:</span>
                            <span class="text-danger">- रु {{ number_format($salary->total_deductions, 2) }}</span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>Net Salary:</strong>
                            <strong class="text-primary">रु {{ number_format($salary->net_salary, 2) }}</strong>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <a href="{{ route('salary.payslip', $salary->id) }}" 
                           class="btn btn-info">
                            <i class="fas fa-file-pdf me-2"></i>View Payslip
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection