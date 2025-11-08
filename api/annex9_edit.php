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
    $stmt = db_query("SELECT * FROM annex9_power_supply WHERE id = ?", [$record_id]);
} else {
    $stmt = db_query("SELECT * FROM annex9_power_supply WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
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
    'annex9_edit_form_access',
    "Opened edit form for record #{$record_id}"
);

// Convert datetime for datetime-local input
$interruption_date_local = date('Y-m-d\TH:i', strtotime($record['interruption_datetime']));
$restored_date_local = $record['restored_datetime'] ? date('Y-m-d\TH:i', strtotime($record['restored_datetime'])) : '';

// Check if current image exists
$current_image_path = $record['image_path'] ? '../uploads/' . $record['image_path'] : '';
$current_image_exists = $record['image_path'] && file_exists($current_image_path);
?>

<style>
.upload-area {
    border: 3px dashed #ccc;
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: #f8f9fa;
    min-height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.upload-area[style*="display: none"] {
    display: none !important;
}
.upload-area:hover {
    border-color: #0d6efd;
    background: #e7f1ff;
}
.upload-area.has-new-image {
    border-color: #28a745;
    background: #d4edda;
}
.upload-area.has-error {
    border-color: #dc3545;
    background: #f8d7da;
}
.preview-container {
    max-width: 100%;
}
.preview-image {
    max-width: 100%;
    max-height: 400px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 3px solid #28a745;
}
.current-image-section {
    transition: opacity 0.3s;
}
.current-image-section.dimmed {
    opacity: 0.3;
    pointer-events: none;
}
.image-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.9rem;
    margin-bottom: 10px;
}
.badge-current {
    background: #0dcaf0;
    color: white;
}
.badge-new {
    background: #28a745;
    color: white;
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>

<!-- Alert Container -->
<div id="editAlertContainer"></div>

<form id="editRecordForm" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
    <input type="hidden" name="current_image_path" value="<?php echo htmlspecialchars($record['image_path'] ?? ''); ?>">
    
    <div class="row g-3">
        <!-- Location Details -->
        <div class="col-12">
            <h6 class="text-primary border-bottom pb-2 mb-3">
                <i class="fas fa-map-marker-alt me-2"></i>Location Information
            </h6>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label class="form-label">Region</label>
            <input type="text" class="form-control" name="region" value="<?php echo htmlspecialchars($record['region']); ?>" required maxlength="100">
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label class="form-label">Province</label>
            <input type="text" class="form-control" name="province" value="<?php echo htmlspecialchars($record['province']); ?>" required maxlength="100">
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label class="form-label">City/Municipality</label>
            <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($record['city']); ?>" required maxlength="100">
        </div>
        
        <div class="col-md-6 col-lg-3">
            <label class="form-label">Barangay</label>
            <input type="text" class="form-control bg-light" name="barangay" value="<?php echo htmlspecialchars($record['barangay']); ?>" readonly required>
        </div>
        
        <!-- Power Supply Details -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3">
                <i class="fas fa-bolt me-2"></i>Power Supply Details
            </h6>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">Service Provider</label>
            <input type="text" class="form-control" name="service_provider" value="<?php echo htmlspecialchars($record['service_provider']); ?>" required maxlength="200">
        </div>
        
        <div class="col-md-6">
            <label class="form-label">Date/Time of Interruption</label>
            <input type="datetime-local" class="form-control" name="interruption_date" value="<?php echo $interruption_date_local; ?>" required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">Date/Time Restored</label>
            <input type="datetime-local" class="form-control" name="restored_date" value="<?php echo $restored_date_local; ?>">
        </div>
        
        <div class="col-md-6">
            <label class="form-label">Remarks</label>
            <input type="text" class="form-control" name="remarks" value="<?php echo htmlspecialchars($record['remarks'] ?? ''); ?>" maxlength="500">
        </div>

        <!-- Image Upload Section -->
        <div class="col-12 mt-4">
            <h6 class="text-primary border-bottom pb-2 mb-3">
                <i class="fas fa-image me-2"></i>Image Attachment
            </h6>
            
            <!-- Current Image Display - Will be dimmed when new image is selected -->
            <?php if ($current_image_exists): ?>
            <div class="current-image-section mb-3" id="currentImageSection">
                <div class="text-center mb-2">
                    <span class="image-badge badge-current">
                        <i class="fas fa-image me-2"></i>CURRENT IMAGE
                    </span>
                </div>
                <div class="text-center">
                    <img src="<?php echo $current_image_path; ?>" 
                         alt="Current image" 
                         style="max-width: 300px; max-height: 300px; cursor: pointer; border: 2px solid #0dcaf0; border-radius: 8px;"
                         class="img-thumbnail"
                         onclick="window.open(this.src, '_blank')">
                    <div class="mt-2">
                        <small class="text-muted">Click image to view full size</small>
                    </div>
                </div>
                <div class="form-check mt-3 text-center">
                    <input class="form-check-input" type="checkbox" name="remove_current_image" id="removeCurrentCheck" value="1">
                    <label class="form-check-label text-danger" for="removeCurrentCheck">
                        <i class="fas fa-trash me-1"></i>Remove current image (without replacing)
                    </label>
                </div>
                <hr class="my-4">
            </div>
            <?php else: ?>
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>No image currently attached
            </div>
            <?php endif; ?>

            <!-- NEW Image Preview - Shows CLEARLY when new image selected -->
            <div id="newImagePreview" style="display: none;">
                <div class="text-center mb-3">
                    <span class="image-badge badge-new">
                        <i class="fas fa-upload me-2"></i>NEW IMAGE - WILL REPLACE CURRENT
                    </span>
                </div>
                <div class="preview-container text-center mb-3">
                    <img id="imagePreview" class="preview-image" alt="New image preview">
                </div>
                <div class="alert alert-success text-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Ready to Upload!</strong>
                    <div id="fileName" class="small mt-2"></div>
                    <button type="button" class="btn btn-danger btn-sm mt-2" id="removeNewImageBtn">
                        <i class="fas fa-times me-1"></i> Cancel New Image
                    </button>
                </div>
                <hr class="my-4">
            </div>

            <!-- Upload Area - Click to select new image -->
            <div class="upload-area" id="uploadArea">
                <div class="text-center">
                    <i class="fas fa-cloud-upload-alt fa-5x text-primary mb-3"></i>
                    <h4 class="mb-2">Click to Upload <?php echo $current_image_exists ? 'Replacement' : 'New'; ?> Image</h4>
                    <p class="text-muted mb-1">JPG, JPEG, PNG, GIF (Max 5MB)</p>
                    <?php if ($current_image_exists): ?>
                    <p class="text-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>This will replace the current image above</strong>
                    </p>
                    <?php endif; ?>
                </div>
                
                <div id="errorArea" class="alert alert-danger mt-3" style="display: none;"></div>
            </div>
            
            <input type="file" id="imageInput" name="image" accept="image/*" style="display: none;">
        </div>
    </div>
    
    <div class="modal-footer mt-4">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="saveBtn">
            <i class="fas fa-save me-2"></i>Save Changes
        </button>
    </div>
</form>