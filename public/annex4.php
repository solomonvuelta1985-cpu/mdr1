<?php
// Define page title for the header
define('PAGE_TITLE', 'Annex 4 - Damaged Houses');

// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();

// Log form access for audit tracking
log_audit_action($_SESSION['user_id'], 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form');


// Security Functions (Add these to your functions.php file if not present)
if (!function_exists('check_rate_limit')) {
    function check_rate_limit($user_id, $action, $max_attempts = 10, $time_window = 300) {
        global $pdo;
        $time_window_ago = time() - $time_window;
        
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as attempt_count 
                FROM rate_limits 
                WHERE user_id = ? AND action = ? AND attempt_time > ?
            ");
            $stmt->execute([$user_id, $action, $time_window_ago]);
            $result = $stmt->fetch();
            
            if ($result['attempt_count'] >= $max_attempts) {
                return false;
            }
            
            // Log this attempt
            $stmt = $pdo->prepare("
                INSERT INTO rate_limits (user_id, action, attempt_time, ip_address) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $action, time(), $_SERVER['REMOTE_ADDR']]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Rate limit error: " . $e->getMessage());
            return true; // Fail open on error
        }
    }
}

if (!function_exists('log_audit_event')) {
    function log_audit_event($user_id, $action, $details = '') {
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (user_id, action, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id, 
                $action, 
                $details,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (PDOException $e) {
            error_log("Audit log error: " . $e->getMessage());
        }
    }
}

if (!function_exists('log_security_event')) {
    function log_security_event($user_id, $event_type, $details = '') {
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                INSERT INTO security_logs (user_id, event_type, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id, 
                $event_type, 
                $details,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (PDOException $e) {
            error_log("Security log error: " . $e->getMessage());
        }
    }
}

// Get user data and check barangay selection for admins
$user_data = get_user_location_data($_SESSION['user_id']);
$user_role = $user_data['user_role'] ?? 'user';

// Check if admin has selected a barangay
if (is_admin()) {
    $current_lock = get_admin_locked_barangay($_SESSION['user_id']);
    if (!$current_lock) {
        set_flash('Please select a barangay from the admin dashboard before filling out forms.', 'error');
        header('Location: ../admin/barangay_locking.php');
        exit;
    }
    $user_barangay = $current_lock;
} else {
    // Regular users use their assigned barangay
    $user_barangay = $user_data['barangay'] ?? '';
}

// Input validation and sanitization functions
function validate_damage_entry($entry) {
    $errors = [];
    
    // Required fields
    $required = ['region', 'province', 'city', 'barangay'];
    foreach ($required as $field) {
        if (empty($entry[$field])) {
            $errors[] = "$field is required";
        }
    }
    
    // Validate numbers
    if (isset($entry['totally_damaged']) && $entry['totally_damaged'] < 0) {
        $errors[] = "Totally damaged must be a positive number";
    }
    
    if (isset($entry['partially_damaged']) && $entry['partially_damaged'] < 0) {
        $errors[] = "Partially damaged must be a positive number";
    }
    
    // Validate lengths
    $max_lengths = [
        'region' => 100,
        'province' => 100,
        'city' => 100,
        'barangay' => 100,
        'cost' => 50,
        'remarks' => 500
    ];
    
    foreach ($max_lengths as $field => $max_length) {
        if (isset($entry[$field]) && strlen($entry[$field]) > $max_length) {
            $errors[] = "$field exceeds maximum length of $max_length characters";
        }
    }
    
    return $errors;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_token($_POST['csrf_token'])) {
        set_flash('Security token validation failed. Please try again.', 'error');
        header('Location: annex4.php');
        exit;
    }
    
    // Rate limiting - check if user has submitted too many requests
    if (!check_rate_limit($_SESSION['user_id'], 'annex4_submission', 10, 300)) { // 10 submissions per 5 minutes
        set_flash('Too many submission attempts. Please wait a few minutes before trying again.', 'error');
        header('Location: annex4.php');
        exit;
    }

    // Process each entry
    $success_count = 0;
    $error_count = 0;
    $validation_errors = [];
    
    if (isset($_POST['region']) && is_array($_POST['region'])) {
        foreach ($_POST['region'] as $index => $region) {
            // Skip empty entries (all fields empty)
            if (empty($region) && 
                empty($_POST['province'][$index]) && 
                empty($_POST['city'][$index]) && 
                empty($_POST['barangay'][$index])) {
                continue;
            }
            
            // Sanitize inputs
            $entry_data = [
                'region' => sanitize($region),
                'province' => sanitize($_POST['province'][$index] ?? ''),
                'city' => sanitize($_POST['city'][$index] ?? ''),
                'barangay' => sanitize($_POST['barangay'][$index] ?? ''),
                'totally_damaged' => intval($_POST['totallyDamaged'][$index] ?? 0),
                'partially_damaged' => intval($_POST['partiallyDamaged'][$index] ?? 0),
                'total_damaged' => intval($_POST['totalDamaged'][$index] ?? 0),
                'cost' => sanitize($_POST['cost'][$index] ?? ''),
                'remarks' => sanitize($_POST['remarks'][$index] ?? '')
            ];
            
            // Validate entry
            $entry_errors = validate_damage_entry($entry_data);
            if (!empty($entry_errors)) {
                $validation_errors = array_merge($validation_errors, $entry_errors);
                $error_count++;
                continue;
            }
            
            // Verify barangay access for non-admin users
            if (!is_admin() && $entry_data['barangay'] !== $user_barangay) {
                log_security_event($_SESSION['user_id'], 'unauthorized_barangay_access', 
                    "Attempted to submit data for barangay: " . $entry_data['barangay']);
                $error_count++;
                continue;
            }
            
            // Insert into database using prepared statement
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO annex4_damaged_houses 
                    (region, province, city, barangay, totally_damaged, partially_damaged, total_damaged, cost, remarks, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $result = $stmt->execute([
                    $entry_data['region'], 
                    $entry_data['province'], 
                    $entry_data['city'], 
                    $entry_data['barangay'],
                    $entry_data['totally_damaged'], 
                    $entry_data['partially_damaged'], 
                    $entry_data['total_damaged'],
                    $entry_data['cost'], 
                    $entry_data['remarks'], 
                    $_SESSION['user_id']
                ]);
                
                if ($result) {
                    $success_count++;
                    
                    // Log successful submission for audit
                    log_audit_event($_SESSION['user_id'], 'annex4_submission', 
                        "Submitted damaged houses report for " . $entry_data['barangay']);
                } else {
                    $error_count++;
                }
            } catch (PDOException $e) {
                // Log error but don't expose details to user
                error_log("Annex4 database error for user {$_SESSION['user_id']}: " . $e->getMessage());
                $error_count++;
            }
        }
    }
    
    // Set flash messages
    if ($success_count > 0) {
        set_flash("Successfully saved $success_count damaged house report(s).", 'success');
    }
    if ($error_count > 0) {
        $error_msg = "Failed to save $error_count report(s).";
        if (!empty($validation_errors)) {
            $error_msg .= " Validation errors: " . implode(', ', array_slice($validation_errors, 0, 3));
        }
        set_flash($error_msg, 'error');
    }
    
    header('Location: annex4.php');
    exit;
}

// Generate CSRF token
$csrf_token = generate_token();

// Start output buffering for sidenav
ob_start();
?>
<style>
    .form-container {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 12px rgba(0,0,0,0.15);
        padding: clamp(20px, 4vw, 25px);
        margin: 0 auto;
        width: 100%;
        max-width: 98vw;
        position: relative;
        min-height: 400px;
    }
    .header-section {
        text-align: center;
        margin-bottom: clamp(20px, 4vw, 25px);
        border-bottom: 2px solid #dee2e6;
        padding-bottom: clamp(10px, 2.5vw, 15px);
    }
    .header-section h1 {
        font-size: clamp(1.5rem, 4vw, 1.8rem);
        font-weight: bold;
        margin-bottom: 8px;
    }
    .header-section h2 {
        font-size: clamp(1.2rem, 3.5vw, 1.4rem);
        font-weight: bold;
        color: #333;
    }
    .entry-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: clamp(20px, 4vw, 25px);
        margin-bottom: clamp(20px, 4vw, 25px);
        background-color: #fff;
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    .section-title {
        font-size: clamp(1.1rem, 3vw, 1.2rem);
        font-weight: 600;
        color: #0d6efd;
        margin-bottom: 12px;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 6px;
    }
    .entry-card .row {
        row-gap: 15px;
    }
    .form-label {
        font-size: clamp(0.95rem, 2.5vw, 1rem);
        font-weight: 500;
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        font-size: clamp(0.9rem, 2.5vw, 1rem);
        padding: clamp(8px, 2vw, 10px);
        width: 100%;
        border-radius: 6px;
        height: 48px;
    }
    .form-control::placeholder {
        color: #6c757d;
        opacity: 0.7;
    }
    .number-input {
        text-align: right;
    }
    .btn {
        font-size: clamp(0.95rem, 2.5vw, 1rem);
        padding: clamp(10px, 2.5vw, 12px) clamp(20px, 4vw, 25px);
        border-radius: 6px;
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-add-entry {
        background-color: #0d6efd;
        border: none;
    }
    .btn-add-entry:hover {
        background-color: #0b5ed7;
    }
    .btn-remove {
        background-color: #dc3545;
        color: white;
        border: none;
    }
    .btn-remove:hover {
        background-color: #c82333;
    }
    .btn-submit {
        background-color: #198754;
        border: none;
    }
    .btn-submit:hover {
        background-color: #157347;
    }
    .btn-technical-notes {
        background-color: #6c757d;
        border: none;
    }
    .btn-technical-notes:hover {
        background-color: #5a6268;
    }
    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        margin-top: clamp(15px, 3vw, 20px);
        gap: 15px;
    }
    .action-buttons > div {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
        cursor: not-allowed;
    }
    
    /* Skeleton Loader Styles - FIXED */
    .skeleton-loader {
        position: relative;
        width: 100%;
        background: white;
        border-radius: 10px;
        padding: clamp(20px, 4vw, 25px);
    }
    
    .skeleton-entry {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: clamp(20px, 4vw, 25px);
        margin-bottom: clamp(20px, 4vw, 25px);
        background-color: #fff;
    }
    
    .skeleton-line {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    
    .skeleton-title {
        height: 24px;
        width: 40%;
        margin-bottom: 20px;
    }
    
    .skeleton-label {
        height: 16px;
        width: 60%;
        margin-bottom: 8px;
    }
    
    .skeleton-input {
        height: 48px;
        width: 100%;
    }
    
    .skeleton-button {
        height: 48px;
        width: 150px;
        border-radius: 6px;
    }
    
    .skeleton-action-buttons {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        margin-top: clamp(15px, 3vw, 20px);
        gap: 15px;
    }
    
    .skeleton-action-buttons > div {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    @keyframes loading {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }
    
    /* Fade in animation for form content */
    .form-content {
        opacity: 0;
        transform: translateY(10px);
        transition: opacity 0.5s ease, transform 0.5s ease;
        display: none;
    }
    
    .form-content.loaded {
        opacity: 1;
        transform: translateY(0);
        display: block;
    }
    
    /* Lazy loading for new entries */
    .lazy-entry {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.4s ease, transform 0.4s ease;
    }
    
    .lazy-entry.loaded {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .action-buttons, .skeleton-action-buttons {
            flex-direction: column;
            align-items: stretch;
        }
        .action-buttons > div, .skeleton-action-buttons > div {
            flex-direction: column;
        }
        .btn, .skeleton-button {
            width: 100%;
        }
        .entry-card .row > div {
            min-width: 0;
        }
    }
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 0.5rem;
        }
        .modal-body .row {
            flex-direction: column;
        }
        .modal-body .col-md-6 {
            margin-bottom: 15px;
        }
    }
</style>

<div class="form-container">
            <!-- Skeleton Loader for Initial Page Load -->
            <div id="pageSkeletonLoader" class="skeleton-loader">
                <!-- Skeleton Header -->
                <div class="header-section">
                    <div class="skeleton-line skeleton-title" style="width: 70%; margin: 0 auto 8px;"></div>
                    <div class="skeleton-line skeleton-title" style="width: 50%; margin: 0 auto;"></div>
                </div>
                
                <!-- Skeleton Entry Card -->
                <div class="skeleton-entry">
                    <div class="skeleton-line skeleton-title" style="width: 30%;"></div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <div class="skeleton-line skeleton-label"></div>
                            <div class="skeleton-line skeleton-input"></div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="skeleton-line skeleton-label"></div>
                            <div class="skeleton-line skeleton-input"></div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="skeleton-line skeleton-label"></div>
                            <div class="skeleton-line skeleton-input"></div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="skeleton-line skeleton-label"></div>
                            <div class="skeleton-line skeleton-input"></div>
                        </div>
                    </div>
                    
                    <div class="skeleton-line skeleton-title mt-4" style="width: 25%;"></div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <div class="skeleton-line skeleton-label"></div>
                            <div class="skeleton-line skeleton-input"></div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="skeleton-line skeleton-label"></div>
                            <div class="skeleton-line skeleton-input"></div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="skeleton-line skeleton-label"></div>
                            <div class="skeleton-line skeleton-input"></div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="skeleton-line skeleton-label"></div>
                            <div class="skeleton-line skeleton-input"></div>
                        </div>
                    </div>
                    
                    <div class="skeleton-line skeleton-title mt-4" style="width: 35%;"></div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="skeleton-line skeleton-label"></div>
                            <div class="skeleton-line skeleton-input"></div>
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <div class="skeleton-line skeleton-button" style="width: 140px;"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Skeleton Action Buttons -->
                <div class="skeleton-action-buttons">
                    <div>
                        <div class="skeleton-line skeleton-button" style="width: 130px;"></div>
                        <div class="skeleton-line skeleton-button" style="width: 180px;"></div>
                    </div>
                    <div class="skeleton-line skeleton-button" style="width: 130px;"></div>
                </div>
            </div>

            <!-- Actual Form Content (Initially Hidden) -->
            <div id="formContent" class="form-content">
                <div class="header-section">
                    <h1>NDRRMC Memorandum Circular No. 05, s. 2025</h1>
                    <h2>Annex 4: Damaged Houses</h2>
                    
                    <?php if ($user_role === 'admin'): ?>
                        <div class="alert alert-info mt-3">
                            <strong>Admin Mode:</strong> You are currently reporting for 
                            <strong><?= htmlspecialchars($user_barangay) ?></strong> barangay.
                            <br>
                            <small class="text-muted">
                                To change barangay, visit the 
                                <a href="../admin/barangay_locking.php" class="alert-link">Barangay Locking Management</a> page.
                            </small>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Flash Messages -->
                <?php show_flash(); ?>

                <form id="damagedHousesForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div id="entriesContainer">
                        <!-- Initial entry -->
                        <div class="entry-card lazy-entry loaded">
                            <div class="section-title">Location Details</div>
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-3">
                                    <label for="region-0" class="form-label">Region</label>
                                    <?php if ($user_role === 'admin'): ?>
                                        <input type="text" class="form-control" id="region-0" name="region[]" value="REGION II" required maxlength="100">
                                    <?php else: ?>
                                        <input type="text" class="form-control bg-light" id="region-0" name="region[]" value="REGION II" readonly>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="province-0" class="form-label">Province</label>
                                    <?php if ($user_role === 'admin'): ?>
                                        <input type="text" class="form-control" id="province-0" name="province[]" value="CAGAYAN" required maxlength="100">
                                    <?php else: ?>
                                        <input type="text" class="form-control bg-light" id="province-0" name="province[]" value="CAGAYAN" readonly>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="city-0" class="form-label">City/Municipality</label>
                                    <?php if ($user_role === 'admin'): ?>
                                        <input type="text" class="form-control" id="city-0" name="city[]" value="BAGGAO" required maxlength="100">
                                    <?php else: ?>
                                        <input type="text" class="form-control bg-light" id="city-0" name="city[]" value="BAGGAO" readonly>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="barangay-0" class="form-label">Barangay</label>
                                    <input type="text" class="form-control bg-light" id="barangay-0" name="barangay[]" 
                                        value="<?= htmlspecialchars($user_barangay) ?>" readonly>
                                </div>
                            </div>

                            <div class="section-title mt-4">Damage Details</div>
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-3">
                                    <label for="totallyDamaged-0" class="form-label">Totally Damaged</label>
                                    <input type="number" class="form-control number-input" id="totallyDamaged-0" name="totallyDamaged[]" placeholder="0" min="0" max="999999" onchange="calculateTotal(this)">
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="partiallyDamaged-0" class="form-label">Partially Damaged</label>
                                    <input type="number" class="form-control number-input" id="partiallyDamaged-0" name="partiallyDamaged[]" placeholder="0" min="0" max="999999" onchange="calculateTotal(this)">
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="totalDamaged-0" class="form-label">Total Damaged</label>
                                    <input type="number" class="form-control number-input" id="totalDamaged-0" name="totalDamaged[]" placeholder="0" readonly>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="cost-0" class="form-label">Cost</label>
                                    <input type="text" class="form-control" id="cost-0" name="cost[]" placeholder="Enter cost" maxlength="50">
                                </div>
                            </div>

                            <div class="section-title mt-4">Additional Information</div>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="remarks-0" class="form-label">Remarks</label>
                                    <input type="text" class="form-control" id="remarks-0" name="remarks[]" placeholder="Enter remarks" maxlength="500">
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-12">
                                    <button type="button" class="btn btn-remove" onclick="removeEntry(this)">Remove Entry</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <div>
                            <button type="button" class="btn btn-primary btn-add-entry" id="addEntryBtn">Add Entry</button>
                            <button type="button" class="btn btn-secondary btn-technical-notes" data-bs-toggle="modal" data-bs-target="#technicalNotesModal">
                                View Technical Notes
                            </button>
                        </div>
                        <button type="submit" class="btn btn-success btn-submit">Save Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Technical Notes Modal -->
    <div class="modal fade" id="technicalNotesModal" tabindex="-1" aria-labelledby="technicalNotesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="technicalNotesModalLabel">Technical Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Region, Province, City/Municipality, Barangay</strong><br>
                            Specify the exact location of the reported damaged houses.</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Number of Damaged Houses</strong><br>
                            Specify the number of totally and partially damaged houses and its total.</p>
                            <p><strong>Cost</strong><br>
                            Indicate the total cost of damage if available.</p>
                            <p><strong>Remarks</strong><br>
                            Include any other relevant details or contextual information about the damaged houses.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let entryCount = 1;

        // Initialize page with skeleton loader
        document.addEventListener('DOMContentLoaded', function() {
            // Show skeleton loader initially
            const skeletonLoader = document.getElementById('pageSkeletonLoader');
            const formContent = document.getElementById('formContent');
            
            skeletonLoader.style.display = 'block';
            formContent.style.display = 'none';
            
            // Simulate page loading (in a real app, this would be actual data loading)
            setTimeout(function() {
                // Hide skeleton loader
                skeletonLoader.style.display = 'none';

                // Show form content with fade-in animation
                formContent.style.display = 'block';
                setTimeout(function() {
                    formContent.classList.add('loaded');
                    // Initialize cost formatting after form is loaded
                    initializeCostFormatting();
                }, 50);
            }, 1200); // 1.2 second loading simulation
        });

        // Add entry functionality with skeleton loader
        document.getElementById('addEntryBtn').addEventListener('click', function() {
            const entriesContainer = document.getElementById('entriesContainer');
            const button = this;
            
            // Disable button during loading
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
            
            // Create skeleton entry
            const skeletonEntry = document.createElement('div');
            skeletonEntry.className = 'entry-card skeleton-entry';
            skeletonEntry.innerHTML = `
                <div class="skeleton-line skeleton-title" style="width: 30%;"></div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 25%;"></div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 35%;"></div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <div class="skeleton-line skeleton-button" style="width: 140px;"></div>
                    </div>
                </div>
            `;
            
            entriesContainer.appendChild(skeletonEntry);
            
            // Simulate loading delay for new entry
            setTimeout(function() {
                // Remove skeleton
                skeletonEntry.remove();
                
                // Add actual entry
                const newEntry = document.createElement('div');
                newEntry.className = 'entry-card lazy-entry';
                newEntry.innerHTML = `
                    <div class="section-title">Location Details</div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <label for="region-${entryCount}" class="form-label">Region</label>
                            <?php if ($user_role === 'admin'): ?>
                                <input type="text" class="form-control" id="region-${entryCount}" name="region[]" value="REGION II" required maxlength="100">
                            <?php else: ?>
                                <input type="text" class="form-control bg-light" id="region-${entryCount}" name="region[]" value="REGION II" readonly>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="province-${entryCount}" class="form-label">Province</label>
                            <?php if ($user_role === 'admin'): ?>
                                <input type="text" class="form-control" id="province-${entryCount}" name="province[]" value="CAGAYAN" required maxlength="100">
                            <?php else: ?>
                                <input type="text" class="form-control bg-light" id="province-${entryCount}" name="province[]" value="CAGAYAN" readonly>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="city-${entryCount}" class="form-label">City/Municipality</label>
                            <?php if ($user_role === 'admin'): ?>
                                <input type="text" class="form-control" id="city-${entryCount}" name="city[]" value="BAGGAO" required maxlength="100">
                            <?php else: ?>
                                <input type="text" class="form-control bg-light" id="city-${entryCount}" name="city[]" value="BAGGAO" readonly>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="barangay-${entryCount}" class="form-label">Barangay</label>
                            <input type="text" class="form-control bg-light" id="barangay-${entryCount}" name="barangay[]"
                                value="<?= htmlspecialchars($user_barangay) ?>" readonly>
                        </div>
                    </div>

                    <div class="section-title mt-4">Damage Details</div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <label for="totallyDamaged-${entryCount}" class="form-label">Totally Damaged</label>
                            <input type="number" class="form-control number-input" id="totallyDamaged-${entryCount}" name="totallyDamaged[]" placeholder="0" min="0" max="999999" onchange="calculateTotal(this)">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="partiallyDamaged-${entryCount}" class="form-label">Partially Damaged</label>
                            <input type="number" class="form-control number-input" id="partiallyDamaged-${entryCount}" name="partiallyDamaged[]" placeholder="0" min="0" max="999999" onchange="calculateTotal(this)">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="totalDamaged-${entryCount}" class="form-label">Total Damaged</label>
                            <input type="number" class="form-control number-input" id="totalDamaged-${entryCount}" name="totalDamaged[]" placeholder="0" readonly>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="cost-${entryCount}" class="form-label">Cost</label>
                            <input type="text" class="form-control" id="cost-${entryCount}" name="cost[]" placeholder="Enter cost" maxlength="50">
                        </div>
                    </div>

                    <div class="section-title mt-4">Additional Information</div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="remarks-${entryCount}" class="form-label">Remarks</label>
                            <input type="text" class="form-control" id="remarks-${entryCount}" name="remarks[]" placeholder="Enter remarks" maxlength="500">
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-remove" onclick="removeEntry(this)">Remove Entry</button>
                        </div>
                    </div>
                `;
                entriesContainer.appendChild(newEntry);

                // Trigger lazy loading animation
                setTimeout(function() {
                    newEntry.classList.add('loaded');
                    // Initialize cost formatting for the new entry
                    const newCostInput = newEntry.querySelector('input[name="cost[]"]');
                    if (newCostInput) {
                        newCostInput.addEventListener('blur', function() {
                            formatCost(this);
                        });
                        newCostInput.addEventListener('input', function() {
                            // Allow only numbers and decimal point
                            this.value = this.value.replace(/[^0-9.]/g, '');
                        });
                    }
                }, 50);

                // Re-enable button
                button.disabled = false;
                button.innerHTML = 'Add Entry';

                entryCount++;
            }, 800); // 800ms loading delay for new entries
        });

        // Remove entry functionality with animation
        function removeEntry(button) {
            const entry = button.closest('.entry-card');
            const entriesContainer = document.getElementById('entriesContainer');
            
            // Don't remove if it's the only entry
            if (entriesContainer.children.length > 1) {
                // Add fade-out animation
                entry.style.opacity = '0';
                entry.style.transform = 'translateY(-20px)';
                
                setTimeout(function() {
                    entry.remove();
                }, 300);
            } else {
                alert("You need at least one entry in the form.");
            }
        }

        // Calculate total damaged houses
        function calculateTotal(input) {
            const entry = input.closest('.entry-card');
            const totallyInput = entry.querySelector('input[name="totallyDamaged[]"]');
            const partiallyInput = entry.querySelector('input[name="partiallyDamaged[]"]');
            const totalInput = entry.querySelector('input[name="totalDamaged[]"]');

            const totally = parseInt(totallyInput.value) || 0;
            const partially = parseInt(partiallyInput.value) || 0;
            const total = totally + partially;

            totalInput.value = total;
        }

        // Format cost with commas
        function formatCost(input) {
            let value = input.value.replace(/,/g, ''); // Remove existing commas
            if (value === '' || isNaN(value)) {
                input.value = '';
                return;
            }
            // Format with commas
            input.value = parseFloat(value).toLocaleString('en-US');
        }

        // Initialize cost formatting for existing entries
        function initializeCostFormatting() {
            const costInputs = document.querySelectorAll('input[name="cost[]"]');
            costInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    formatCost(this);
                });
                input.addEventListener('input', function() {
                    // Allow only numbers and decimal point
                    this.value = this.value.replace(/[^0-9.]/g, '');
                });
            });
        }

        // Handle form submission with loading state
        document.getElementById('damagedHousesForm').addEventListener('submit', function(e) {
            const submitButton = this.querySelector('.btn-submit');
            const originalText = submitButton.innerHTML;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            
            // Form will submit normally, the button will reset on page reload
        });

        // Client-side validation
        document.getElementById('damagedHousesForm').addEventListener('submit', function(e) {
            let isValid = true;
            const entries = this.querySelectorAll('.entry-card');
            
            entries.forEach((entry, index) => {
                const region = entry.querySelector('input[name="region[]"]');
                const province = entry.querySelector('input[name="province[]"]');
                const city = entry.querySelector('input[name="city[]"]');
                const barangay = entry.querySelector('input[name="barangay[]"]');
                
                // Skip validation if all fields are empty
                if (!region.value && !province.value && !city.value && !barangay.value) {
                    return;
                }
                
                // Check required fields
                if (!region.value || !province.value || !city.value || !barangay.value) {
                    isValid = false;
                    alert(`Please fill all location fields in entry ${index + 1}`);
                    return;
                }
                
                // Check number ranges
                const totally = entry.querySelector('input[name="totallyDamaged[]"]');
                const partially = entry.querySelector('input[name="partiallyDamaged[]"]');
                
                if (totally.value < 0 || totally.value > 999999) {
                    isValid = false;
                    alert(`Totally damaged value must be between 0 and 999,999 in entry ${index + 1}`);
                    return;
                }
                
                if (partially.value < 0 || partially.value > 999999) {
                    isValid = false;
                    alert(`Partially damaged value must be between 0 and 999,999 in entry ${index + 1}`);
                    return;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                const submitButton = this.querySelector('.btn-submit');
                submitButton.disabled = false;
                submitButton.innerHTML = 'Save Report';
            }
        });
    </script>

<?php
// Get the captured content and store it in a variable
$content = ob_get_clean();

// Now include the sidenav layout which will wrap this content
require_once '../includes/sidenav.php';
?>