<?php
// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();

// Check if record ID is provided
if (!isset($_GET['id'])) {
    die('<div class="alert alert-danger">Record ID is required</div>');
}

$record_id = sanitize($_GET['id']);
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_role'] === 'admin');

// Fetch the record
if ($is_admin) {
    $stmt = db_query("
        SELECT * FROM annex14_suspension_work 
        WHERE id = ?
    ", [$record_id]);
} else {
    $stmt = db_query("
        SELECT * FROM annex14_suspension_work 
        WHERE id = ? AND created_by = ?
    ", [$record_id, $user_id]);
}

$record = $stmt->fetch();

if (!$record) {
    die('<div class="alert alert-danger">Record not found or access denied</div>');
}

// Generate CSRF token
$csrf_token = generate_token();
?>

<form id="editRecordForm" method="POST" action="../api/annex14_update.php">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="edit_type" class="form-label">Type</label>
            <select class="form-select" id="edit_type" name="type" required>
                <option value="">Select Type</option>
                <option value="Government" <?php echo $record['type'] === 'Government' ? 'selected' : ''; ?>>Government – Public offices only</option>
                <option value="Private" <?php echo $record['type'] === 'Private' ? 'selected' : ''; ?>>Private – Private sector establishments only</option>
                <option value="All" <?php echo $record['type'] === 'All' ? 'selected' : ''; ?>>All – Both public and private sectors</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="edit_suspension_date" class="form-label">Date/Time of Suspension</label>
            <input type="datetime-local" class="form-control" id="edit_suspension_date" name="suspension_date" 
                   value="<?php echo date('Y-m-d\TH:i', strtotime($record['suspension_date'])); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="edit_resumption_date" class="form-label">Date/Time of Resumption</label>
            <input type="datetime-local" class="form-control" id="edit_resumption_date" name="resumption_date" 
                   value="<?php echo $record['resumption_date'] ? date('Y-m-d\TH:i', strtotime($record['resumption_date'])) : ''; ?>">
        </div>
        <div class="col-12">
            <label for="edit_remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="edit_remarks" name="remarks" rows="3" 
                      placeholder="Enter remarks"><?php echo htmlspecialchars($record['remarks']); ?></textarea>
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