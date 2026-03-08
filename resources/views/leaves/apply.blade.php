@extends('layouts.app')

@section('title', 'Apply for Leave')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-file-signature me-2"></i>Apply for Leave</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('leaves.apply') }}" id="leaveForm">
                        @csrf
                        
                        <!-- Employee Selection (for HR/Admin) -->
                        @if(auth()->user()->isAdmin() || auth()->user()->isHR())
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee *</label>
                                <select name="employee_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->full_name }} ({{ $emp->employee_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-4 pt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="emergency_leave" value="1" 
                                               id="emergency_leave">
                                        <label class="form-check-label" for="emergency_leave">
                                            Emergency Leave
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <input type="hidden" name="employee_id" value="{{ auth()->user()->employee_id ?? $employee->id }}">
                        @endif

                        <!-- Leave Type -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Leave Type *</label>
                                <select name="leave_type" class="form-control" required id="leaveType">
                                    <option value="">Select Leave Type</option>
                                    <option value="sick" {{ old('leave_type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                                    <option value="casual" {{ old('leave_type') == 'casual' ? 'selected' : '' }}>Casual Leave</option>
                                    <option value="annual" {{ old('leave_type') == 'annual' ? 'selected' : '' }}>Annual Leave</option>
                                    <option value="maternity" {{ old('leave_type') == 'maternity' ? 'selected' : '' }}>Maternity Leave</option>
                                    <option value="paternity" {{ old('leave_type') == 'paternity' ? 'selected' : '' }}>Paternity Leave</option>
                                    <option value="bereavement" {{ old('leave_type') == 'bereavement' ? 'selected' : '' }}>Bereavement Leave</option>
                                    <option value="unpaid" {{ old('leave_type') == 'unpaid' ? 'selected' : '' }}>Unpaid Leave</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Medical Certificate Required</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="medical_certificate" value="1" 
                                           id="medical_certificate" {{ old('medical_certificate') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="medical_certificate">
                                        I will provide medical certificate
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Date *</label>
                                <input type="date" name="start_date" class="form-control" 
                                       value="{{ old('start_date') }}" required id="startDate">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date *</label>
                                <input type="date" name="end_date" class="form-control" 
                                       value="{{ old('end_date') }}" required id="endDate">
                            </div>
                        </div>

                        <!-- Half Day Options -->
                        <div class="row mb-3" id="halfDaySection" style="display: none;">
                            <div class="col-md-6">
                                <label class="form-label">Half Day</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_half_day" value="1" 
                                           id="is_half_day" {{ old('is_half_day') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_half_day">
                                        This is a half day leave
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6" id="halfDayPeriod" style="display: none;">
                                <label class="form-label">Half Day Period</label>
                                <select name="half_day_period" class="form-control">
                                    <option value="first_half" {{ old('half_day_period') == 'first_half' ? 'selected' : '' }}>First Half</option>
                                    <option value="second_half" {{ old('half_day_period') == 'second_half' ? 'selected' : '' }}>Second Half</option>
                                </select>
                            </div>
                        </div>

                        <!-- Leave Balance Preview -->
                        <div class="alert alert-info" id="balancePreview" style="display: none;">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="balanceText"></span>
                        </div>

                        <!-- Reason -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Reason for Leave *</label>
                                <textarea name="reason" class="form-control" rows="4" 
                                          placeholder="Please provide details about your leave..." required>{{ old('reason') }}</textarea>
                            </div>
                        </div>

                        <!-- Contact During Leave -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Contact Number During Leave</label>
                                <input type="text" name="contact_number" class="form-control" 
                                       value="{{ old('contact_number', auth()->user()->employee->mobile_number ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alternate Contact Person</label>
                                <input type="text" name="alternate_contact" class="form-control" 
                                       value="{{ old('alternate_contact') }}">
                            </div>
                        </div>

                        <!-- Handover Notes -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Work Handover Notes (Optional)</label>
                                <textarea name="handover_notes" class="form-control" rows="3" 
                                          placeholder="Any pending work or handover instructions...">{{ old('handover_notes') }}</textarea>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Leave Application
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const leaveType = document.getElementById('leaveType');
    const halfDaySection = document.getElementById('halfDaySection');
    const isHalfDay = document.getElementById('is_half_day');
    const halfDayPeriod = document.getElementById('halfDayPeriod');
    const balancePreview = document.getElementById('balancePreview');
    const balanceText = document.getElementById('balanceText');

    // Set min date to today
    const today = new Date().toISOString().split('T')[0];
    startDate.min = today;
    endDate.min = today;

    // Start date change handler
    startDate.addEventListener('change', function() {
        endDate.min = this.value;
        if (endDate.value && endDate.value < this.value) {
            endDate.value = this.value;
        }
        calculateDays();
        checkLeaveBalance();
    });

    // End date change handler
    endDate.addEventListener('change', function() {
        calculateDays();
        checkLeaveBalance();
    });

    // Half day checkbox handler
    isHalfDay.addEventListener('change', function() {
        halfDayPeriod.style.display = this.checked ? 'block' : 'none';
        calculateDays();
    });

    // Leave type change handler
    leaveType.addEventListener('change', function() {
        const type = this.value;
        // Show half day option for certain leave types
        if (['sick', 'casual'].includes(type)) {
            halfDaySection.style.display = 'block';
        } else {
            halfDaySection.style.display = 'none';
            isHalfDay.checked = false;
            halfDayPeriod.style.display = 'none';
        }
        checkLeaveBalance();
    });

    // Calculate total days
    function calculateDays() {
        if (startDate.value && endDate.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            // Adjust for half day
            const totalDays = isHalfDay.checked ? diffDays - 0.5 : diffDays;
            
            // Show days count in a tooltip or display
            console.log(`Total days: ${totalDays}`);
        }
    }

    // Check leave balance via AJAX
    function checkLeaveBalance() {
        if (!leaveType.value || !startDate.value || !endDate.value) return;

        const employeeId = document.querySelector('select[name="employee_id"]')?.value || 
                          document.querySelector('input[name="employee_id"]')?.value;

        if (!employeeId) return;

        fetch(`/leaves/check-balance?employee_id=${employeeId}&leave_type=${leaveType.value}&start_date=${startDate.value}&end_date=${endDate.value}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    balancePreview.style.display = 'block';
                    balanceText.innerHTML = `
                        <strong>${data.leave_type} Leave Balance:</strong> ${data.available_days} days available.
                        Requested: ${data.requested_days} days. 
                        ${data.can_apply ? 
                            '<span class="text-success">You can apply for this leave.</span>' : 
                            '<span class="text-danger">Insufficient balance!</span>'}
                    `;
                    
                    if (!data.can_apply) {
                        balancePreview.className = 'alert alert-danger';
                    } else {
                        balancePreview.className = 'alert alert-info';
                    }
                }
            });
    }

    // Initialize
    calculateDays();
    if (leaveType.value) {
        leaveType.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection