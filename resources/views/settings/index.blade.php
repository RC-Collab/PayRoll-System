@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-800 mb-2">
            <i class="fas fa-cog text-blue-600 me-3"></i>System Settings
        </h1>
        <p class="text-slate-600">Manage company information and system preferences</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" id="settingsForm">
        @csrf
        @method('PUT')

        <!-- Company Information Section -->
        <div class="card mb-5 shadow-sm border-0">
            <div class="card-header bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4">
                <h5 class="mb-0 d-flex align-items-center">
                    <i class="fas fa-building me-2"></i>Company Information
                </h5>
            </div>
            <div class="card-body p-5">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Name</label>
                        <input type="text" name="company_name" class="form-control form-control-lg" 
                               value="{{ $settings['company_name'] ?? 'Nepal Payroll System' }}" required
                               placeholder="Enter company name">
                        <small class="text-muted">This will appear in the sidebar and reports</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Email</label>
                        <input type="email" name="company_email" class="form-control form-control-lg" 
                               value="{{ $settings['company_email'] ?? '' }}"
                               placeholder="contact@company.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Address</label>
                        <input type="text" name="company_address" class="form-control form-control-lg" 
                               value="{{ $settings['company_address'] ?? '' }}" required
                               placeholder="Kathmandu, Nepal">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Phone</label>
                        <input type="tel" name="company_phone" class="form-control form-control-lg" 
                               value="{{ $settings['company_phone'] ?? '' }}"
                               placeholder="+977-1-XXXXXXX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Registration Number</label>
                        <input type="text" name="company_reg_number" class="form-control form-control-lg" 
                               value="{{ $settings['company_reg_number'] ?? '' }}"
                               placeholder="PAN/VAT Number">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fiscal Year Start Month</label>
                        <select name="fiscal_year_start" class="form-select form-select-lg">
                            <option value="">Select Month</option>
                            <option value="1" {{ ($settings['fiscal_year_start'] ?? '') == 1 ? 'selected' : '' }}>January</option>
                            <option value="4" {{ ($settings['fiscal_year_start'] ?? '') == 4 ? 'selected' : '' }}>April</option>
                            <option value="7" {{ ($settings['fiscal_year_start'] ?? '') == 7 ? 'selected' : '' }}>July</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll Settings Section -->
        <div class="card mb-5 shadow-sm border-0">
            <div class="card-header bg-gradient-to-r from-green-600 to-green-700 text-white py-4">
                <h5 class="mb-0 d-flex align-items-center">
                    <i class="fas fa-money-bill-wave me-2"></i>Payroll Configuration
                </h5>
            </div>
            <div class="card-body p-5">
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Working Days per Month</label>
                        <input type="number" name="working_days_per_month" class="form-control form-control-lg" 
                               value="{{ $settings['working_days_per_month'] ?? 26 }}" min="20" max="31">
                        <small class="text-muted">For salary calculation</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Provident Fund (%)</label>
                        <div class="input-group input-group-lg">
                            <input type="number" name="provident_fund_percentage" class="form-control" 
                                   value="{{ $settings['provident_fund_percentage'] ?? 10 }}" min="0" max="20" step="0.1">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tax Deduction (%)</label>
                        <div class="input-group input-group-lg">
                            <input type="number" name="tds_percentage" class="form-control" 
                                   value="{{ $settings['tds_percentage'] ?? 1.5 }}" min="0" max="5" step="0.1">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leave Configuration Section -->
        <div class="card mb-5 shadow-sm border-0">
            <div class="card-header bg-gradient-to-r from-amber-600 to-amber-700 text-white py-4">
                <h5 class="mb-0 d-flex align-items-center">
                    <i class="fas fa-calendar-alt me-2"></i>Leave Configuration
                </h5>
            </div>
            <div class="card-body p-5">
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sick Leave (Days/Year)</label>
                        <input type="number" name="sick_leave_days" class="form-control form-control-lg" 
                               value="{{ $settings['sick_leave_days'] ?? 15 }}" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Casual Leave (Days/Year)</label>
                        <input type="number" name="casual_leave_days" class="form-control form-control-lg" 
                               value="{{ $settings['casual_leave_days'] ?? 12 }}" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Annual Leave (Days/Year)</label>
                        <input type="number" name="annual_leave_days" class="form-control form-control-lg" 
                               value="{{ $settings['annual_leave_days'] ?? 18 }}" min="0">
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-3 justify-content-end mb-4">
            <a href="{{ route('dashboard') }}" class="btn btn-light btn-lg">
                <i class="fas fa-times me-2"></i>Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-2"></i>Save All Settings
            </button>
        </div>
    </form>
</div>

<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.form-control-lg, .form-select-lg {
    padding: 0.75rem 1rem;
    font-size: 1rem;
}

.card-header {
    background-image: linear-gradient(135deg, var(--bs-primary), var(--bs-info));
}
</style>
@endsection