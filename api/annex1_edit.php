<?php
// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

//   Require login
require_login();

//   Security rate limiting (view/edit form access logging)
if (!check_rate_limit($_SESSION['user_id'], 'annex1_edit_access', 30, 3600)) { // 30 form opens/hour
    log_security_event($_SESSION['user_id'], 'rate_limit_exceeded', 'Annex1 edit form access limit exceeded');
    die('<div class="alert alert-danger">Too many edit form requests. Please try again later.</div>');
}

//   Ensure record ID provided
if (!isset($_GET['id'])) {
    log_security_event($_SESSION['user_id'], 'missing_id', 'Annex1 edit accessed without record ID');
    die('<div class="alert alert-danger">Record ID is required</div>');
}

$record_id = sanitize($_GET['id']);
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_role'] === 'admin');

//   Fetch the record (maintains your original SQL logic)
if ($is_admin) {
    $stmt = db_query("
        SELECT * FROM annex1_related_incidents 
        WHERE id = ?
    ", [$record_id]);
} else {
    $stmt = db_query("
        SELECT * FROM annex1_related_incidents 
        WHERE id = ? AND created_by = ?
    ", [$record_id, $user_id]);
}

$record = $stmt->fetch();

if (!$record) {
    log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to access Annex1 edit for record ID {$record_id}");
    die('<div class="alert alert-danger">Record not found or access denied</div>');
}

//   Generate CSRF token (existing)
$csrf_token = generate_token();

//   Audit log for successful record access
log_audit_action(
    $_SESSION['user_id'],
    'annex1_edit_access',
    "Accessed edit form for Annex1 record ID {$record_id} ({$record['incident_type']}) in barangay {$record['barangay']}"
);
?>

<form id="editRecordForm" method="POST" action="../api/annex1_update.php">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="edit_incident_type" class="form-label">Type of Incident</label>
            <select class="form-select" id="edit_incident_type" name="incident_type" required>
                <option value="">Select Type</option>
                <option value="Landslide" <?php echo $record['incident_type'] == 'Landslide' ? 'selected' : ''; ?>>Landslide</option>
                <option value="Flooding" <?php echo $record['incident_type'] == 'Flooding' ? 'selected' : ''; ?>>Flooding</option>
                <option value="Fire" <?php echo $record['incident_type'] == 'Fire' ? 'selected' : ''; ?>>Fire</option>
                <option value="Earthquake" <?php echo $record['incident_type'] == 'Earthquake' ? 'selected' : ''; ?>>Earthquake</option>
                <option value="Storm Surge" <?php echo $record['incident_type'] == 'Storm Surge' ? 'selected' : ''; ?>>Storm Surge</option>
                <option value="Tsunami" <?php echo $record['incident_type'] == 'Tsunami' ? 'selected' : ''; ?>>Tsunami</option>
                <option value="Volcanic Eruption" <?php echo $record['incident_type'] == 'Volcanic Eruption' ? 'selected' : ''; ?>>Volcanic Eruption</option>
                <option value="Other" <?php echo $record['incident_type'] == 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="edit_occurrence_date" class="form-label">Date/Time of Occurrence</label>
            <input type="datetime-local" class="form-control" id="edit_occurrence_date" 
                   name="occurrence_date" value="<?php echo $record['occurrence_date'] ? date('Y-m-d\TH:i', strtotime($record['occurrence_date'])) : ''; ?>" required>
        </div>
        <div class="col-12">
            <label for="edit_description" class="form-label">Description</label>
            <textarea class="form-control" id="edit_description" name="description" 
                      rows="3" placeholder="Provide description of the incident"><?php echo htmlspecialchars($record['description']); ?></textarea>
        </div>
        <div class="col-12">
            <label for="edit_actions_taken" class="form-label">Actions Taken</label>
            <textarea class="form-control" id="edit_actions_taken" name="actions_taken" 
                      rows="3" placeholder="Describe actions taken"><?php echo htmlspecialchars($record['actions_taken']); ?></textarea>
        </div>
        <div class="col-12">
            <label for="edit_remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="edit_remarks" name="remarks" 
                      rows="2" placeholder="Enter remarks"><?php echo htmlspecialchars($record['remarks']); ?></textarea>
        </div>
    </div>
    
    <div class="modal-footer mt-4">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Update Record
        </button>
    </div>
</form>

<script>
// Form submission handler
document.getElementById('editRecordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
        } else {
            return response.text();
        }
    })
    .then(data => {
        if (data) {
            try {
                const result = JSON.parse(data);
                if (result.success) {
                    alert('Record updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Failed to update record'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (e) {
                location.reload();
            }
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>
