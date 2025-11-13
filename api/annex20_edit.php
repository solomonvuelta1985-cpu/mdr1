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
    $stmt = db_query("SELECT * FROM annex20_assistance WHERE id = ?", [$record_id]);
} else {
    $stmt = db_query("SELECT * FROM annex20_assistance WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
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
    'annex20_edit_form_access',
    "Opened edit form for assistance record #{$record_id}"
);

// Predefined options
$clusters = [
    'Food',
    'WASH',
    'Shelter',
    'Health',
    'Protection',
    'Education',
    'Nutrition',
    'Camp Management',
    'Emergency Telecommunications',
    'Logistics'
];

$assistance_types = [
    'Food packs',
    'Hygiene kits',
    'Water containers',
    'Temporary shelter materials',
    'Medicines',
    'Blankets',
    'Sleeping mats',
    'Cooking utensils',
    'Mosquito nets',
    'Water purification tablets',
    'Other'
];
?>

<form id="editRecordForm" method="POST" action="../api/annex20_update.php">
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

        <!-- Assistance Classification -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-hands-helping me-2"></i>Assistance Classification</h6>
        </div>

        <div class="col-md-6">
            <label for="edit_cluster" class="form-label">Cluster</label>
            <select class="form-select" id="edit_cluster" name="cluster" required>
                <option value="">Select Cluster</option>
                <?php foreach ($clusters as $cluster): ?>
                    <option value="<?php echo htmlspecialchars($cluster); ?>" <?php echo $record['cluster'] === $cluster ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cluster); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="edit_type" class="form-label">Type</label>
            <select class="form-select" id="edit_type" name="type" required>
                <option value="">Select Type</option>
                <?php foreach ($assistance_types as $type): ?>
                    <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $record['type'] === $type ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Assistance Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-box-open me-2"></i>Assistance Details</h6>
        </div>

        <div class="col-md-4">
            <label for="edit_quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="edit_quantity" name="quantity" value="<?php echo $record['quantity']; ?>" min="0" max="1000000" required>
        </div>

        <div class="col-md-4">
            <label for="edit_unit" class="form-label">Unit</label>
            <input type="text" class="form-control" id="edit_unit" name="unit" value="<?php echo htmlspecialchars($record['unit']); ?>" maxlength="50" required>
        </div>

        <div class="col-md-4">
            <label for="edit_cost_per_unit" class="form-label">Cost per Unit (₱)</label>
            <input type="number" class="form-control" id="edit_cost_per_unit" name="cost_per_unit" value="<?php echo number_format($record['cost_per_unit'], 2, '.', ''); ?>" min="0" step="0.01" required>
        </div>

        <div class="col-md-12">
            <label for="edit_amount" class="form-label">Total Amount (₱)</label>
            <input type="number" class="form-control bg-light" id="edit_amount" name="amount" value="<?php echo number_format($record['amount'], 2, '.', ''); ?>" step="0.01" readonly>
        </div>

        <!-- Additional Information -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
        </div>

        <div class="col-md-12">
            <label for="edit_remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="edit_remarks" name="remarks" rows="3" maxlength="500"><?php echo htmlspecialchars($record['remarks'] ?? ''); ?></textarea>
            <small class="text-muted">Source, recipients, delivery details, etc.</small>
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
// Calculate total amount
function calculateEditAmount() {
    const quantity = parseFloat(document.getElementById('edit_quantity').value) || 0;
    const costPerUnit = parseFloat(document.getElementById('edit_cost_per_unit').value) || 0;
    const amount = quantity * costPerUnit;
    document.getElementById('edit_amount').value = amount.toFixed(2);
}

// Initialize immediately (not waiting for DOMContentLoaded since this is loaded via AJAX)
(function() {
    // Wait a tiny bit for the form to be fully inserted into DOM
    setTimeout(function() {
        // Calculate initial amount
        calculateEditAmount();

        // Add input event listeners for auto-calculation
        document.getElementById('edit_quantity').addEventListener('input', calculateEditAmount);
        document.getElementById('edit_cost_per_unit').addEventListener('input', calculateEditAmount);
    }, 100);
})();

// Handle form submission
document.getElementById('editRecordForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;

    // IMPORTANT: Force recalculation and get values directly from inputs
    const quantity = parseFloat(document.getElementById('edit_quantity').value) || 0;
    const costPerUnit = parseFloat(document.getElementById('edit_cost_per_unit').value) || 0;
    const calculatedAmount = quantity * costPerUnit;

    // Set the amount field with calculated value
    document.getElementById('edit_amount').value = calculatedAmount.toFixed(2);

    // Create FormData and manually set the amount to ensure accuracy
    const formData = new FormData(document.getElementById('editRecordForm'));
    formData.set('amount', calculatedAmount.toFixed(2));

    fetch('../api/annex20_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Check if redirect URL is provided
            if (data.redirect) {
                // Redirect to annex20_records.php
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
