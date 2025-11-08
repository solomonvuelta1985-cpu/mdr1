<?php
/**
 * API Handler: Edit Affected Population Record (Annex 2)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// RATE LIMITING: Prevent edit form spam
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex2_edit', 30, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex2 edit rate limit exceeded');
    echo '<div class="alert alert-danger">Too many requests. Please try again later.</div>';
    exit;
}

// Get record ID
$record_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$record_id) {
    echo '<div class="alert alert-danger">Invalid record ID</div>';
    log_security_event($_SESSION['user_id'], 'invalid_input', 'Invalid record ID for Annex2 edit');
    exit;
}

// Get user info
$is_admin = ($_SESSION['user_role'] === 'admin');

// Fetch record
try {
    if ($is_admin) {
        $sql = "SELECT * FROM annex2_affected_population WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$record_id]);
    } else {
        $sql = "SELECT * FROM annex2_affected_population WHERE id = ? AND created_by = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$record_id, $_SESSION['user_id']]);
    }

    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        echo '<div class="alert alert-warning">Record not found or access denied.</div>';
        log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to edit Annex2 record #{$record_id} without permission");
        exit;
    }
    
    // AUDIT LOG: Edit form access
    log_audit_action(
        $_SESSION['user_id'],
        'annex2_edit_access',
        "Accessed edit form for record #{$record_id} for {$record['barangay']}"
    );
    
    // SECURITY LOG: Edit form access
    log_security_event(
        $_SESSION['user_id'],
        'edit_form_access',
        "Accessed Annex 2 edit form for record #{$record_id}"
    );
    
} catch (PDOException $e) {
    error_log("Annex2 Edit Error: " . $e->getMessage());
    echo '<div class="alert alert-danger">Unable to load record. Please try again.</div>';
    exit;
}

// Generate CSRF token
$csrf_token = generate_token();
?>

<style>
    .edit-form {
        padding: 20px;
    }
    .form-section {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #dee2e6;
    }
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 5px;
    }
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .readonly-field {
        background-color: #e9ecef;
    }
    .field-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }
</style>

<div class="edit-form">
    <form id="editForm" method="POST" action="../api/annex2_update.php">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="record_id" value="<?php echo $record_id; ?>">

        <!-- Location Information -->
        <div class="form-section">
            <div class="section-title">Location Information</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="region" class="form-label">Region <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="region" name="region" 
                           value="<?php echo htmlspecialchars($record['region']); ?>" 
                           maxlength="100" required>
                </div>
                <div class="col-md-6">
                    <label for="province" class="form-label">Province <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="province" name="province" 
                           value="<?php echo htmlspecialchars($record['province']); ?>" 
                           maxlength="100" required>
                </div>
                <div class="col-md-6">
                    <label for="city" class="form-label">City/Municipality <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="city" name="city" 
                           value="<?php echo htmlspecialchars($record['city']); ?>" 
                           maxlength="100" required>
                </div>
                <div class="col-md-6">
                    <label for="barangay" class="form-label">Barangay <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="barangay" name="barangay" 
                           value="<?php echo htmlspecialchars($record['barangay']); ?>" 
                           maxlength="200" required>
                </div>
            </div>
        </div>

        <!-- Affected Population -->
        <div class="form-section">
            <div class="section-title">Affected Population</div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="affected_families_cumulative" class="form-label">Families (Cumulative)</label>
                    <input type="number" class="form-control" id="affected_families_cumulative" 
                           name="affected_families_cumulative" 
                           value="<?php echo $record['affected_families_cumulative'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
                <div class="col-md-3">
                    <label for="affected_families_current" class="form-label">Families (Current)</label>
                    <input type="number" class="form-control" id="affected_families_current" 
                           name="affected_families_current" 
                           value="<?php echo $record['affected_families_current'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
                <div class="col-md-3">
                    <label for="affected_persons_cumulative" class="form-label">Persons (Cumulative)</label>
                    <input type="number" class="form-control" id="affected_persons_cumulative" 
                           name="affected_persons_cumulative" 
                           value="<?php echo $record['affected_persons_cumulative'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
                <div class="col-md-3">
                    <label for="affected_persons_current" class="form-label">Persons (Current)</label>
                    <input type="number" class="form-control" id="affected_persons_current" 
                           name="affected_persons_current" 
                           value="<?php echo $record['affected_persons_current'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
            </div>
        </div>

        <!-- Evacuation Centers -->
        <div class="form-section">
            <div class="section-title">Evacuation Centers</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="num_ecs_cumulative" class="form-label">Number of ECs (Cumulative)</label>
                    <input type="number" class="form-control" id="num_ecs_cumulative" 
                           name="num_ecs_cumulative" 
                           value="<?php echo $record['num_ecs_cumulative'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
                <div class="col-md-6">
                    <label for="num_ecs_current" class="form-label">Number of ECs (Current)</label>
                    <input type="number" class="form-control" id="num_ecs_current" 
                           name="num_ecs_current" 
                           value="<?php echo $record['num_ecs_current'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
            </div>
        </div>

        <!-- Inside Evacuation Centers -->
        <div class="form-section">
            <div class="section-title">Inside Evacuation Centers</div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="inside_families_cumulative" class="form-label">Families (Cumulative)</label>
                    <input type="number" class="form-control" id="inside_families_cumulative" 
                           name="inside_families_cumulative" 
                           value="<?php echo $record['inside_families_cumulative'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
                <div class="col-md-3">
                    <label for="inside_families_current" class="form-label">Families (Current)</label>
                    <input type="number" class="form-control" id="inside_families_current" 
                           name="inside_families_current" 
                           value="<?php echo $record['inside_families_current'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
                <div class="col-md-3">
                    <label for="inside_persons_cumulative" class="form-label">Persons (Cumulative)</label>
                    <input type="number" class="form-control" id="inside_persons_cumulative" 
                           name="inside_persons_cumulative" 
                           value="<?php echo $record['inside_persons_cumulative'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
                <div class="col-md-3">
                    <label for="inside_persons_current" class="form-label">Persons (Current)</label>
                    <input type="number" class="form-control" id="inside_persons_current" 
                           name="inside_persons_current" 
                           value="<?php echo $record['inside_persons_current'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
            </div>
        </div>

        <!-- Outside Evacuation Centers -->
        <div class="form-section">
            <div class="section-title">Outside Evacuation Centers</div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="outside_families_cumulative" class="form-label">Families (Cumulative)</label>
                    <input type="number" class="form-control" id="outside_families_cumulative" 
                           name="outside_families_cumulative" 
                           value="<?php echo $record['outside_families_cumulative'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
                <div class="col-md-3">
                    <label for="outside_families_current" class="form-label">Families (Current)</label>
                    <input type="number" class="form-control" id="outside_families_current" 
                           name="outside_families_current" 
                           value="<?php echo $record['outside_families_current'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
                <div class="col-md-3">
                    <label for="outside_persons_cumulative" class="form-label">Persons (Cumulative)</label>
                    <input type="number" class="form-control" id="outside_persons_cumulative" 
                           name="outside_persons_cumulative" 
                           value="<?php echo $record['outside_persons_cumulative'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
                <div class="col-md-3">
                    <label for="outside_persons_current" class="form-label">Persons (Current)</label>
                    <input type="number" class="form-control" id="outside_persons_current" 
                           name="outside_persons_current" 
                           value="<?php echo $record['outside_persons_current'] ?? 0; ?>" 
                           min="0" max="999999999">
                </div>
            </div>
        </div>

        <!-- Total Displaced (Auto-calculated) -->
        <div class="form-section">
            <div class="section-title">Total Displaced (Auto-calculated)</div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="total_families_cumulative" class="form-label">Families (Cumulative)</label>
                    <input type="number" class="form-control readonly-field" id="total_families_cumulative" 
                           name="total_families_cumulative" 
                           value="<?php echo $record['total_families_cumulative'] ?? 0; ?>" 
                           readonly min="0" max="999999999">
                    <small class="text-muted">Auto-calculated</small>
                </div>
                <div class="col-md-3">
                    <label for="total_families_current" class="form-label">Families (Current)</label>
                    <input type="number" class="form-control readonly-field" id="total_families_current" 
                           name="total_families_current" 
                           value="<?php echo $record['total_families_current'] ?? 0; ?>" 
                           readonly min="0" max="999999999">
                    <small class="text-muted">Auto-calculated</small>
                </div>
                <div class="col-md-3">
                    <label for="total_persons_cumulative" class="form-label">Persons (Cumulative)</label>
                    <input type="number" class="form-control readonly-field" id="total_persons_cumulative" 
                           name="total_persons_cumulative" 
                           value="<?php echo $record['total_persons_cumulative'] ?? 0; ?>" 
                           readonly min="0" max="999999999">
                    <small class="text-muted">Auto-calculated</small>
                </div>
                <div class="col-md-3">
                    <label for="total_persons_current" class="form-label">Persons (Current)</label>
                    <input type="number" class="form-control readonly-field" id="total_persons_current" 
                           name="total_persons_current" 
                           value="<?php echo $record['total_persons_current'] ?? 0; ?>" 
                           readonly min="0" max="999999999">
                    <small class="text-muted">Auto-calculated</small>
                </div>
            </div>
        </div>

        <!-- Remarks -->
        <div class="form-section">
            <div class="section-title">Remarks</div>
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="3" 
                              maxlength="500"><?php echo htmlspecialchars($record['remarks'] ?? ''); ?></textarea>
                    <small class="text-muted">Maximum 500 characters</small>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="saveButton">
                <i class="fas fa-save me-2"></i>Save Changes
            </button>
        </div>
    </form>
</div>

<script>
// Auto-calculate totals
function calculateTotals() {
    // Cumulative totals
    const insideFamiliesCum = parseInt(document.getElementById('inside_families_cumulative').value) || 0;
    const outsideFamiliesCum = parseInt(document.getElementById('outside_families_cumulative').value) || 0;
    const insidePersonsCum = parseInt(document.getElementById('inside_persons_cumulative').value) || 0;
    const outsidePersonsCum = parseInt(document.getElementById('outside_persons_cumulative').value) || 0;
    
    document.getElementById('total_families_cumulative').value = insideFamiliesCum + outsideFamiliesCum;
    document.getElementById('total_persons_cumulative').value = insidePersonsCum + outsidePersonsCum;
    
    // Current totals
    const insideFamiliesCur = parseInt(document.getElementById('inside_families_current').value) || 0;
    const outsideFamiliesCur = parseInt(document.getElementById('outside_families_current').value) || 0;
    const insidePersonsCur = parseInt(document.getElementById('inside_persons_current').value) || 0;
    const outsidePersonsCur = parseInt(document.getElementById('outside_persons_current').value) || 0;
    
    document.getElementById('total_families_current').value = insideFamiliesCur + outsideFamiliesCur;
    document.getElementById('total_persons_current').value = insidePersonsCur + outsidePersonsCur;
}

// Add event listeners for auto-calculation
['inside_families_cumulative', 'outside_families_cumulative', 'inside_families_current', 'outside_families_current',
 'inside_persons_cumulative', 'outside_persons_cumulative', 'inside_persons_current', 'outside_persons_current'].forEach(id => {
    document.getElementById(id).addEventListener('input', calculateTotals);
});

// Form submission
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const saveButton = document.getElementById('saveButton');
    const originalText = saveButton.innerHTML;
    
    // Show loading state
    saveButton.disabled = true;
    saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    
    // Create FormData object
    const formData = new FormData(this);
    
    // Submit via AJAX
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert('✅ Record updated successfully!');
            // Close modal and reload page
            window.location.reload();
        } else {
            // Show error message
            alert('❌ Error: ' + data.message);
            // Restore button
            saveButton.disabled = false;
            saveButton.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ An error occurred while saving the record.');
        // Restore button
        saveButton.disabled = false;
        saveButton.innerHTML = originalText;
    });
});

// Initial calculation
calculateTotals();
</script>