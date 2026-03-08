@if($leaves->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Contact</th>
                    <th>Leave Type</th>
                    <th>Date Range</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    @if($showActions ?? false)
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($leaves as $leave)
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
                        <div class="small text-muted">{{ $leave->employee->email }}</div>
                    </td>
                    <td>
                        <span class="badge bg-{{ leave_type_color($leave->leave_type) }}">
                            {{ ucfirst($leave->leave_type) }}
                        </span>
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - 
                        {{ \Carbon\Carbon::parse($leave->end_date)->format('d M, Y') }}
                    </td>
                    <td>
                        <span class="badge bg-info">{{ $leave->total_days }} days</span>
                    </td>
                    <td>
                        <div class="text-truncate" style="max-width: 200px;" title="{{ $leave->reason }}">
                            {{ $leave->reason }}
                        </div>
                    </td>
                    <td>
                        @if($leave->status == 'approved')
                            <span class="badge bg-success">Approved</span>
                            <div class="small text-muted">
                                {{ \Carbon\Carbon::parse($leave->approved_at)->format('d M, Y') }}
                            </div>
                        @elseif($leave->status == 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                            <div class="small text-muted">
                                {{ \Carbon\Carbon::parse($leave->rejected_at)->format('d M, Y') }}
                            </div>
                        @endif
                    </td>
                    @if($showActions ?? false)
                    <td>
                        <div class="btn-group" role="group">
                            @if($leave->status == 'pending')
                                <button type="button" class="btn btn-sm btn-success" 
                                        onclick="approveLeave({{ $leave->id }})">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="rejectLeave({{ $leave->id }})">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(method_exists($leaves, 'links'))
        <div class="d-flex justify-content-center mt-3">
            {{ $leaves->links() }}
        </div>
    @endif
@else
    <div class="text-center py-4">
        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
        <h5>No leave records found</h5>
        <p class="text-muted">No leaves match the selected criteria</p>
    </div>
@endif