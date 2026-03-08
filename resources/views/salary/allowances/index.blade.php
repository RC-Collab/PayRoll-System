@extends('layouts.app')

@section('title', 'Allowance Management')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Allowance Management</h1>
        <div>
            <a href="{{ route('salary.allowances.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Allowance
            </a>
            <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Salary
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">All Allowances</h5>
        </div>
        <div class="card-body">
            <div class="text-center py-5">
                <i class="fas fa-plus-circle fa-4x text-muted mb-3"></i>
                <h4>Allowance Management Coming Soon!</h4>
                <p class="text-muted mb-4">This feature is under development. You'll be able to manage all employee allowances here.</p>
                
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Coming Features:</strong>
                            <ul class="text-start mt-2 mb-0">
                                <li>Create fixed allowances (DA, HRA, Medical, etc.)</li>
                                <li>Percentage-based allowances (10% of basic)</li>
                                <li>Formula-based allowances using expressions</li>
                                <li>Taxable/Non-taxable allowance options</li>
                                <li>Per-employee allowance overrides</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('salary.allowances.create') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-2"></i>Create First Allowance
                </a>
            </div>
        </div>
    </div>
</div>
@endsection