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
    $stmt = db_query("SELECT * FROM annex7_other_assets_damage WHERE id = ?", [$record_id]);
} else {
    $stmt = db_query("SELECT * FROM annex7_other_assets_damage WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
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
    'annex7_edit_form_access',
    "Opened edit form for record #{$record_id}"
);
?>

<form id="editRecordForm" method="POST" action="../api/annex7_update.php">
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

        <!-- Asset Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-box me-2"></i>Asset Details</h6>
        </div>

        <div class="col-md-6 col-lg-4">
            <label for="edit_type" class="form-label">Classification</label>
            <select class="form-select" id="edit_type" name="type" required>
                <option value="">Select Classification</option>
                <option value="Vehicles" <?php echo $record['classification'] === 'Vehicles' ? 'selected' : ''; ?>>Vehicles</option>
                <option value="Aircraft" <?php echo $record['classification'] === 'Aircraft' ? 'selected' : ''; ?>>Aircraft</option>
                <option value="Weapons / Ammunition" <?php echo $record['classification'] === 'Weapons / Ammunition' ? 'selected' : ''; ?>>Weapons / Ammunition</option>
                <option value="Medicines" <?php echo $record['classification'] === 'Medicines' ? 'selected' : ''; ?>>Medicines</option>
                <option value="Hospital Equipment" <?php echo $record['classification'] === 'Hospital Equipment' ? 'selected' : ''; ?>>Hospital Equipment</option>
                <option value="IEC Materials" <?php echo $record['classification'] === 'IEC Materials' ? 'selected' : ''; ?>>IEC Materials</option>
                <option value="Furniture" <?php echo $record['classification'] === 'Furniture' ? 'selected' : ''; ?>>Furniture</option>
                <option value="Educational Materials" <?php echo $record['classification'] === 'Educational Materials' ? 'selected' : ''; ?>>Educational Materials</option>
                <option value="Electronics / ICT Equipment" <?php echo $record['classification'] === 'Electronics / ICT Equipment' ? 'selected' : ''; ?>>Electronics / ICT Equipment</option>
                <option value="Other" <?php echo $record['classification'] === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>

        <div class="col-md-12 col-lg-8">
            <label for="edit_name" class="form-label">Particulars / Name of Asset</label>
            <input type="text" class="form-control" id="edit_name" name="name" value="<?php echo htmlspecialchars($record['particulars'] ?? ''); ?>" maxlength="500">
        </div>

        <!-- Quantity and Cost Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-calculator me-2"></i>Quantity and Cost Details</h6>
        </div>

        <div class="col-md-4 col-lg-3">
            <label for="edit_unit" class="form-label">Unit</label>
            <input type="text" class="form-control" id="edit_unit" name="unit" value="<?php echo htmlspecialchars($record['unit'] ?? ''); ?>" maxlength="50" placeholder="e.g., piece, set, unit">
        </div>

        <div class="col-md-4 col-lg-3">
            <label for="edit_quantity" class="form-label">Quantity</label>
            <input type="number" step="0.01" class="form-control" id="edit_quantity" name="quantity" value="<?php echo $record['quantity']; ?>" min="0">
        </div>

        <div class="col-md-4 col-lg-6">
            <label for="edit_cost" class="form-label">Estimated Cost of Damage</label>
            <input type="text" class="form-control" id="edit_cost" name="cost" value="<?php echo htmlspecialchars($record['cost'] ?? ''); ?>" maxlength="100" placeholder="Enter estimated cost">
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
// Handle form submission
document.getElementById('editRecordForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;

    fetch('../api/annex7_update.php', {
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
