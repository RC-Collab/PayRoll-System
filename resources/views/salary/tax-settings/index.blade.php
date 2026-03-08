@extends('layouts.app')

@section('title', 'Tax Settings')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Tax Settings</h1>
        <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Salary
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Nepal Tax Configuration</h5>
        </div>
        <div class="card-body">
            <div class="text-center py-5">
                <i class="fas fa-percent fa-4x text-muted mb-3"></i>
                <h4>Tax Settings Coming Soon!</h4>
                <p class="text-muted mb-4">This feature is under development. You'll be able to configure Nepal's progressive tax slabs here.</p>
                
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Coming Features:</strong>
                            <ul class="text-start mt-2 mb-0">
                                <li>Configure tax slabs for unmarried employees</li>
                                <li>Configure tax slabs for married employees</li>
                                <li>Set PF, CIT, and insurance deduction limits</li>
                                <li>Fiscal year management</li>
                                <li>Auto-calculation based on salary ranges</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button class="btn btn-info me-2" disabled>
                        <i class="fas fa-save me-2"></i>Save Settings (Coming Soon)
                    </button>
                    <a href="{{ route('salary.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview of Nepal Tax Slabs 2082/83 -->
    <div class="card mt-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Current Nepal Tax Structure (Preview)</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">Unmarried Individuals</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Annual Income (NPR)</th>
                                <th>Tax Rate</th>
                                <th>Calculation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Up to 5,00,000</td><td>1%</td><td>1% of income</td></tr>
                            <tr><td>5,00,001 - 7,00,000</td><td>10%</td><td>5,000 + 10% above 5,00,000</td></tr>
                            <tr><td>7,00,001 - 10,00,000</td><td>20%</td><td>25,000 + 20% above 7,00,000</td></tr>
                            <tr><td>10,00,001 - 20,00,000</td><td>30%</td><td>85,000 + 30% above 10,00,000</td></tr>
                            <tr><td>Above 20,00,000</td><td>36%</td><td>3,85,000 + 36% above 20,00,000</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-success">Married Couples</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Annual Income (NPR)</th>
                                <th>Tax Rate</th>
                                <th>Calculation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Up to 6,00,000</td><td>1%</td><td>1% of income</td></tr>
                            <tr><td>6,00,001 - 8,00,000</td><td>10%</td><td>6,000 + 10% above 6,00,000</td></tr>
                            <tr><td>8,00,001 - 11,00,000</td><td>20%</td><td>26,000 + 20% above 8,00,000</td></tr>
                            <tr><td>11,00,001 - 21,00,000</td><td>30%</td><td>86,000 + 30% above 11,00,000</td></tr>
                            <tr><td>Above 21,00,000</td><td>36%</td><td>3,86,000 + 36% above 21,00,000</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3 text-center text-muted">
                <small><i class="fas fa-info-circle me-1"></i> These are the current Nepal tax slabs for FY 2082/83. You'll be able to customize these in the settings.</small>
            </div>
        </div>
    </div>
</div>
@endsection