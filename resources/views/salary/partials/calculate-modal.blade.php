<!-- Calculate Salary Modal -->
<div class="modal fade" id="calculateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-calculator me-2"></i>
                    Calculate Salary
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('salary.calculate') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Month</label>
                        <input type="month" name="month" class="form-control" 
                               value="{{ $currentMonth }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Calculate For</label>
                        <select name="calculate_for" class="form-control" id="calculateFor" required>
                            <option value="all">All Employees</option>
                            <option value="department">Specific Department</option>
                            <option value="employee">Specific Employee</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="departmentSection" style="display: none;">
                        <label class="form-label fw-bold">Department</label>
                        <select name="department_id" class="form-control">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3" id="employeeSection" style="display: none;">
                        <label class="form-label fw-bold">Employee</label>
                        <select name="employee_id" class="form-control">
                            <option value="">Select Employee</option>
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Per-day Rate:</strong> Basic Salary ÷ 30 days<br>
                        <small>Absent days will be deducted at this rate</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calculator me-2"></i>Calculate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('calculateFor')?.addEventListener('change', function() {
    const val = this.value;
    document.getElementById('departmentSection').style.display = val === 'department' ? 'block' : 'none';
    document.getElementById('employeeSection').style.display = val === 'employee' ? 'block' : 'none';
});
</script>