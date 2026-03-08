@extends('layouts.app')

@section('title', 'Salary History')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Salary History</h1>
        <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Salary
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('salary.history') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select name="employee" class="form-control">
                        <option value="">All Employees</option>
                        @foreach($allEmployees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-control">
                        @for($i = date('Y'); $i >= 2020; $i--)
                            <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-control">
                        <option value="">All Months</option>
                        @foreach(range(1, 12) as $month)
                            @php
                                $monthName = DateTime::createFromFormat('!m', $month)->format('F');
                            @endphp
                            <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}" 
                                    {{ request('month') == str_pad($month, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ $monthName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('salary.history') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Salary History Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Month</th>
                            <th>Basic Salary</th>
                            <th>Allowances</th>
                            <th>Deductions</th>
                            <th>Net Salary</th>
                            <th>Payment Date</th>
                            <th>Status</th>
                            <th>Payment Info</th>
                            <th>Action</th>
                            <th>Payslip</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salaries as $salary)
                        <tr>
                            <td>
                                <strong>{{ $salary->employee->full_name }}</strong>
                                <div class="small text-muted">{{ $salary->employee->employee_code }}</div>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($salary->salary_month)->format('M Y') }}
                            </td>
                            <td>
                                रु {{ number_format($salary->basic_salary) }}
                            </td>
                            <td>
                                <span class="text-success">+ रु {{ number_format($salary->total_allowances) }}</span>
                            </td>
                            <td>
                                <span class="text-danger">- रु {{ number_format($salary->total_deductions) }}</span>
                            </td>
                            <td>
                                <strong class="text-primary">रु {{ number_format($salary->net_salary) }}</strong>
                            </td>
                            <td>
                                @if($salary->payment_date)
                                    {{ \Carbon\Carbon::parse($salary->payment_date)->format('d M, Y') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $salary->payment_status == 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($salary->payment_status) }}
                                </span>
                            </td>
                            <td>
                                @if($salary->payment_status == 'paid')
                                    <small class="d-block">
                                        {{ $salary->payment_method }}
                                    </small>
                                    @if($salary->payment_bank)
                                        <small class="d-block">Bank: {{ $salary->payment_bank }}</small>
                                    @endif
                                    @if($salary->cheque_number)
                                        <small class="d-block">Cheque: {{ $salary->cheque_number }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($salary->payment_status !== 'paid')
                                    <a href="{{ route('salary.payout.form', $salary->id) }}" 
                                       class="btn btn-sm btn-success mb-1">
                                        <i class="fas fa-money-check-alt"></i> Payout
                                    </a>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('salary.payslip', $salary->id) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                <h5>No salary records found</h5>
                                <p class="text-muted">No salary records match your filter criteria</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($salaries->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $salaries->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection