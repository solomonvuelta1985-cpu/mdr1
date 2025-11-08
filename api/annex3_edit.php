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
    $stmt = db_query("SELECT * FROM annex3_casualties WHERE id = ?", [$record_id]);
} else {
    $stmt = db_query("SELECT * FROM annex3_casualties WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
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
    'annex3_edit_form_access',
    "Opened edit form for record #{$record_id}"
);
?>

<form id="editRecordForm" method="POST" action="../api/annex3_update.php">
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
            <input type="text" class="form-control" id="edit_barangay" name="barangay" value="<?php echo htmlspecialchars($record['barangay']); ?>" required maxlength="100">
        </div>
        
        <!-- Personal Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-user me-2"></i>Personal Details</h6>
        </div>
        
        <div class="col-md-6 col-lg-2">
            <label for="edit_category" class="form-label">Category</label>
            <select class="form-select" id="edit_category" name="category" required>
                <option value="">Select</option>
                <option value="Dead" <?php echo $record['category'] === 'Dead' ? 'selected' : ''; ?>>Dead</option>
                <option value="Injured" <?php echo $record['category'] === 'Injured' ? 'selected' : ''; ?>>Injured</option>
                <option value="Ill" <?php echo $record['category'] === 'Ill' ? 'selected' : ''; ?>>Ill</option>
                <option value="Missing" <?php echo $record['category'] === 'Missing' ? 'selected' : ''; ?>>Missing</option>
            </select>
        </div>
        
        <div class="col-md-6 col-lg-2">
            <label for="edit_surname" class="form-label">Surname</label>
            <input type="text" class="form-control" id="edit_surname" name="surname" value="<?php echo htmlspecialchars($record['surname']); ?>" required maxlength="100">
        </div>
        
        <div class="col-md-6 col-lg-2">
            <label for="edit_first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="edit_first_name" name="first_name" value="<?php echo htmlspecialchars($record['first_name']); ?>" required maxlength="100">
        </div>
        
        <div class="col-md-6 col-lg-2">
            <label for="edit_middle_name" class="form-label">Middle Name</label>
            <input type="text" class="form-control" id="edit_middle_name" name="middle_name" value="<?php echo htmlspecialchars($record['middle_name'] ?? ''); ?>" maxlength="100">
        </div>
        
        <div class="col-md-6 col-lg-2">
            <label for="edit_age" class="form-label">Age</label>
            <input type="number" class="form-control" id="edit_age" name="age" value="<?php echo $record['age']; ?>" min="0" max="120" required>
        </div>
        
        <div class="col-md-6 col-lg-2">
            <label for="edit_sex" class="form-label">Sex</label>
            <select class="form-select" id="edit_sex" name="sex" required>
                <option value="">Select</option>
                <option value="Male" <?php echo $record['sex'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo $record['sex'] === 'Female' ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>
        
        <!-- Incident Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Incident Details</h6>
        </div>
        
        <div class="col-md-6">
            <label for="edit_address" class="form-label">Address</label>
            <input type="text" class="form-control" id="edit_address" name="address" value="<?php echo htmlspecialchars($record['address']); ?>" required maxlength="255">
        </div>
        
        <div class="col-md-6">
            <label for="edit_cause" class="form-label">Cause</label>
            <textarea class="form-control" id="edit_cause" name="cause" required maxlength="500"><?php echo htmlspecialchars($record['cause']); ?></textarea>
        </div>
        
        <div class="col-md-6">
            <label for="edit_remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="edit_remarks" name="remarks" maxlength="500"><?php echo htmlspecialchars($record['remarks'] ?? ''); ?></textarea>
        </div>
        
        <div class="col-md-3">
            <label for="edit_source" class="form-label">Source of Data</label>
            <select class="form-select" id="edit_source" name="source" required>
                <option value="">Select</option>
                <option value="MDM Cluster" <?php echo $record['source'] === 'MDM Cluster' ? 'selected' : ''; ?>>MDM Cluster</option>
                <option value="LGU" <?php echo $record['source'] === 'LGU' ? 'selected' : ''; ?>>LGU</option>
                <option value="DOH" <?php echo $record['source'] === 'DOH' ? 'selected' : ''; ?>>DOH</option>
                <option value="PNP" <?php echo $record['source'] === 'PNP' ? 'selected' : ''; ?>>PNP</option>
                <option value="BFAR" <?php echo $record['source'] === 'BFAR' ? 'selected' : ''; ?>>BFAR</option>
                <option value="Other" <?php echo $record['source'] === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        
        <div class="col-md-3">
            <label for="edit_validated" class="form-label">Validated?</label>
            <select class="form-select" id="edit_validated" name="validated" required>
                <option value="">Select</option>
                <option value="Yes" <?php echo $record['validated'] === 'Yes' ? 'selected' : ''; ?>>Yes</option>
                <option value="No" <?php echo $record['validated'] === 'No' ? 'selected' : ''; ?>>No</option>
            </select>
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
    
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;
    
    fetch('../api/annex3_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Record updated successfully!');
            location.reload();
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