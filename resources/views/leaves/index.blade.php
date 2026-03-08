@extends('layouts.app')

@section('title', 'Leave Management')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Leave Management</h1>
        <div class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            Admin/HR Panel - Manage Employee Leaves
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Pending</h6>
                            <h2 class="mb-0">{{ $stats['pending'] }}</h2>
                        </div>
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Approved</h6>
                            <h2 class="mb-0">{{ $stats['approved'] }}</h2>
                        </div>
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Rejected</h6>
                            <h2 class="mb-0">{{ $stats['rejected'] }}</h2>
                        </div>
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">On Leave</h6>
                            <h2 class="mb-0">{{ $stats['onLeave'] }}</h2>
                        </div>
                        <i class="fas fa-umbrella-beach fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Leaves (Top Priority) -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-clock me-2"></i>Pending Leave Requests
                <span class="badge bg-light text-primary ms-2">{{ $pendingLeaves->count() }}</span>
            </h5>
        </div>
        <div class="card-body">
            @if($pendingLeaves->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee Details</th>
                                <th>Leave Type</th>
                                <th>Date Range</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Applied On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingLeaves as $leave)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            @if($leave->employee->profile_image)
                                                <img src="{{ asset('storage/' . $leave->employee->profile_image) }}" 
                                                     class="rounded-circle" width="45" height="45">
                                            @else
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 45px; height: 45px; font-size: 16px;">
                                                    {{ substr($leave->employee->first_name, 0, 1) }}{{ substr($leave->employee->last_name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <strong>{{ $leave->employee->full_name }}</strong>
                                            <div class="small text-muted">
                                                {{ $leave->employee->designation }}
                                            </div>
                                            <div class="small">
                                                <i class="fas fa-phone-alt me-1"></i>{{ $leave->employee->mobile_number }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ leave_type_color($leave->leave_type) }}">
                                        {{ ucfirst($leave->leave_type) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }}</strong>
                                    <div class="small text-muted">to</div>
                                    <strong>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M, Y') }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $leave->total_days }} days</span>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $leave->reason }}">
                                        {{ $leave->reason }}
                                    </div>
                                    @if($leave->medical_certificate)
                                        <div class="small text-danger mt-1">
                                            <i class="fas fa-file-medical me-1"></i>Medical Certificate Required
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($leave->created_at)->format('d M, Y') }}
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="approveLeave({{ $leave->id }})" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="rejectLeave({{ $leave->id }})" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>No pending leave requests</h5>
                    <p class="text-muted">All leave requests have been processed</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Leave Status Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="leaveTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="approved-tab" data-bs-toggle="tab" 
                            data-bs-target="#approved" type="button">
                        Approved Leaves ({{ $approvedLeaves->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" 
                            data-bs-target="#rejected" type="button">
                        Rejected Leaves ({{ $rejectedLeaves->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="current-tab" data-bs-toggle="tab" 
                            data-bs-target="#current" type="button">
                        Currently on Leave ({{ $currentLeaves->count() }})
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="leaveTabsContent">
                
                <!-- Approved Leaves Tab -->
                <div class="tab-pane fade show active" id="approved" role="tabpanel">
                    @include('leaves._leave_list', ['leaves' => $approvedLeaves, 'showActions' => false])
                </div>

                <!-- Rejected Leaves Tab -->
                <div class="tab-pane fade" id="rejected" role="tabpanel">
                    @include('leaves._leave_list', ['leaves' => $rejectedLeaves, 'showActions' => false])
                </div>

                <!-- Currently on Leave Tab -->
                <div class="tab-pane fade" id="current" role="tabpanel">
                    @if($currentLeaves->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Contact</th>
                                        <th>Leave Type</th>
                                        <th>Leave Period</th>
                                        <th>Days Left</th>
                                        <th>Department</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($currentLeaves as $leave)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    @if($leave->employee->profile_image)
                                                        <img src="{{ asset('storage/' . $leave->employee->profile_image) }}" 
                                                             class="rounded-circle" width="40" height="40">
                                                    @else
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px;">
                                                            {{ substr($leave->employee->first_name, 0, 1) }}{{ substr($leave->employee->last_name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <strong>{{ $leave->employee->full_name }}</strong>
                                                    <div class="small text-muted">{{ $leave->employee->designation }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $leave->employee->mobile_number }}</div>
                                            @if($leave->contact_during_leave)
                                                <div class="small text-muted">
                                                    During leave: {{ $leave->contact_during_leave }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ leave_type_color($leave->leave_type) }}">
                                                {{ ucfirst($leave->leave_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - 
                                            {{ \Carbon\Carbon::parse($leave->end_date)->format('d M, Y') }}
                                            <div class="small text-muted">
                                                {{ $leave->total_days }} days
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $daysLeft = \Carbon\Carbon::parse($leave->end_date)->diffInDays(now());
                                            @endphp
                                            @if($daysLeft > 0)
                                                <span class="badge bg-warning">{{ $daysLeft }} days left</span>
                                            @else
                                                <span class="badge bg-success">Returning today</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $leave->employee->departments->first()->name ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>No employees currently on leave</h5>
                            <p class="text-muted">All employees are present at work</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Modal -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalTitle">Approve Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="actionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="action" id="actionType">
                    <div class="mb-3">
                        <label class="form-label">Remarks (Optional)</label>
                        <textarea name="remarks" class="form-control" rows="3" 
                                  placeholder="Add remarks for approval/rejection"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="actionButton">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.nav-tabs .nav-link {
    border-bottom: 2px solid transparent;
    font-weight: 500;
}
.nav-tabs .nav-link.active {
    border-bottom-color: #0d6efd;
    font-weight: 600;
}
.avatar-sm {
    flex-shrink: 0;
}
</style>
@endpush

@push('scripts')
<script>
let currentLeaveId = null;

function approveLeave(leaveId) {
    currentLeaveId = leaveId;
    document.getElementById('actionModalTitle').textContent = 'Approve Leave';
    document.getElementById('actionType').value = 'approve';
    document.getElementById('actionButton').className = 'btn btn-success';
    document.getElementById('actionButton').textContent = 'Approve Leave';
    document.getElementById('actionForm').action = `/leaves/${leaveId}/approve`;
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}

function rejectLeave(leaveId) {
    currentLeaveId = leaveId;
    document.getElementById('actionModalTitle').textContent = 'Reject Leave';
    document.getElementById('actionType').value = 'reject';
    document.getElementById('actionButton').className = 'btn btn-danger';
    document.getElementById('actionButton').textContent = 'Reject Leave';
    document.getElementById('actionForm').action = `/leaves/${leaveId}/reject`;
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}
</script>
@endpush
@endsection