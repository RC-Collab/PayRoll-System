@extends('layouts.app')

@section('title', 'Leave Details')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Leave Details</h1>
        <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Employee:</th>
                                    <td>{{ $leave->employee->full_name }}</td>
                                </tr>
                                <tr>
                                    <th>Employee Code:</th>
                                    <td>{{ $leave->employee->employee_code }}</td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td>{{ $leave->employee->departments->first()->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Leave Type:</th>
                                    <td>
                                        <span class="badge bg-{{ leave_type_color($leave->leave_type) }}">
                                            {{ ucfirst($leave->leave_type) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Start Date:</th>
                                    <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>End Date:</th>
                                    <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Total Days:</th>
                                    <td>{{ $leave->total_days }} days</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($leave->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($leave->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Reason -->
                    <div class="mt-4">
                        <h6>Reason for Leave:</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                {{ $leave->reason }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Remarks (if any) -->
                    @if($leave->remarks)
                    <div class="mt-4">
                        <h6>Remarks:</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                {{ $leave->remarks }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Actions Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    @if($leave->status == 'pending')
                        <form method="POST" action="{{ route('leaves.approve', $leave->id) }}" class="mb-3">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Approve with Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" placeholder="Optional remarks"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check me-2"></i>Approve Leave
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('leaves.reject', $leave->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Reject with Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" placeholder="Reason for rejection"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-times me-2"></i>Reject Leave
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This leave has already been {{ $leave->status }}.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection