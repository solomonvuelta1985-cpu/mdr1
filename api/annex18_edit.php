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
    $stmt = db_query("SELECT * FROM annex18_animal_evacuation WHERE id = ?", [$record_id]);
} else {
    $stmt = db_query("SELECT * FROM annex18_animal_evacuation WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
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
    'annex18_edit_form_access',
    "Opened edit form for record #{$record_id}"
);
?>

<form id="editRecordForm" method="POST" action="../api/annex18_update.php">
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
        
        <!-- Animal Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-paw me-2"></i>Animal Details</h6>
        </div>
        
        <div class="col-md-6">
            <label for="edit_classification" class="form-label">Classification</label>
            <select class="form-select" id="edit_classification" name="classification" onchange="updateEditTypeOptions()" required>
                <option value="">Select Classification</option>
                <option value="Livestock" <?php echo $record['classification'] === 'Livestock' ? 'selected' : ''; ?>>Livestock</option>
                <option value="Poultry" <?php echo $record['classification'] === 'Poultry' ? 'selected' : ''; ?>>Poultry</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label for="edit_type" class="form-label">Type</label>
            <select class="form-select" id="edit_type" name="type" required>
                <option value="">Select Type</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label for="edit_quantity" class="form-label">Quantity (No. of heads)</label>
            <input type="number" class="form-control" id="edit_quantity" name="quantity" value="<?php echo $record['quantity']; ?>" min="0" max="999999" required>
        </div>
        
        <div class="col-md-6">
            <label for="edit_evacuation_reason" class="form-label">Reason for Evacuation</label>
            <select class="form-select" id="edit_evacuation_reason" name="evacuation_reason" required>
                <option value="">Select Reason</option>
                <option value="Expected Flooding" <?php echo $record['evacuation_reason'] === 'Expected Flooding' ? 'selected' : ''; ?>>Expected Flooding</option>
                <option value="Volcanic Eruption" <?php echo $record['evacuation_reason'] === 'Volcanic Eruption' ? 'selected' : ''; ?>>Volcanic Eruption</option>
                <option value="Typhoon" <?php echo $record['evacuation_reason'] === 'Typhoon' ? 'selected' : ''; ?>>Typhoon</option>
                <option value="Storm Surge" <?php echo $record['evacuation_reason'] === 'Storm Surge' ? 'selected' : ''; ?>>Storm Surge</option>
                <option value="Landslide Risk" <?php echo $record['evacuation_reason'] === 'Landslide Risk' ? 'selected' : ''; ?>>Landslide Risk</option>
                <option value="Other" <?php echo $record['evacuation_reason'] === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        
        <!-- Evacuation Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-home me-2"></i>Evacuation Details</h6>
        </div>
        
        <div class="col-md-6">
            <label for="edit_shelter_location" class="form-label">Temporary Shelter Location</label>
            <input type="text" class="form-control" id="edit_shelter_location" name="shelter_location" value="<?php echo htmlspecialchars($record['shelter_location'] ?? ''); ?>" maxlength="255">
        </div>
        
        <div class="col-md-6">
            <label for="edit_assistance_provider" class="form-label">Assistance Provider</label>
            <select class="form-select" id="edit_assistance_provider" name="assistance_provider">
                <option value="">Select Provider</option>
                <option value="LGU" <?php echo $record['assistance_provider'] === 'LGU' ? 'selected' : ''; ?>>LGU</option>
                <option value="DA" <?php echo $record['assistance_provider'] === 'DA' ? 'selected' : ''; ?>>DA (Department of Agriculture)</option>
                <option value="NGO" <?php echo $record['assistance_provider'] === 'NGO' ? 'selected' : ''; ?>>NGO</option>
                <option value="Private Sector" <?php echo $record['assistance_provider'] === 'Private Sector' ? 'selected' : ''; ?>>Private Sector</option>
                <option value="Community" <?php echo $record['assistance_provider'] === 'Community' ? 'selected' : ''; ?>>Community</option>
                <option value="Other" <?php echo $record['assistance_provider'] === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        
        <!-- Additional Information -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
        </div>
        
        <div class="col-12">
            <label for="edit_remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="edit_remarks" name="remarks" maxlength="1000"><?php echo htmlspecialchars($record['remarks'] ?? ''); ?></textarea>
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
const typeOptions = {
    'Livestock': ['Pig', 'Cow', 'Goat', 'Horse'],
    'Poultry': ['Chicken', 'Duck']
};

// Store the current type from PHP
const currentType = '<?php echo htmlspecialchars($record['type']); ?>';

// Initialize form with current classification
document.addEventListener('DOMContentLoaded', function() {
    const classification = document.getElementById('edit_classification');
    
    console.log('Initializing edit form:');
    console.log('- Classification:', classification.value);
    console.log('- Current Type:', currentType);
    
    if (classification.value) {
        // First populate type options with the correct selection
        updateEditTypeOptions();
        // Force set the type value after a small delay to ensure DOM is ready
        setTimeout(() => {
            const typeSelect = document.getElementById('edit_type');
            if (typeSelect && currentType) {
                typeSelect.value = currentType;
                console.log('Forced type selection to:', currentType);
            }
        }, 100);
    }
});

function updateEditTypeOptions() {
    const classification = document.getElementById('edit_classification').value;
    const typeSelect = document.getElementById('edit_type');
    
    typeSelect.innerHTML = '<option value="">Select Type</option>';
    
    console.log('Updating type options for:', classification, 'with selection:', currentType);
    
    if (typeOptions[classification]) {
        typeOptions[classification].forEach(type => {
            const option = document.createElement('option');
            option.value = type;
            option.textContent = type;
            // Set selected attribute if it matches
            if (type === currentType) {
                option.selected = true;
                console.log('Setting option as selected:', type);
            }
            typeSelect.appendChild(option);
        });
    }
    
    // Ensure the selected value is set after population
    if (currentType) {
        typeSelect.value = currentType;
    }
}

// Handle form submission
document.getElementById('editRecordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;
    
    fetch('../api/annex18_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Check if redirect URL is provided
            if (data.redirect) {
                // Redirect to annex18_records.php
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