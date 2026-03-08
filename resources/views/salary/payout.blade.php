@extends('layouts.app')

@section('title', 'Salary Payout')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Salary Payout</h1>
        <a href="{{ route('salary.history') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to History
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Payslip Details</h5>
            <div class="row">
                <div class="col-md-6">
                    <strong>Employee:</strong> {{ $salary->employee->full_name }}<br>
                    <small class="text-muted">{{ $salary->employee->employee_code }}</small>
                </div>
                <div class="col-md-6 text-end">
                    <strong>Month:</strong> {{ \Carbon\Carbon::parse($salary->salary_month)->format('F Y') }}
                </div>
            </div>

            <hr>
            <div class="row mb-2">
                <div class="col-md-4">
                    <span>Basic Salary:</span>
                    <span class="float-end">रु {{ number_format($salary->basic_salary, 2) }}</span>
                </div>
                <div class="col-md-4">
                    <span>Allowances:</span>
                    <span class="float-end text-success">+ रु {{ number_format($salary->total_allowances, 2) }}</span>
                </div>
                <div class="col-md-4">
                    <span>Deductions:</span>
                    <span class="float-end text-danger">- रु {{ number_format($salary->total_deductions, 2) }}</span>
                </div>
            </div>
            <hr>
            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <strong>Net Salary:</strong>
                    <strong class="text-primary">रु {{ number_format($salary->net_salary, 2) }}</strong>
                </div>
            </div>

            <form method="POST" action="{{ route('salary.payout.process', $salary->id) }}">
                @csrf

                <h5 class="mb-3">Payment Information</h5>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Payment Method *</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="Cheque">Cheque</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bank Name *</label>
                        <input type="text" name="payment_bank" class="form-control" value="{{ old('payment_bank') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cheque Number</label>
                        <input type="text" name="cheque_number" class="form-control" value="{{ old('cheque_number') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Amount (रु) *</label>
                        <input type="number" name="paid_amount" class="form-control" step="0.01" 
                               value="{{ old('paid_amount', $salary->net_salary) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Date *</label>
                        <input type="date" name="payment_date" class="form-control" 
                               value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check-circle me-2"></i>Mark as Paid
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
