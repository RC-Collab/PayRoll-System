<!-- Fine Modal -->
<div class="modal fade" id="fineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Apply Fine
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="fineForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="salary_id" id="fine_salary_id">
                    <input type="hidden" name="employee_name" id="fine_employee_name">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Employee</label>
                        <p class="form-control-plaintext" id="display_employee_name"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fine Amount (रु) *</label>
                        <div class="input-group">
                            <span class="input-group-text">रु</span>
                            <input type="number" name="fine_amount" id="fine_amount" 
                                   class="form-control form-control-lg" step="0.01" min="0" 
                                   placeholder="Enter amount" required>
                        </div>
                        <small class="text-muted">This amount will be deducted from salary</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason *</label>
                        <textarea name="fine_reason" id="fine_reason" class="form-control" 
                                  rows="3" placeholder="e.g., Late attendance, Policy violation, etc." 
                                  required></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Fine will be added to total deductions. Net salary will be recalculated automatically.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="submitFine">
                        <i class="fas fa-check me-2"></i>Apply Fine
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bonus Modal -->
<div class="modal fade" id="bonusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-gift me-2"></i>
                    Apply Bonus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bonusForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="salary_id" id="bonus_salary_id">
                    <input type="hidden" name="employee_name" id="bonus_employee_name">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Employee</label>
                        <p class="form-control-plaintext" id="display_bonus_employee"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Bonus Amount (रु) *</label>
                        <div class="input-group">
                            <span class="input-group-text">रु</span>
                            <input type="number" name="bonus_amount" id="bonus_amount" 
                                   class="form-control form-control-lg" step="0.01" min="0" 
                                   placeholder="Enter amount" required>
                        </div>
                        <small class="text-muted">This amount will be added to salary</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason *</label>
                        <textarea name="bonus_reason" id="bonus_reason" class="form-control" 
                                  rows="3" placeholder="e.g., Performance bonus, Festival bonus, etc." 
                                  required></textarea>
                    </div>
                    
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle me-2"></i>
                        Bonus will be added to total allowances. Net salary will be recalculated automatically.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="submitBonus">
                        <i class="fas fa-check me-2"></i>Apply Bonus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Individual Fine
document.getElementById('fineForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitFine');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Applying...';
    
    fetch('/salary/apply-fine', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            salary_id: document.getElementById('fine_salary_id').value,
            fine_amount: document.getElementById('fine_amount').value,
            fine_reason: document.getElementById('fine_reason').value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            toastr.error(data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Apply Fine';
        }
    })
    .catch(error => {
        toastr.error('Error applying fine');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Apply Fine';
    });
});

// Individual Bonus
document.getElementById('bonusForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBonus');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Applying...';
    
    fetch('/salary/apply-bonus', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            salary_id: document.getElementById('bonus_salary_id').value,
            bonus_amount: document.getElementById('bonus_amount').value,
            bonus_reason: document.getElementById('bonus_reason').value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            toastr.error(data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Apply Bonus';
        }
    })
    .catch(error => {
        toastr.error('Error applying bonus');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Apply Bonus';
    });
});

document.addEventListener('DOMContentLoaded', function() {
    });
</script>
@endpush