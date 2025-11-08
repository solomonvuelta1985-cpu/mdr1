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
    $stmt = db_query("SELECT * FROM annex5_agriculture_damage WHERE id = ?", [$record_id]);
} else {
    $stmt = db_query("SELECT * FROM annex5_agriculture_damage WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
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
    'annex5_edit_form_access',
    "Opened edit form for record #{$record_id}"
);
?>

<form id="editRecordForm" method="POST" action="../api/annex5_update.php">
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
        
        <!-- Classification -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-seedling me-2"></i>Agriculture Classification</h6>
        </div>
        
        <div class="col-md-6">
            <label for="edit_classification" class="form-label">Classification</label>
            <select class="form-select" id="edit_classification" name="classification" onchange="toggleEditFields(this)" required>
                <option value="">Select Classification</option>
                <option value="Crops" <?php echo $record['classification'] === 'Crops' ? 'selected' : ''; ?>>Crops</option>
                <option value="Livestock and Poultry" <?php echo $record['classification'] === 'Livestock and Poultry' ? 'selected' : ''; ?>>Livestock and Poultry</option>
                <option value="Fisheries" <?php echo $record['classification'] === 'Fisheries' ? 'selected' : ''; ?>>Fisheries</option>
                <option value="Agricultural Infrastructure" <?php echo $record['classification'] === 'Agricultural Infrastructure' ? 'selected' : ''; ?>>Agricultural Infrastructure</option>
                <option value="Machineries and Equipment" <?php echo $record['classification'] === 'Machineries and Equipment' ? 'selected' : ''; ?>>Machineries and Equipment</option>
            </select>
        </div>
        
<div class="col-md-6">
    <label for="edit_type" class="form-label">Type</label>
    <select class="form-select" id="edit_type" name="type" required>
        <option value="">Select Type</option>
        <?php
        $typeOptions = [
            'Crops' => ['Rice', 'Corn', 'HVC'],
            'Livestock and Poultry' => ['Livestock', 'Poultry'],
            'Fisheries' => ['Produce', 'Fishing gears/paraphernalia', 'Facilities and equipment'],
            'Agricultural Infrastructure' => ['Irrigation System', 'Farm Structures'],
            'Machineries and Equipment' => ['Machineries', 'Equipment']
        ];
        
        $currentClassification = $record['classification'];
        $currentType = $record['type'];
        
        if (isset($typeOptions[$currentClassification])) {
            foreach ($typeOptions[$currentClassification] as $type) {
                $selected = ($type === $currentType) ? 'selected' : '';
                echo "<option value=\"" . htmlspecialchars($type) . "\" $selected>" . htmlspecialchars($type) . "</option>";
            }
        }
        ?>
    </select>
</div>
        
        <!-- Impact Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Impact Details</h6>
        </div>
        
        <div class="col-md-6">
            <label for="edit_affected_people" class="form-label">Number of Fisherfolks or Farmers Affected</label>
            <input type="number" class="form-control" id="edit_affected_people" name="affected_people" value="<?php echo $record['affected_people']; ?>" min="0" max="1000000" required>
        </div>
        
        <div class="col-md-6">
            <label for="edit_damage_value" class="form-label">Production Loss / Cost Damage in Value (₱)</label>
            <input type="number" class="form-control" id="edit_damage_value" name="damage_value" value="<?php echo $record['damage_value']; ?>" min="0" step="0.01" required>
        </div>
        
        <!-- Crops Specific Fields -->
        <div class="edit-crops-fields" style="display: none;">
            <div class="col-12 mt-3">
                <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-wheat me-2"></i>Crops Damage Details</h6>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="edit_no_recovery_area" class="form-label">Affected Crop Area - No Chance of Recovery (ha)</label>
                    <input type="number" class="form-control" id="edit_no_recovery_area" name="no_recovery_area" value="<?php echo $record['no_recovery_area']; ?>" min="0" step="0.01" onchange="calculateEditCropTotal()">
                </div>
                <div class="col-md-4">
                    <label for="edit_with_recovery_area" class="form-label">Affected Crop Area - With Chance of Recovery (ha)</label>
                    <input type="number" class="form-control" id="edit_with_recovery_area" name="with_recovery_area" value="<?php echo $record['with_recovery_area']; ?>" min="0" step="0.01" onchange="calculateEditCropTotal()">
                </div>
                <div class="col-md-4">
                    <label for="edit_total_crop_area" class="form-label">Total Affected Crop Area (ha)</label>
                    <input type="number" class="form-control" id="edit_total_crop_area" name="total_crop_area" value="<?php echo $record['total_crop_area']; ?>" readonly>
                </div>
                <div class="col-md-12">
                    <label for="edit_production_loss_volume" class="form-label">Production Loss in Volume (MT)</label>
                    <input type="number" class="form-control" id="edit_production_loss_volume" name="production_loss_volume" value="<?php echo $record['production_loss_volume']; ?>" min="0" step="0.01">
                </div>
            </div>
        </div>
        
        <!-- Livestock and Poultry Specific Fields -->
        <div class="edit-livestock-fields" style="display: none;">
            <div class="col-12 mt-3">
                <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-horse me-2"></i>Livestock and Poultry Damage Details</h6>
            </div>
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="edit_animal_heads" class="form-label">Number of Heads (Livestock and Poultry)</label>
                    <input type="number" class="form-control" id="edit_animal_heads" name="animal_heads" value="<?php echo $record['animal_heads']; ?>" min="0">
                </div>
            </div>
        </div>
        
        <!-- Fisheries Specific Fields -->
        <div class="edit-fisheries-fields" style="display: none;">
            <div class="col-12 mt-3">
                <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-fish me-2"></i>Fisheries Damage Details</h6>
            </div>
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="edit_fisheries_details" class="form-label">Fisheries Impact Details</label>
                    <input type="text" class="form-control" id="edit_fisheries_details" name="fisheries_details" value="<?php echo htmlspecialchars($record['fisheries_details'] ?? ''); ?>" maxlength="500">
                </div>
            </div>
        </div>
        
        <!-- Infrastructure and Machinery Specific Fields -->
        <div class="edit-infrastructure-fields edit-machinery-fields" style="display: none;">
            <div class="col-12 mt-3">
                <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-tools me-2"></i>Infrastructure and Machinery Damage Details</h6>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="edit_totally_damaged" class="form-label">Totally Damaged</label>
                    <input type="number" class="form-control" id="edit_totally_damaged" name="totally_damaged" value="<?php echo $record['totally_damaged']; ?>" min="0" onchange="calculateEditInfrastructureTotal()">
                </div>
                <div class="col-md-4">
                    <label for="edit_partially_damaged" class="form-label">Partially Damaged</label>
                    <input type="number" class="form-control" id="edit_partially_damaged" name="partially_damaged" value="<?php echo $record['partially_damaged']; ?>" min="0" onchange="calculateEditInfrastructureTotal()">
                </div>
                <div class="col-md-4">
                    <label for="edit_total_damaged" class="form-label">Total Damaged</label>
                    <input type="number" class="form-control" id="edit_total_damaged_display" name="total_damaged" value="<?php echo $record['total_damaged']; ?>" readonly>
                </div>
            </div>
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
    'Crops': ['Rice', 'Corn', 'HVC'],
    'Livestock and Poultry': ['Livestock', 'Poultry'],
    'Fisheries': ['Produce', 'Fishing gears/paraphernalia', 'Facilities and equipment'],
    'Agricultural Infrastructure': ['Irrigation System', 'Farm Structures'],
    'Machineries and Equipment': ['Machineries', 'Equipment']
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
        populateTypeOptions(classification.value, currentType);
        // Then show/hide relevant fields
        toggleEditFields(classification);
        
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

function populateTypeOptions(classification, selectedType = '') {
    const typeSelect = document.getElementById('edit_type');
    typeSelect.innerHTML = '<option value="">Select Type</option>';
    
    console.log('Populating types for:', classification, 'with selection:', selectedType);
    
    if (typeOptions[classification]) {
        typeOptions[classification].forEach(type => {
            const option = document.createElement('option');
            option.value = type;
            option.textContent = type;
            // Set selected attribute if it matches
            if (type === selectedType) {
                option.selected = true;
                console.log('Setting option as selected:', type);
            }
            typeSelect.appendChild(option);
        });
    }
    
    // Ensure the selected value is set after population
    if (selectedType) {
        typeSelect.value = selectedType;
    }
}

function toggleEditFields(select) {
    const classification = select.value;
    
    console.log('Toggling fields for classification:', classification);
    
    // Hide all field groups
    document.querySelectorAll('.edit-crops-fields, .edit-livestock-fields, .edit-fisheries-fields, .edit-infrastructure-fields, .edit-machinery-fields').forEach(field => {
        field.style.display = 'none';
    });
    
    // Populate type options - use the stored currentType
    populateTypeOptions(classification, currentType);
    
    // Show relevant fields based on classification
    if (classification === 'Crops') {
        document.querySelector('.edit-crops-fields').style.display = 'block';
        console.log('Showing crops fields');
    } else if (classification === 'Livestock and Poultry') {
        document.querySelector('.edit-livestock-fields').style.display = 'block';
        console.log('Showing livestock fields');
    } else if (classification === 'Fisheries') {
        document.querySelector('.edit-fisheries-fields').style.display = 'block';
        console.log('Showing fisheries fields');
    } else if (classification === 'Agricultural Infrastructure') {
        document.querySelector('.edit-infrastructure-fields').style.display = 'block';
        console.log('Showing infrastructure fields');
    } else if (classification === 'Machineries and Equipment') {
        document.querySelector('.edit-machinery-fields').style.display = 'block';
        console.log('Showing machinery fields');
    }
    
    // Final assurance - set the type value again
    setTimeout(() => {
        const typeSelect = document.getElementById('edit_type');
        if (typeSelect && currentType) {
            typeSelect.value = currentType;
            console.log('Final type value set to:', currentType);
        }
    }, 200);
}

function calculateEditCropTotal() {
    const noRecovery = parseFloat(document.getElementById('edit_no_recovery_area').value) || 0;
    const withRecovery = parseFloat(document.getElementById('edit_with_recovery_area').value) || 0;
    document.getElementById('edit_total_crop_area').value = (noRecovery + withRecovery).toFixed(2);
}

function calculateEditInfrastructureTotal() {
    const totally = parseInt(document.getElementById('edit_totally_damaged').value) || 0;
    const partially = parseInt(document.getElementById('edit_partially_damaged').value) || 0;
    document.getElementById('edit_total_damaged_display').value = totally + partially;
}

// Handle form submission
document.getElementById('editRecordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;
    
    fetch('../api/annex5_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Check if redirect URL is provided
            if (data.redirect) {
                // Redirect to annex5_records.php
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