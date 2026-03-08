@php
use Illuminate\Support\Str;
@endphp

@extends('layouts.app')

@section('title', 'Salary Components')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Salary Components</h1>
        <div>
            <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Salary
            </a>
            <a href="{{ route('salary.components.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Component
            </a>
        </div>
    </div>

    <!-- Components Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Calculation</th>
                            <th>Amount/Rate</th>
                            <th>Applicable To</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($components as $component)
                        <tr>
                            <td>
                                <strong>{{ $component->name }}</strong>
                                @if($component->description)
                                    <small class="d-block text-muted">{{ Str::limit($component->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeColors = [
                                        'allowance' => 'success',
                                        'deduction' => 'danger',
                                        'bonus' => 'info',
                                        'other' => 'secondary'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $typeColors[$component->type] ?? 'secondary' }}">
                                    {{ ucfirst($component->type) }}
                                </span>
                            </td>
                            <td>
                                {{ ucfirst(str_replace('_', ' ', $component->calculation_type)) }}
                            </td>
                            <td>
                                @if($component->calculation_type == 'fixed')
                                    रु {{ number_format($component->fixed_amount, 2) }}
                                @elseif($component->calculation_type == 'percentage')
                                    {{ $component->percentage }}%
                                @elseif($component->calculation_type == 'attendance_based')
                                    रु {{ number_format($component->attendance_rate, 2) }} per {{ $component->attendance_field }}
                                @elseif($component->calculation_type == 'formula')
                                    <code>{{ $component->formula }}</code>
                                @endif
                            </td>
                            <td>
                                @php
                                    $applicableText = [
                                        'all' => 'All Employees',
                                        'department' => 'Specific Departments',
                                        'employee' => 'Specific Employees',
                                        'designation' => 'Specific Designations'
                                    ];
                                @endphp
                                {{ $applicableText[$component->applicable_to] ?? ucfirst($component->applicable_to) }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $component->is_active ? 'success' : 'danger' }}">
                                    {{ $component->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('salary.components.edit', $component->id) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-{{ $component->is_active ? 'warning' : 'success' }} toggle-status"
                                            data-id="{{ $component->id }}">
                                        <i class="fas fa-{{ $component->is_active ? 'times' : 'check' }}"></i>
                                    </button>
                                    <form method="POST" action="{{ route('salary.components.destroy', $component->id) }}" 
                                          class="d-inline" onsubmit="return confirm('Delete this component?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-puzzle-piece fa-3x text-muted mb-3"></i>
                                <h5>No salary components found</h5>
                                <p class="text-muted">Create your first salary component</p>
                                <a href="{{ route('salary.components.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add Component
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle status
    document.querySelectorAll('.toggle-status').forEach(button => {
        button.addEventListener('click', function() {
            const componentId = this.dataset.id;
            fetch(`/salary/components/${componentId}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });
});
</script>
@endpush