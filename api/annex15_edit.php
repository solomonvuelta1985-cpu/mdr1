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
    $stmt = db_query("SELECT * FROM annex15_suspension_classes WHERE id = ?", [$record_id]);
} else {
    $stmt = db_query("SELECT * FROM annex15_suspension_classes WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
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
    'annex15_edit_form_access',
    "Opened edit form for record #{$record_id}"
);
?>

<form id="editRecordForm" method="POST" action="../api/annex15_update.php">
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
        
        <!-- Suspension Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-school me-2"></i>Suspension Details</h6>
        </div>
        
        <div class="col-md-6">
            <label for="edit_level" class="form-label">Level</label>
            <select class="form-select" id="edit_level" name="level" required>
                <option value="">Select Level</option>
                <option value="Preschool / Kindergarten" <?php echo $record['level'] === 'Preschool / Kindergarten' ? 'selected' : ''; ?>>Preschool / Kindergarten</option>
                <option value="Elementary" <?php echo $record['level'] === 'Elementary' ? 'selected' : ''; ?>>Elementary</option>
                <option value="Secondary / High School" <?php echo $record['level'] === 'Secondary / High School' ? 'selected' : ''; ?>>Secondary / High School</option>
                <option value="Senior High School" <?php echo $record['level'] === 'Senior High School' ? 'selected' : ''; ?>>Senior High School</option>
                <option value="Tertiary / College / University" <?php echo $record['level'] === 'Tertiary / College / University' ? 'selected' : ''; ?>>Tertiary / College / University</option>
                <option value="All Levels" <?php echo $record['level'] === 'All Levels' ? 'selected' : ''; ?>>All Levels</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label for="edit_type" class="form-label">Type</label>
            <select class="form-select" id="edit_type" name="type" required>
                <option value="">Select Type</option>
                <option value="Public" <?php echo $record['type'] === 'Public' ? 'selected' : ''; ?>>Public - Government-run schools only</option>
                <option value="Private" <?php echo $record['type'] === 'Private' ? 'selected' : ''; ?>>Private - Private learning institutions only</option>
                <option value="All" <?php echo $record['type'] === 'All' ? 'selected' : ''; ?>>All - Both public and private institutions</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label for="edit_suspension_date" class="form-label">Date/Time of Suspension</label>
            <input type="datetime-local" class="form-control" id="edit_suspension_date" name="suspension_date" value="<?php echo date('Y-m-d\TH:i', strtotime($record['suspension_date'])); ?>" required>
        </div>
        
        <div class="col-md-6">
            <label for="edit_resumption_date" class="form-label">Date/Time of Resumption</label>
            <input type="datetime-local" class="form-control" id="edit_resumption_date" name="resumption_date" value="<?php echo $record['resumption_date'] ? date('Y-m-d\TH:i', strtotime($record['resumption_date'])) : ''; ?>">
        </div>
        
        <!-- Additional Information -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
        </div>
        
        <div class="col-md-6">
            <label for="edit_suspension_reason" class="form-label">Reason for Suspension</label>
            <select class="form-select" id="edit_suspension_reason" name="suspension_reason" required>
                <option value="">Select Reason</option>
                <option value="Typhoon Signal" <?php echo $record['suspension_reason'] === 'Typhoon Signal' ? 'selected' : ''; ?>>Typhoon Signal</option>
                <option value="Heavy Rainfall / Flooding" <?php echo $record['suspension_reason'] === 'Heavy Rainfall / Flooding' ? 'selected' : ''; ?>>Heavy Rainfall / Flooding</option>
                <option value="Earthquake" <?php echo $record['suspension_reason'] === 'Earthquake' ? 'selected' : ''; ?>>Earthquake</option>
                <option value="Volcanic Activity" <?php echo $record['suspension_reason'] === 'Volcanic Activity' ? 'selected' : ''; ?>>Volcanic Activity</option>
                <option value="Transport Strike" <?php echo $record['suspension_reason'] === 'Transport Strike' ? 'selected' : ''; ?>>Transport Strike</option>
                <option value="Health Emergency" <?php echo $record['suspension_reason'] === 'Health Emergency' ? 'selected' : ''; ?>>Health Emergency</option>
                <option value="Security Concern" <?php echo $record['suspension_reason'] === 'Security Concern' ? 'selected' : ''; ?>>Security Concern</option>
                <option value="Other" <?php echo $record['suspension_reason'] === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label for="edit_issuing_authority" class="form-label">Issuing Authority</label>
            <select class="form-select" id="edit_issuing_authority" name="issuing_authority" required>
                <option value="">Select Authority</option>
                <option value="LGU Executive Order" <?php echo $record['issuing_authority'] === 'LGU Executive Order' ? 'selected' : ''; ?>>LGU Executive Order</option>
                <option value="DepEd Advisory" <?php echo $record['issuing_authority'] === 'DepEd Advisory' ? 'selected' : ''; ?>>DepEd Advisory</option>
                <option value="CHED Memorandum" <?php echo $record['issuing_authority'] === 'CHED Memorandum' ? 'selected' : ''; ?>>CHED Memorandum</option>
                <option value="School Administration" <?php echo $record['issuing_authority'] === 'School Administration' ? 'selected' : ''; ?>>School Administration</option>
                <option value="Other" <?php echo $record['issuing_authority'] === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label for="edit_suspension_scope" class="form-label">Scope of Suspension</label>
            <select class="form-select" id="edit_suspension_scope" name="suspension_scope" required>
                <option value="">Select Scope</option>
                <option value="Whole Day" <?php echo $record['suspension_scope'] === 'Whole Day' ? 'selected' : ''; ?>>Whole Day</option>
                <option value="Half Day (Morning)" <?php echo $record['suspension_scope'] === 'Half Day (Morning)' ? 'selected' : ''; ?>>Half Day (Morning)</option>
                <option value="Half Day (Afternoon)" <?php echo $record['suspension_scope'] === 'Half Day (Afternoon)' ? 'selected' : ''; ?>>Half Day (Afternoon)</option>
                <option value="Specific Levels Only" <?php echo $record['suspension_scope'] === 'Specific Levels Only' ? 'selected' : ''; ?>>Specific Levels Only</option>
                <option value="All Schools in Area" <?php echo $record['suspension_scope'] === 'All Schools in Area' ? 'selected' : ''; ?>>All Schools in Area</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label for="edit_alternative_delivery" class="form-label">Alternative Delivery</label>
            <select class="form-select" id="edit_alternative_delivery" name="alternative_delivery" required>
                <option value="">Select Alternative</option>
                <option value="None" <?php echo $record['alternative_delivery'] === 'None' ? 'selected' : ''; ?>>None</option>
                <option value="Modular Learning" <?php echo $record['alternative_delivery'] === 'Modular Learning' ? 'selected' : ''; ?>>Modular Learning</option>
                <option value="Online Classes" <?php echo $record['alternative_delivery'] === 'Online Classes' ? 'selected' : ''; ?>>Online Classes</option>
                <option value="Asynchronous Activities" <?php echo $record['alternative_delivery'] === 'Asynchronous Activities' ? 'selected' : ''; ?>>Asynchronous Activities</option>
                <option value="Make-up Classes" <?php echo $record['alternative_delivery'] === 'Make-up Classes' ? 'selected' : ''; ?>>Make-up Classes</option>
            </select>
        </div>
        
        <div class="col-12">
            <label for="edit_remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="edit_remarks" name="remarks" placeholder="Enter additional details, special instructions, or other relevant information" maxlength="1000"><?php echo htmlspecialchars($record['remarks'] ?? ''); ?></textarea>
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
    
    fetch('../api/annex15_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Check if redirect URL is provided
            if (data.redirect) {
                // Redirect to annex15_records.php
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