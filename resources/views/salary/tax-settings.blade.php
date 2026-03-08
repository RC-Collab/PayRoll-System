@extends('layouts.app')

@section('title', 'Tax Settings')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mb-4">Tax Settings - Fiscal Year {{ $taxSetting->fiscal_year ?? '2082/83' }}</h1>

    <form method="POST" action="{{ route('salary.tax-settings.update') }}">
        @csrf
        
        <div class="row">
            <!-- Unmarried Tax Slabs -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Unmarried Tax Slabs</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>From (NPR)</th>
                                    <th>To (NPR)</th>
                                    <th>Rate (%)</th>
                                    <th>Fixed Amount</th>
                                </tr>
                            </thead>
                            <tbody id="unmarried-slabs">
                                @foreach($taxSetting->unmarried_slabs ?? [] as $index => $slab)
                                <tr>
                                    <td><input type="number" name="unmarried_slabs[{{ $index }}][from]" 
                                               value="{{ $slab['from'] }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" name="unmarried_slabs[{{ $index }}][to]" 
                                               value="{{ $slab['to'] }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" name="unmarried_slabs[{{ $index }}][rate]" 
                                               value="{{ $slab['rate'] }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" name="unmarried_slabs[{{ $index }}][fixed]" 
                                               value="{{ $slab['fixed'] ?? 0 }}" class="form-control form-control-sm"></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addUnmarriedSlab()">
                            <i class="fas fa-plus"></i> Add Slab
                        </button>
                    </div>
                </div>
            </div>

            <!-- Married Tax Slabs -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Married Tax Slabs</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>From (NPR)</th>
                                    <th>To (NPR)</th>
                                    <th>Rate (%)</th>
                                    <th>Fixed Amount</th>
                                </tr>
                            </thead>
                            <tbody id="married-slabs">
                                @foreach($taxSetting->married_slabs ?? [] as $index => $slab)
                                <tr>
                                    <td><input type="number" name="married_slabs[{{ $index }}][from]" 
                                               value="{{ $slab['from'] }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" name="married_slabs[{{ $index }}][to]" 
                                               value="{{ $slab['to'] }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" name="married_slabs[{{ $index }}][rate]" 
                                               value="{{ $slab['rate'] }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" name="married_slabs[{{ $index }}][fixed]" 
                                               value="{{ $slab['fixed'] ?? 0 }}" class="form-control form-control-sm"></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="addMarriedSlab()">
                            <i class="fas fa-plus"></i> Add Slab
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deduction Limits -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Deduction Limits</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>PF Percentage (%)</label>
                        <input type="number" name="pf_percentage" step="0.01" 
                               value="{{ $taxSetting->pf_percentage ?? 10 }}" 
                               class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>SSF Employee (%)</label>
                        <input type="number" name="ssf_employee_percentage" step="0.01" 
                               value="{{ $taxSetting->ssf_employee_percentage ?? 11 }}" 
                               class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>SSF Employer (%)</label>
                        <input type="number" name="ssf_employer_percentage" step="0.01" 
                               value="{{ $taxSetting->ssf_employer_percentage ?? 20 }}" 
                               class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>CIT Max (NPR)</label>
                        <input type="number" name="cit_max_amount" 
                               value="{{ $taxSetting->cit_max_amount ?? 300000 }}" 
                               class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Insurance Max (NPR)</label>
                        <input type="number" name="insurance_max_amount" 
                               value="{{ $taxSetting->insurance_max_amount ?? 40000 }}" 
                               class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Medical Max (NPR)</label>
                        <input type="number" name="medical_max_amount" 
                               value="{{ $taxSetting->medical_max_amount ?? 20000 }}" 
                               class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Remote Area Max (NPR)</label>
                        <input type="number" name="remote_area_max_amount" 
                               value="{{ $taxSetting->remote_area_max_amount ?? 50000 }}" 
                               class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save Tax Settings
        </button>
    </form>
</div>

@push('scripts')
<script>
function addUnmarriedSlab() {
    const tbody = document.getElementById('unmarried-slabs');
    const index = tbody.children.length;
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="number" name="unmarried_slabs[${index}][from]" class="form-control form-control-sm"></td>
        <td><input type="number" name="unmarried_slabs[${index}][to]" class="form-control form-control-sm"></td>
        <td><input type="number" name="unmarried_slabs[${index}][rate]" class="form-control form-control-sm"></td>
        <td><input type="number" name="unmarried_slabs[${index}][fixed]" class="form-control form-control-sm"></td>
    `;
    tbody.appendChild(row);
}

function addMarriedSlab() {
    const tbody = document.getElementById('married-slabs');
    const index = tbody.children.length;
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="number" name="married_slabs[${index}][from]" class="form-control form-control-sm"></td>
        <td><input type="number" name="married_slabs[${index}][to]" class="form-control form-control-sm"></td>
        <td><input type="number" name="married_slabs[${index}][rate]" class="form-control form-control-sm"></td>
        <td><input type="number" name="married_slabs[${index}][fixed]" class="form-control form-control-sm"></td>
    `;
    tbody.appendChild(row);
}
</script>
@endpush
@endsection