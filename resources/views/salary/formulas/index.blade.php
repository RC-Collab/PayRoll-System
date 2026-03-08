@extends('layouts.app')

@section('title', 'Salary Formulas')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Salary Formulas</h1>
        <div>
            <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Salary
            </a>
            <a href="{{ route('salary.formulas.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Formula
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Available Variables</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($variables as $key => $label)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <code>{ {{ $key }} }</code>
                            <span class="badge bg-info">{{ $label }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Use variables in curly braces in your formulas
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Formula Tester</h5>
                </div>
                <div class="card-body">
                    <form id="formulaTester">
                        <div class="mb-3">
                            <label class="form-label">Formula</label>
                            <input type="text" class="form-control" id="testFormula" placeholder="e.g., {basic_salary} * 0.1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Test Variables (JSON)</label>
                            <textarea class="form-control" id="testVariables" rows="3">{"basic_salary": 50000}</textarea>
                        </div>
                        <button type="button" class="btn btn-primary w-100" onclick="testFormula()">
                            <i class="fas fa-calculator me-2"></i>Test Formula
                        </button>
                    </form>
                    <div id="testResult" class="mt-3" style="display: none;">
                        <div class="alert alert-success">
                            <strong>Result:</strong> <span id="resultValue"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Variable</th>
                                    <th>Formula</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formulas as $formula)
                                <tr>
                                    <td>
                                        <strong>{{ $formula->name }}</strong>
                                        @if($formula->description)
                                            <div class="small text-muted">{{ $formula->description }}</div>
                                        @endif
                                    </td>
                                    <td><code>{{ $formula->variable_name }}</code></td>
                                    <td><code>{{ $formula->formula }}</code></td>
                                    <td>
                                        <span class="badge bg-{{ $formula->is_active ? 'success' : 'danger' }}">
                                            {{ $formula->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('salary.formulas.edit', $formula->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('salary.formulas.destroy', $formula->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this formula?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function testFormula() {
    const formula = document.getElementById('testFormula').value;
    const variables = document.getElementById('testVariables').value;
    
    fetch('/salary/formulas/test', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            formula: formula,
            variables: JSON.parse(variables)
        })
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('testResult');
        const resultValue = document.getElementById('resultValue');
        
        if (data.success) {
            resultValue.textContent = data.result;
            resultDiv.style.display = 'block';
            resultDiv.querySelector('.alert').className = 'alert alert-success';
        } else {
            resultValue.textContent = data.message;
            resultDiv.style.display = 'block';
            resultDiv.querySelector('.alert').className = 'alert alert-danger';
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endpush
@endsection