@extends('layouts.app')

@section('title', 'Salary Payslip')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Salary Payslip</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Employee:</strong><br>
                    {{ $salary->employee->full_name }}<br>
                    <small class="text-muted">{{ $salary->employee->employee_code }}</small>
                </div>
                <div class="col-md-6 text-end">
                    <strong>Month:</strong><br>
                    {{ \Carbon\Carbon::parse($salary->salary_month)->format('F Y') }}
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

            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <strong>Net Salary:</strong>
                    <strong class="text-primary">रु {{ number_format($salary->net_salary, 2) }}</strong>
                </div>
            </div>

            @php
                $attendance = $salary->getAttendanceSummary();
            @endphp
            @if(is_array($attendance) && count($attendance))
                <h5 class="mt-4">Attendance</h5>
                <div class="row">
                    @foreach($attendance as $key => $value)
                        <div class="col-md-3">
                            <span class="text-muted">{{ ucfirst(str_replace('_',' ',$key)) }}:</span>
                            <span class="float-end">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <h5 class="mt-4">Payment Details</h5>
            <div class="row mb-2">
                <div class="col-md-4">
                    <span>Method:</span>
                    <span class="float-end">{{ ucfirst($salary->payment_method) ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <span>Bank:</span>
                    <span class="float-end">{{ $salary->payment_bank ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <span>Cheque #:</span>
                    <span class="float-end">{{ $salary->cheque_number ?? '-' }}</span>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4">
                    <span>Payment Date:</span>
                    <span class="float-end">{{ $salary->payment_date?->format('d M, Y') ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <span>Paid Amount:</span>
                    <span class="float-end">रु {{ number_format($salary->paid_amount ?? $salary->net_salary, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection