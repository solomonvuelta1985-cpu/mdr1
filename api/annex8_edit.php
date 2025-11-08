<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// ✅ Require login
require_login();

// ✅ Verify record ID
if (!isset($_GET['id'])) {
    http_response_code(400);
    log_security_event($_SESSION['user_id'], 'invalid_request', 'Annex8 edit accessed without ID');
    exit('Record ID required');
}

$record_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_role'] === 'admin');

// ✅ Fetch record (with role-based control)
if ($is_admin) {
    $stmt = db_query("SELECT * FROM annex8_road_bridge_status WHERE id = ?", [$record_id]);
} else {
    $stmt = db_query("SELECT * FROM annex8_road_bridge_status WHERE id = ? AND created_by = ?", [$record_id, $user_id]);
}

$record = $stmt->fetch();

// ✅ Check if record exists and is accessible
if (!$record) {
    log_security_event($user_id, 'unauthorized_access', "Attempted to access Annex8 edit form for record #{$record_id}");
    echo '<div class="alert alert-danger">Record not found or access denied.</div>';
    exit;
}

// ✅ Generate CSRF token
$csrf_token = generate_token();

// ✅ Log successful access to edit form
log_audit_action($user_id, 'annex8_edit_access', "Accessed edit form for Annex8 record #{$record_id}");
?>

<form action="../api/annex8_update.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="record_id" value="<?= $record['id'] ?>">
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Type</label>
                <select class="form-select" name="type" required>
                    <option value="">Select Type</option>
                    <option value="Road" <?= $record['type'] === 'Road' ? 'selected' : '' ?>>Road</option>
                    <option value="Bridge" <?= $record['type'] === 'Bridge' ? 'selected' : '' ?>>Bridge</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Classification</label>
                <select class="form-select" name="classification" required>
                    <option value="">Select Class</option>
                    <option value="National" <?= $record['classification'] === 'National' ? 'selected' : '' ?>>National</option>
                    <option value="Provincial" <?= $record['classification'] === 'Provincial' ? 'selected' : '' ?>>Provincial</option>
                    <option value="City/Municipal" <?= $record['classification'] === 'City/Municipal' ? 'selected' : '' ?>>City/Municipal</option>
                    <option value="Barangay" <?= $record['classification'] === 'Barangay' ? 'selected' : '' ?>>Barangay</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Road Section/Bridge Name</label>
        <input type="text" class="form-control" name="road_section" value="<?= htmlspecialchars($record['road_section']) ?>" required>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status" required>
                    <option value="">Select Status</option>
                    <option value="Passable" <?= $record['status'] === 'Passable' ? 'selected' : '' ?>>Passable</option>
                    <option value="Not Passable" <?= $record['status'] === 'Not Passable' ? 'selected' : '' ?>>Not Passable</option>
                    <option value="Passable to Heavy Vehicles Only" <?= $record['status'] === 'Passable to Heavy Vehicles Only' ? 'selected' : '' ?>>Passable to Heavy Vehicles Only</option>
                    <option value="Passable to Light Vehicles Only" <?= $record['status'] === 'Passable to Light Vehicles Only' ? 'selected' : '' ?>>Passable to Light Vehicles Only</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <input type="text" class="form-control" name="remarks" value="<?= htmlspecialchars($record['remarks']) ?>" placeholder="Enter remarks">
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Date/Time Not Passable</label>
                <input type="datetime-local" class="form-control" name="date_not_passable" 
                       value="<?= $record['date_not_passable'] ? date('Y-m-d\TH:i', strtotime($record['date_not_passable'])) : '' ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Date/Time Passable</label>
                <input type="datetime-local" class="form-control" name="date_passable" 
                       value="<?= $record['date_passable'] ? date('Y-m-d\TH:i', strtotime($record['date_passable'])) : '' ?>">
            </div>
        </div>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Update Image (Optional)</label>
        <input type="file" class="form-control" name="image" accept="image/*">
        <?php if (!empty($record['image_path'])): ?>
            <div class="mt-2">
                <small class="text-muted">Current Image:</small><br>
                <img src="../uploads/<?= htmlspecialchars($record['image_path']) ?>" class="img-thumbnail mt-1" style="max-height: 100px;">
            </div>
        <?php endif; ?>
    </div>
    
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Record</button>
    </div>
</form>
