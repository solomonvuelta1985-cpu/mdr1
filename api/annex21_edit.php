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
    $stmt = db_query("SELECT * FROM annex21_assistance_lgu WHERE id = ?", [$record_id]);
} else {
    $stmt = db_query("SELECT * FROM annex21_assistance_lgu WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
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
    'annex21_edit_form_access',
    "Opened edit form for record #{$record_id}"
);
?>

<form id="editRecordForm" method="POST" action="../api/annex21_update.php">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
    
    <div class="row g-3">
        <!-- Location Details -->
        <div class="col-12">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-map-marker-alt me-2"></i>Location Information</h6>
        </div>
        
        <div class="col-md-4">
            <label for="edit_region" class="form-label">Region</label>
            <input type="text" class="form-control" id="edit_region" name="region" value="<?php echo htmlspecialchars($record['region']); ?>" required maxlength="100">
        </div>
        
        <div class="col-md-4">
            <label for="edit_province" class="form-label">Province</label>
            <input type="text" class="form-control" id="edit_province" name="province" value="<?php echo htmlspecialchars($record['province']); ?>" required maxlength="100">
        </div>
        
        <div class="col-md-4">
            <label for="edit_city" class="form-label">City/Municipality</label>
            <input type="text" class="form-control" id="edit_city" name="city" value="<?php echo htmlspecialchars($record['city']); ?>" required maxlength="100">
        </div>
        
        <div class="col-12">
            <label for="edit_recipient" class="form-label">Recipient (LGU/Agency)</label>
            <input type="text" class="form-control" id="edit_recipient" name="recipient" value="<?php echo htmlspecialchars($record['recipient']); ?>" required maxlength="255">
        </div>
        
        <!-- Assistance Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-hand-holding-heart me-2"></i>Assistance Details</h6>
        </div>
        
        <div class="col-md-6">
            <label for="edit_cluster" class="form-label">Cluster</label>
            <select class="form-select" id="edit_cluster" name="cluster" required>
                <option value="">Select Cluster</option>
                <option value="Food" <?php echo $record['cluster'] === 'Food' ? 'selected' : ''; ?>>Food</option>
                <option value="WASH" <?php echo $record['cluster'] === 'WASH' ? 'selected' : ''; ?>>WASH</option>
                <option value="Shelter" <?php echo $record['cluster'] === 'Shelter' ? 'selected' : ''; ?>>Shelter</option>
                <option value="Health" <?php echo $record['cluster'] === 'Health' ? 'selected' : ''; ?>>Health</option>
                <option value="Protection" <?php echo $record['cluster'] === 'Protection' ? 'selected' : ''; ?>>Protection</option>
                <option value="Education" <?php echo $record['cluster'] === 'Education' ? 'selected' : ''; ?>>Education</option>
                <option value="Nutrition" <?php echo $record['cluster'] === 'Nutrition' ? 'selected' : ''; ?>>Nutrition</option>
                <option value="Camp Management" <?php echo $record['cluster'] === 'Camp Management' ? 'selected' : ''; ?>>Camp Management</option>
                <option value="Emergency Telecommunications" <?php echo $record['cluster'] === 'Emergency Telecommunications' ? 'selected' : ''; ?>>Emergency Telecommunications</option>
                <option value="Logistics" <?php echo $record['cluster'] === 'Logistics' ? 'selected' : ''; ?>>Logistics</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label for="edit_type" class="form-label">Type</label>
            <select class="form-select" id="edit_type" name="type" required>
                <option value="">Select Type</option>
                <option value="Food packs" <?php echo $record['type'] === 'Food packs' ? 'selected' : ''; ?>>Food packs</option>
                <option value="Hygiene kits" <?php echo $record['type'] === 'Hygiene kits' ? 'selected' : ''; ?>>Hygiene kits</option>
                <option value="Drinking water" <?php echo $record['type'] === 'Drinking water' ? 'selected' : ''; ?>>Drinking water</option>
                <option value="Sleeping kits" <?php echo $record['type'] === 'Sleeping kits' ? 'selected' : ''; ?>>Sleeping kits</option>
                <option value="Medical supplies" <?php echo $record['type'] === 'Medical supplies' ? 'selected' : ''; ?>>Medical supplies</option>
                <option value="Shelter repair kits" <?php echo $record['type'] === 'Shelter repair kits' ? 'selected' : ''; ?>>Shelter repair kits</option>
                <option value="Emergency generators" <?php echo $record['type'] === 'Emergency generators' ? 'selected' : ''; ?>>Emergency generators</option>
                <option value="Communication equipment" <?php echo $record['type'] === 'Communication equipment' ? 'selected' : ''; ?>>Communication equipment</option>
                <option value="Vehicles" <?php echo $record['type'] === 'Vehicles' ? 'selected' : ''; ?>>Vehicles</option>
                <option value="Fuel" <?php echo $record['type'] === 'Fuel' ? 'selected' : ''; ?>>Fuel</option>
                <option value="Other" <?php echo $record['type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        
        <div class="col-md-3">
            <label for="edit_quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="edit_quantity" name="quantity" value="<?php echo $record['quantity']; ?>" min="0" max="9999999" onchange="calculateEditAmount()" required>
        </div>
        
        <div class="col-md-3">
            <label for="edit_unit" class="form-label">Unit</label>
            <input type="text" class="form-control" id="edit_unit" name="unit" value="<?php echo htmlspecialchars($record['unit']); ?>" required maxlength="50">
        </div>
        
        <div class="col-md-3">
            <label for="edit_cost_per_unit" class="form-label">Cost per Unit (₱)</label>
            <input type="number" class="form-control" id="edit_cost_per_unit" name="cost_per_unit" value="<?php echo $record['cost_per_unit']; ?>" min="0" step="0.01" onchange="calculateEditAmount()" required>
        </div>
        
        <div class="col-md-3">
            <label for="edit_amount" class="form-label">Amount (₱)</label>
            <input type="number" class="form-control" id="edit_amount" name="amount" value="<?php echo $record['amount']; ?>" readonly>
        </div>
        
        <!-- Source and Remarks -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
        </div>
        
        <div class="col-md-6">
            <label for="edit_source" class="form-label">Source</label>
            <select class="form-select" id="edit_source" name="source" required>
                <option value="">Select Source</option>
                <option value="DSWD" <?php echo $record['source'] === 'DSWD' ? 'selected' : ''; ?>>DSWD</option>
                <option value="DOH" <?php echo $record['source'] === 'DOH' ? 'selected' : ''; ?>>DOH</option>
                <option value="OCD" <?php echo $record['source'] === 'OCD' ? 'selected' : ''; ?>>OCD</option>
                <option value="DPWH" <?php echo $record['source'] === 'DPWH' ? 'selected' : ''; ?>>DPWH</option>
                <option value="LGU" <?php echo $record['source'] === 'LGU' ? 'selected' : ''; ?>>LGU</option>
                <option value="NGO" <?php echo $record['source'] === 'NGO' ? 'selected' : ''; ?>>NGO</option>
                <option value="Private Sector" <?php echo $record['source'] === 'Private Sector' ? 'selected' : ''; ?>>Private Sector</option>
                <option value="Foreign Aid" <?php echo $record['source'] === 'Foreign Aid' ? 'selected' : ''; ?>>Foreign Aid</option>
                <option value="UN Agencies" <?php echo $record['source'] === 'UN Agencies' ? 'selected' : ''; ?>>UN Agencies</option>
                <option value="Other Government Agency" <?php echo $record['source'] === 'Other Government Agency' ? 'selected' : ''; ?>>Other Government Agency</option>
            </select>
        </div>
        
        <div class="col-md-6">
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
// Calculate amount function
function calculateEditAmount() {
    const quantity = parseFloat(document.getElementById('edit_quantity').value) || 0;
    const costPerUnit = parseFloat(document.getElementById('edit_cost_per_unit').value) || 0;
    const amount = quantity * costPerUnit;
    document.getElementById('edit_amount').value = amount.toFixed(2);
}

// Initialize amount calculation
document.addEventListener('DOMContentLoaded', function() {
    calculateEditAmount();
});

// Handle form submission
document.getElementById('editRecordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;
    
    fetch('../api/annex21_update.php', {
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