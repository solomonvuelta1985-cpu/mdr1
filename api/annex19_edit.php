<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$record_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$record_id) {
    echo '<div class="alert alert-danger">Invalid record ID</div>';
    exit;
}

// Fetch record
$is_admin = ($_SESSION['user_role'] === 'admin');

if ($is_admin) {
    $stmt = db_query("SELECT * FROM annex19_families_assisted WHERE id = ?", [$record_id]);
} else {
    $stmt = db_query("SELECT * FROM annex19_families_assisted WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
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
    'annex19_edit_form_access',
    "Opened edit form for record #{$record_id}"
);
?>

<form id="editRecordForm" method="POST" action="../api/annex19_update.php">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
    
    <div class="row g-3">
        <!-- Location Details -->
        <div class="col-12">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-map-marker-alt me-2"></i>Location Information</h6>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label for="edit_region" class="form-label">Region</label>
            <input type="text" class="form-control" id="edit_region" name="region" value="<?php echo htmlspecialchars($record['region']); ?>" required maxlength="100">
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label for="edit_province" class="form-label">Province</label>
            <input type="text" class="form-control" id="edit_province" name="province" value="<?php echo htmlspecialchars($record['province']); ?>" required maxlength="100">
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label for="edit_city" class="form-label">City/Municipality</label>
            <input type="text" class="form-control" id="edit_city" name="city" value="<?php echo htmlspecialchars($record['city']); ?>" required maxlength="100">
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label for="edit_barangay" class="form-label">Barangay</label>
            <input type="text" class="form-control bg-light" id="edit_barangay" name="barangay" value="<?php echo htmlspecialchars($record['barangay']); ?>" readonly required>
        </div>
        
        <!-- Assistance Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-hands-helping me-2"></i>Assistance Details</h6>
        </div>
        
        <div class="col-md-6">
            <label for="edit_families_requiring_assistance" class="form-label">No. of Families Requiring Assistance</label>
            <input type="number" class="form-control" id="edit_families_requiring_assistance" name="families_requiring_assistance" value="<?php echo $record['families_requiring_assistance']; ?>" min="0" max="100000" required onchange="calculateEditPercentage()">
        </div>
        
        <div class="col-md-6">
            <label for="edit_families_assisted" class="form-label">No. of Families Assisted</label>
            <input type="number" class="form-control" id="edit_families_assisted" name="families_assisted" value="<?php echo $record['families_assisted']; ?>" min="0" max="100000" required onchange="calculateEditPercentage()">
        </div>
        
        <div class="col-md-6">
            <label for="edit_percentage_assisted" class="form-label">% of Families Assisted</label>
            <input type="number" class="form-control bg-light" id="edit_percentage_assisted" name="percentage_assisted" value="<?php echo $record['percentage_assisted']; ?>" readonly step="0.01">
        </div>
        
        <div class="col-md-6">
            <label for="edit_assistance_type" class="form-label">Type of Assistance</label>
            <select class="form-select" id="edit_assistance_type" name="assistance_type" required>
                <option value="">Select Type</option>
                <option value="Food Packs" <?php echo $record['assistance_type'] === 'Food Packs' ? 'selected' : ''; ?>>Food Packs</option>
                <option value="Hygiene Kits" <?php echo $record['assistance_type'] === 'Hygiene Kits' ? 'selected' : ''; ?>>Hygiene Kits</option>
                <option value="Shelter Materials" <?php echo $record['assistance_type'] === 'Shelter Materials' ? 'selected' : ''; ?>>Shelter Materials</option>
                <option value="Medical Aid" <?php echo $record['assistance_type'] === 'Medical Aid' ? 'selected' : ''; ?>>Medical Aid</option>
                <option value="Cash Assistance" <?php echo $record['assistance_type'] === 'Cash Assistance' ? 'selected' : ''; ?>>Cash Assistance</option>
                <option value="Other" <?php echo $record['assistance_type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label for="edit_assistance_provider" class="form-label">Assistance Provider</label>
            <select class="form-select" id="edit_assistance_provider" name="assistance_provider" required>
                <option value="">Select Provider</option>
                <option value="LGU" <?php echo $record['assistance_provider'] === 'LGU' ? 'selected' : ''; ?>>LGU</option>
                <option value="DSWD" <?php echo $record['assistance_provider'] === 'DSWD' ? 'selected' : ''; ?>>DSWD</option>
                <option value="NGO" <?php echo $record['assistance_provider'] === 'NGO' ? 'selected' : ''; ?>>NGO</option>
                <option value="Private Sector" <?php echo $record['assistance_provider'] === 'Private Sector' ? 'selected' : ''; ?>>Private Sector</option>
                <option value="Other" <?php echo $record['assistance_provider'] === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        
        <!-- Additional Information -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
        </div>
        
        <div class="col-12">
            <label for="edit_remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="edit_remarks" name="remarks" placeholder="Enter remarks (e.g., assistance gaps, reasons, target timeline)" maxlength="1000"><?php echo htmlspecialchars($record['remarks'] ?? ''); ?></textarea>
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
// Calculate percentage function
function calculateEditPercentage() {
    const familiesRequiring = parseFloat(document.getElementById('edit_families_requiring_assistance').value) || 0;
    const familiesAssisted = parseFloat(document.getElementById('edit_families_assisted').value) || 0;
    const percentageField = document.getElementById('edit_percentage_assisted');
    
    if (familiesRequiring > 0) {
        const percentage = (familiesAssisted / familiesRequiring) * 100;
        percentageField.value = percentage.toFixed(2);
    } else {
        percentageField.value = 0;
    }
}

// Initialize percentage calculation
document.addEventListener('DOMContentLoaded', function() {
    calculateEditPercentage();
});

// Handle form submission
document.getElementById('editRecordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;
    
    fetch('../api/annex19_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Check if redirect URL is provided
            if (data.redirect) {
                // Redirect to annex19_records.php
                window.location.href = data.redirect;
            } else {
                // Fallback: Show success message and reload
                alert('✅ Record updated successfully!');
                location.reload();
            }
        } else {
            alert('❌ Error: ' + (data.message || 'Failed to update record'));
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>