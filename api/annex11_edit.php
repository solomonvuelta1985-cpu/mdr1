<?php
/**
 * API: Load Edit Form for Communication Line Record
 * Returns HTML form with pre-populated data
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// Get record ID
$record_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$record_id) {
    echo '<div class="alert alert-danger">Invalid record ID</div>';
    exit;
}

// Fetch record
$is_admin = ($_SESSION['user_role'] === 'admin');

if ($is_admin) {
    $stmt = db_query("SELECT * FROM annex11_communication_lines WHERE id = ?", [$record_id]);
} else {
    $stmt = db_query("SELECT * FROM annex11_communication_lines WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
}

$record = $stmt->fetch();

if (!$record) {
    echo '<div class="alert alert-danger">Record not found or access denied</div>';
    exit;
}

// Generate CSRF token
$csrf_token = generate_token();

// Log the edit form access
log_audit_action(
    $_SESSION['user_id'],
    'annex11_edit_form_access',
    "Opened edit form for record #{$record_id}"
);

// Convert datetime format for datetime-local input
$interruption_date_formatted = date('Y-m-d\TH:i', strtotime($record['interruption_date']));
$restored_date_formatted = !empty($record['restored_date']) ? date('Y-m-d\TH:i', strtotime($record['restored_date'])) : '';
?>

<form id="editRecordForm" method="POST" action="../api/annex11_update.php">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
    
    <div class="row g-3">
        <!-- Location Details -->
        <div class="col-12">
            <h6 class="text-primary border-bottom pb-2 mb-3">
                <i class="fas fa-map-marker-alt me-2"></i>Location Information
            </h6>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label for="edit_region" class="form-label">Region</label>
            <input type="text" class="form-control" id="edit_region" name="region" 
                   value="<?php echo htmlspecialchars($record['region']); ?>" required maxlength="100">
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label for="edit_province" class="form-label">Province</label>
            <input type="text" class="form-control" id="edit_province" name="province" 
                   value="<?php echo htmlspecialchars($record['province']); ?>" required maxlength="100">
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label for="edit_city" class="form-label">City/Municipality</label>
            <input type="text" class="form-control" id="edit_city" name="city" 
                   value="<?php echo htmlspecialchars($record['city']); ?>" required maxlength="100">
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label for="edit_barangay" class="form-label">Barangay</label>
            <input type="text" class="form-control bg-light" id="edit_barangay" name="barangay" 
                   value="<?php echo htmlspecialchars($record['barangay']); ?>" readonly required>
        </div>
        
        <!-- Communication Service Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3">
                <i class="fas fa-signal me-2"></i>Communication Service Details
            </h6>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label for="edit_telecom" class="form-label">Telecom Provider</label>
            <select class="form-select" id="edit_telecom" name="telecom_provider" required>
                <option value="">Select Provider</option>
                <option value="PLDT" <?php echo $record['telecom_provider'] === 'PLDT' ? 'selected' : ''; ?>>PLDT</option>
                <option value="Globe" <?php echo $record['telecom_provider'] === 'Globe' ? 'selected' : ''; ?>>Globe</option>
                <option value="Smart" <?php echo $record['telecom_provider'] === 'Smart' ? 'selected' : ''; ?>>Smart</option>
                <option value="DITO" <?php echo $record['telecom_provider'] === 'DITO' ? 'selected' : ''; ?>>DITO</option>
                <option value="Other" <?php echo $record['telecom_provider'] === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label for="edit_interruption_date" class="form-label">Date/Time of Interruption</label>
            <input type="datetime-local" class="form-control" id="edit_interruption_date" 
                   name="interruption_date" value="<?php echo $interruption_date_formatted; ?>" required>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label for="edit_restored_date" class="form-label">Date/Time Restored</label>
            <input type="datetime-local" class="form-control" id="edit_restored_date" 
                   name="restored_date" value="<?php echo $restored_date_formatted; ?>">
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label for="edit_remarks" class="form-label">Remarks</label>
            <input type="text" class="form-control" id="edit_remarks" name="remarks" 
                   value="<?php echo htmlspecialchars($record['remarks'] ?? ''); ?>" 
                   placeholder="Enter remarks" maxlength="500">
        </div>
    </div>
    
    <div class="modal-footer mt-4">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save Changes
        </button>
    </div>
</form>

<script>
// Handle form submission
document.getElementById('editRecordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Validate dates
    const interruptionDate = new Date(formData.get('interruption_date'));
    const restoredDate = formData.get('restored_date') ? new Date(formData.get('restored_date')) : null;
    
    if (restoredDate && restoredDate < interruptionDate) {
        alert('⚠️ Restored date cannot be before interruption date.');
        return;
    }
    
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;
    
    fetch('../api/annex11_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Close the modal first
            const modal = bootstrap.Modal.getInstance(document.getElementById('editRecordModal'));
            if (modal) {
                modal.hide();
            }
            
            // Redirect after a short delay to ensure modal closes
            setTimeout(() => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.reload();
                }
            }, 300);
        } else {
            alert('❌ Error: ' + (data.message || 'Failed to update record'));
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        alert('❌ Error: ' + error.message);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>