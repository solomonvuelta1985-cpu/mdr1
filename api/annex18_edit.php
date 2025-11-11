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
                <?php
                // Livestock types
                $livestockTypes = ['Pig', 'Cow', 'Goat', 'Horse'];
                $poultryTypes = ['Chicken', 'Duck'];

                if ($record['classification'] === 'Livestock') {
                    foreach ($livestockTypes as $type) {
                        $selected = ($record['type'] === $type) ? 'selected' : '';
                        echo "<option value=\"{$type}\" {$selected}>{$type}</option>";
                    }
                } elseif ($record['classification'] === 'Poultry') {
                    foreach ($poultryTypes as $type) {
                        $selected = ($record['type'] === $type) ? 'selected' : '';
                        echo "<option value=\"{$type}\" {$selected}>{$type}</option>";
                    }
                }
                ?>
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
const currentClassification = '<?php echo htmlspecialchars($record['classification']); ?>';

// Function to update type options when classification changes
function updateEditTypeOptions() {
    const classification = document.getElementById('edit_classification');
    const typeSelect = document.getElementById('edit_type');

    if (!classification || !typeSelect) {
        return;
    }

    const selectedClassification = classification.value;
    typeSelect.innerHTML = '<option value="">Select Type</option>';

    if (typeOptions[selectedClassification]) {
        typeOptions[selectedClassification].forEach(type => {
            const option = document.createElement('option');
            option.value = type;
            option.textContent = type;
            typeSelect.appendChild(option);
        });
    }
}

// Initialize form
(function initializeEditForm() {
    const classification = document.getElementById('edit_classification');

    if (!classification) {
        // Retry if elements not found (AJAX loading)
        setTimeout(initializeEditForm, 100);
        return;
    }

    // Attach change event listener
    classification.addEventListener('change', updateEditTypeOptions);
})();

// Handle form submission
(function attachFormSubmit() {
    const form = document.getElementById('editRecordForm');

    if (!form) {
        setTimeout(attachFormSubmit, 100);
        return;
    }

    form.addEventListener('submit', function(e) {
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
})();
</script>