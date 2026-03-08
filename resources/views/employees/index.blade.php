@extends('layouts.app')

@section('title', 'Employee Management')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Employee Management</h1>
        <a href="{{ route('employees.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Employee
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Total Employees</h6>
                            <h2 class="mb-0">{{ $stats['total'] ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Active</h6>
                            <h2 class="mb-0">{{ $stats['active'] ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-user-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">On Leave</h6>
                            <h2 class="mb-0">{{ $stats['onLeave'] ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-umbrella-beach fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">This Month</h6>
                            <h2 class="mb-0">{{ $stats['newThisMonth'] ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('employees.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or code..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="department" class="form-control">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="on-leave" {{ request('status') == 'on-leave' ? 'selected' : '' }}>On Leave</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="employee_type" class="form-control">
                            <option value="">All Types</option>
                            @foreach(['permanent', 'contract', 'temporary', 'probation', 'part-time'] as $type)
                                <option value="{{ $type }}" {{ request('employee_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Employees Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Contact</th>
                            <th>Department</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Joining Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        <tr>
                            <td>{{ $employee->employee_code }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        @if($employee->profile_image)
                                            <img src="{{ asset('storage/' . $employee->profile_image) }}" 
                                                 alt="{{ $employee->full_name }}" 
                                                 class="rounded-circle" width="40" height="40">
                                        @else
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $employee->full_name }}</h6>
                                        <small class="text-muted">{{ $employee->designation }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $employee->email }}</div>
                                <small class="text-muted">{{ $employee->mobile_number }}</small>
                            </td>
                            <td>
                                @if($employee->departments->count() > 0)
                                    @foreach($employee->departments as $dept)
                                        <span class="badge bg-info mb-1">{{ $dept->name }}</span>
                                        @if($dept->pivot->role)
                                            <br><small class="text-muted">{{ $dept->pivot->role }}</small>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $employee->employee_type == 'permanent' ? 'success' : 'warning' }}">
                                    {{ ucfirst($employee->employee_type) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active' => 'success',
                                        'inactive' => 'secondary',
                                        'on-leave' => 'warning',
                                        'suspended' => 'danger',
                                        'terminated' => 'dark'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$employee->employment_status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('-', ' ', $employee->employment_status)) }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('employees.show', $employee->id) }}" 
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('employees.edit', $employee->id) }}" 
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete({{ $employee->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-users fa-2x text-muted mb-3"></i>
                                <h5>No employees found</h5>
                                <p class="text-muted">Start by adding your first employee</p>
                                <a href="{{ route('employees.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add Employee
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($employees->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} entries
                </div>
                <div>
                    {{ $employees->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this employee? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Employee</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(employeeId) {
    const form = document.getElementById('deleteForm');
    form.action = `/employees/${employeeId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
@endsection