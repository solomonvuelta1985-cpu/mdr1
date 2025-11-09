<?php
/**
 * Annex 10: Status of Communication Lines Report Form
 * Clean table-based design
 */

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// Get user data
$user_id = $_SESSION['user_id'];
$user_data = get_user_location_data($user_id);
$is_admin = is_admin();

// Centralized barangay locking check for admins
if ($is_admin) {
    $user_barangay = get_admin_locked_barangay($user_id);
    if (!$user_barangay) {
        set_flash('Please select a barangay from the admin dashboard first.', 'error');
        header('Location: ../admin/barangay_locking.php');
        exit;
    }
} else {
    $user_barangay = $user_data['barangay'];
}

// Auto-fill location data
$region = $user_data['region'] ?? 'Region II';
$province = $user_data['province'] ?? 'Cagayan';
$city = $user_data['city'] ?? 'Baggao';

// Generate CSRF token
$csrf_token = generate_token();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        set_flash('Security token validation failed', 'error');
        header('Location: annex10.php');
        exit;
    }

    // Sanitize inputs
    $communication_type = sanitize($_POST['communication_type']);
    $provider = sanitize($_POST['provider']);
    $status = sanitize($_POST['status']);
    $affected_area = sanitize($_POST['affected_area']);
    $reported_time = sanitize($_POST['reported_time']);
    $restored_time = sanitize($_POST['restored_time'] ?? null);
    $cause_of_disruption = sanitize($_POST['cause_of_disruption'] ?? '');
    $remarks = sanitize($_POST['remarks'] ?? '');

    // Create table if not exists
    try {
        db_query("CREATE TABLE IF NOT EXISTS annex10_communication_lines (
            id INT AUTO_INCREMENT PRIMARY KEY,
            region VARCHAR(100) NOT NULL,
            province VARCHAR(100) NOT NULL,
            city VARCHAR(100) NOT NULL,
            barangay VARCHAR(100) NOT NULL,
            communication_type VARCHAR(100) NOT NULL,
            provider VARCHAR(100) NOT NULL,
            status ENUM('Normal', 'Disrupted', 'Partially Disrupted', 'Offline') NOT NULL,
            affected_area VARCHAR(200),
            reported_time DATETIME,
            restored_time DATETIME NULL,
            cause_of_disruption TEXT,
            remarks TEXT,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_archived TINYINT(1) DEFAULT 0,
            FOREIGN KEY (created_by) REFERENCES users(id)
        )");
    } catch (Exception $e) {
        error_log("Table creation error: " . $e->getMessage());
    }

    // Insert record
    try {
        $stmt = db_query(
            "INSERT INTO annex10_communication_lines
            (region, province, city, barangay, communication_type, provider, status, affected_area,
             reported_time, restored_time, cause_of_disruption, remarks, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $region, $province, $city, $user_barangay, $communication_type, $provider, $status,
                $affected_area, $reported_time, $restored_time, $cause_of_disruption, $remarks, $user_id
            ]
        );

        log_audit_action($user_id, 'annex10_create', "Created communication line status report: {$communication_type} - {$provider}");

        set_flash('Communication line status report submitted successfully!', 'success');
        header('Location: annex10_records.php');
        exit;
    } catch (Exception $e) {
        set_flash('Failed to submit report: ' . $e->getMessage(), 'error');
        error_log("Annex 10 submission error: " . $e->getMessage());
    }
}

// Log form access
log_audit_action($user_id, 'annex10_form_access', "Accessed Annex 10 form for barangay: {$user_barangay}");

// Start output buffering for sidenav
ob_start();
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    .form-container {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: clamp(20px, 4vw, 30px);
        margin: 0 auto;
        max-width: 900px;
    }

    .header-section {
        text-align: center;
        margin-bottom: 25px;
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 15px;
    }

    .header-section h1 {
        font-size: clamp(1.3rem, 3.5vw, 1.6rem);
        font-weight: 600;
        margin-bottom: 8px;
        color: #212529;
    }

    .header-section h2 {
        font-size: clamp(1.1rem, 3vw, 1.3rem);
        font-weight: 600;
        color: #495057;
    }

    .section-title {
        background: #f8f9fa;
        padding: 10px 15px;
        border-left: 4px solid #0d6efd;
        margin: 25px 0 15px 0;
        font-weight: 600;
        color: #495057;
        border-radius: 4px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-label {
        font-weight: 600;
        margin-bottom: 6px;
        color: #495057;
        font-size: 0.9rem;
    }

    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
    }

    .readonly-field {
        background-color: #e9ecef;
        cursor: not-allowed;
    }

    .btn {
        padding: 10px 20px;
        font-weight: 500;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #0d6efd;
        border: 1px solid #0d6efd;
        color: white;
    }

    .btn-primary:hover {
        background: #0b5ed7;
        border-color: #0b5ed7;
    }

    .btn-secondary {
        background: #6c757d;
        border: 1px solid #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5c636a;
        border-color: #5c636a;
    }

    .required {
        color: #dc3545;
    }

    @media (max-width: 768px) {
        .form-container {
            padding: 15px;
        }
    }
</style>

<div class="container-fluid mt-4">
    <div class="form-container">
        <!-- Header -->
        <div class="header-section">
            <h1>NDRRMC Memorandum Circular</h1>
            <h2>Annex 10: Status of Communication Lines</h2>
        </div>

        <?php show_flash(); ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <!-- Location Information -->
            <div class="section-title">📍 Location Information</div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Region</label>
                        <input type="text" class="form-control readonly-field"
                               value="<?php echo htmlspecialchars($region); ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Province</label>
                        <input type="text" class="form-control readonly-field"
                               value="<?php echo htmlspecialchars($province); ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">City / Municipality</label>
                        <input type="text" class="form-control readonly-field"
                               value="<?php echo htmlspecialchars($city); ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Barangay</label>
                        <input type="text" class="form-control readonly-field"
                               value="<?php echo htmlspecialchars($user_barangay); ?>" readonly>
                    </div>
                </div>
            </div>

            <!-- Communication Line Details -->
            <div class="section-title">📡 Communication Line Details</div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Type of Communication <span class="required">*</span></label>
                        <select class="form-select" name="communication_type" required>
                            <option value="">Select Type</option>
                            <option value="Mobile Network">Mobile Network</option>
                            <option value="Landline">Landline</option>
                            <option value="Internet">Internet</option>
                            <option value="Radio Communication">Radio Communication</option>
                            <option value="Satellite Communication">Satellite Communication</option>
                            <option value="Emergency Hotline">Emergency Hotline</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Service Provider <span class="required">*</span></label>
                        <input type="text" class="form-control" name="provider"
                               placeholder="e.g., Globe, Smart, PLDT" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select class="form-select" name="status" required>
                            <option value="">Select Status</option>
                            <option value="Normal">Normal</option>
                            <option value="Partially Disrupted">Partially Disrupted</option>
                            <option value="Disrupted">Disrupted</option>
                            <option value="Offline">Offline</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Affected Area</label>
                        <input type="text" class="form-control" name="affected_area"
                               placeholder="Specific location or coverage area">
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="section-title">⏰ Timeline</div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Reported Time <span class="required">*</span></label>
                        <input type="datetime-local" class="form-control" name="reported_time" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Restored Time (if applicable)</label>
                        <input type="datetime-local" class="form-control" name="restored_time">
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="section-title">📝 Additional Information</div>

            <div class="form-group">
                <label class="form-label">Cause of Disruption</label>
                <textarea class="form-control" name="cause_of_disruption" rows="3"
                          placeholder="e.g., Weather-related damage, equipment failure, maintenance"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Remarks</label>
                <textarea class="form-control" name="remarks" rows="3"
                          placeholder="Additional notes or observations"></textarea>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="annex10_records.php" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Submit Report
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once '../includes/sidenav.php';
?>
