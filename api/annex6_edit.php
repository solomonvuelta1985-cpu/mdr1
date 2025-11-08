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
    $stmt = db_query("SELECT * FROM annex6_infrastructure_damage WHERE id = ?", [$record_id]);
} else {
    $stmt = db_query("SELECT * FROM annex6_infrastructure_damage WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
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
    'annex6_edit_form_access',
    "Opened edit form for record #{$record_id}"
);
?>

<form id="editRecordForm" method="POST" action="../api/annex6_update.php">
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

        <!-- Infrastructure Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-building me-2"></i>Infrastructure Details</h6>
        </div>

        <div class="col-md-6 col-lg-3">
            <label for="edit_type" class="form-label">Type</label>
            <select class="form-select" id="edit_type" name="type" required>
                <option value="">Select Type</option>
                <option value="Road" <?php echo $record['type'] === 'Road' ? 'selected' : ''; ?>>Road</option>
                <option value="Bridge" <?php echo $record['type'] === 'Bridge' ? 'selected' : ''; ?>>Bridge</option>
                <option value="Flood Control" <?php echo $record['type'] === 'Flood Control' ? 'selected' : ''; ?>>Flood Control</option>
                <option value="Government Facilities" <?php echo $record['type'] === 'Government Facilities' ? 'selected' : ''; ?>>Government Facilities</option>
                <option value="Health Facilities" <?php echo $record['type'] === 'Health Facilities' ? 'selected' : ''; ?>>Health Facilities</option>
                <option value="Schools" <?php echo $record['type'] === 'Schools' ? 'selected' : ''; ?>>Schools</option>
                <option value="Cultural Heritage" <?php echo $record['type'] === 'Cultural Heritage' ? 'selected' : ''; ?>>Cultural Heritage</option>
                <option value="Utility Service Facilities" <?php echo $record['type'] === 'Utility Service Facilities' ? 'selected' : ''; ?>>Utility Service Facilities</option>
                <option value="Private" <?php echo $record['type'] === 'Private' ? 'selected' : ''; ?>>Private</option>
            </select>
        </div>

        <div class="col-md-6 col-lg-3">
            <label for="edit_classification" class="form-label">Classification</label>
            <select class="form-select" id="edit_classification" name="classification">
                <option value="">Select Classification</option>
                <option value="National" <?php echo $record['classification'] === 'National' ? 'selected' : ''; ?>>National</option>
                <option value="Local" <?php echo $record['classification'] === 'Local' ? 'selected' : ''; ?>>Local</option>
            </select>
        </div>

        <div class="col-md-12 col-lg-6">
            <label for="edit_name" class="form-label">Name of Infrastructure</label>
            <input type="text" class="form-control" id="edit_name" name="name" value="<?php echo htmlspecialchars($record['name'] ?? ''); ?>" maxlength="255">
        </div>

        <!-- Damage Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Damage Details</h6>
        </div>

        <div class="col-md-4 col-lg-2">
            <label for="edit_totally_damaged" class="form-label">Totally Damaged</label>
            <input type="number" class="form-control" id="edit_totally_damaged" name="totally_damaged" value="<?php echo $record['totally_damaged']; ?>" min="0" onchange="calculateEditTotal()">
        </div>

        <div class="col-md-4 col-lg-2">
            <label for="edit_partially_damaged" class="form-label">Partially Damaged</label>
            <input type="number" class="form-control" id="edit_partially_damaged" name="partially_damaged" value="<?php echo $record['partially_damaged']; ?>" min="0" onchange="calculateEditTotal()">
        </div>

        <div class="col-md-4 col-lg-2">
            <label for="edit_total_damaged" class="form-label">Total Damaged</label>
            <input type="number" class="form-control" id="edit_total_damaged" name="total_damaged" value="<?php echo $record['total_damaged']; ?>" readonly>
        </div>

        <div class="col-md-4 col-lg-2">
            <label for="edit_unit" class="form-label">Unit</label>
            <input type="text" class="form-control" id="edit_unit" name="unit" value="<?php echo htmlspecialchars($record['unit'] ?? ''); ?>" maxlength="50">
        </div>

        <div class="col-md-4 col-lg-2">
            <label for="edit_quantity" class="form-label">Quantity</label>
            <input type="number" step="0.01" class="form-control" id="edit_quantity" name="quantity" value="<?php echo $record['quantity']; ?>" min="0">
        </div>

        <div class="col-md-4 col-lg-2">
            <label for="edit_cost" class="form-label">Cost</label>
            <input type="text" class="form-control" id="edit_cost" name="cost" value="<?php echo htmlspecialchars($record['cost'] ?? ''); ?>" maxlength="100">
        </div>

        <!-- Additional Information -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
        </div>

        <div class="col-md-12">
            <label for="edit_remarks" class="form-label">Remarks</label>
            <input type="text" class="form-control" id="edit_remarks" name="remarks" value="<?php echo htmlspecialchars($record['remarks'] ?? ''); ?>" maxlength="500">
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
// Calculate total damaged
function calculateEditTotal() {
    const totally = parseInt(document.getElementById('edit_totally_damaged').value) || 0;
    const partially = parseInt(document.getElementById('edit_partially_damaged').value) || 0;
    document.getElementById('edit_total_damaged').value = totally + partially;
}

// Handle form submission
document.getElementById('editRecordForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;

    fetch('../api/annex6_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
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
