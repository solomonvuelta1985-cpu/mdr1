<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/file_security.php'; // SECURITY FIX: Secure file operations
require_once '../includes/annex8_status_history.php'; // Status history tracking
require_once '../includes/notifications.php'; // Notification system

require_login();

// ========== DEBUG CSRF TOKEN ==========
error_log("=== CSRF DEBUG START ===");
error_log("Session ID: " . session_id());
error_log("User ID: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("CSRF Token in POST: " . ($_POST['csrf_token'] ?? 'NOT SET'));
error_log("CSRF Token in SESSION: " . ($_SESSION['csrf_token'] ?? 'NOT SET'));

// Test the verify_token function directly
$token_match = false;
if (isset($_POST['csrf_token']) && isset($_SESSION['csrf_token'])) {
    $token_match = hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    error_log("Tokens match: " . ($token_match ? 'YES' : 'NO'));
}

if (!isset($_POST['csrf_token'])) {
    error_log("ERROR: No CSRF token in POST data");
} elseif (!isset($_SESSION['csrf_token'])) {
    error_log("ERROR: No CSRF token in SESSION");
}
error_log("=== CSRF DEBUG END ===");
// ========== END DEBUG ==========

// Enhanced session fingerprint validation (with fallback)
if (!function_exists('validate_session_fingerprint') || !validate_session_fingerprint()) {
    // Fallback: basic session validation
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    log_security_event($_SESSION['user_id'], 'invalid_method', 'Annex8 save with non-POST method');
    exit('Method not allowed');
}

// ========== SINGLE CSRF VALIDATION ==========
if (!isset($_POST['csrf_token']) || !verify_token($_POST['csrf_token'])) {
    error_log("CSRF FAIL: Token validation failed");
    set_flash('Security token validation failed', 'error');
    log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex8 save CSRF token mismatch');
    header('Location: ../public/annex8.php');
    exit;
}
error_log("CSRF SUCCESS: Token validated");
// ========== END CSRF VALIDATION ==========

// Concurrent request limiting with fallback
if (!function_exists('check_concurrent_requests') || !check_concurrent_requests($_SESSION['user_id'], 'annex8_submission', 2)) {
    set_flash('Please complete your current submission before starting another one.', 'error');
    header('Location: ../public/annex8.php');
    exit;
}

// Rate limiting check (batch-level)
$action = 'annex8_submission';
if (!check_rate_limit($_SESSION['user_id'], $action, 10, 3600)) { // 10 submissions per hour
    set_flash('Too many submission attempts. Please try again later.', 'error');
    log_security_event($_SESSION['user_id'], 'rate_limit_exceeded', 'Annex8 submission rate limit exceeded');
    header('Location: ../public/annex8.php');
    exit;
}

// Get user's barangay
if (is_admin()) {
    $current_lock = get_admin_locked_barangay($_SESSION['user_id']);
    if (!$current_lock) {
        set_flash('Please select barangay from admin dashboard', 'error');
        header('Location: ../admin/barangay_locking.php');
        exit;
    }
    $user_barangay = $current_lock;
} else {
    $user_data = get_user_location_data($_SESSION['user_id']);
    $user_barangay = $user_data['barangay'];
}

// Create uploads directory if it doesn't exist
$upload_dir = '../uploads/annex8/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Additional directory security
file_put_contents($upload_dir . '.htaccess', "Deny from all\n<Files ~ \"\\.(jpg|jpeg|png|gif)$\">\nOrder allow,deny\nAllow from all\n</Files>");
file_put_contents($upload_dir . 'index.html', ''); // Prevent directory listing

try {
    // Begin transaction for multiple entries
    $pdo->beginTransaction();
    
    $success_count = 0;
    $error_count = 0;
    $submitted_barangays = [];
    
    // Process each entry with enhanced validation
    if (isset($_POST['region']) && is_array($_POST['region'])) {
        
        // Validate reasonable number of entries
        if (count($_POST['region']) > 50) {
            throw new Exception('Too many entries in single submission');
        }
        
        foreach ($_POST['region'] as $index => $region) {
            
            // Validate array index to prevent index manipulation attacks
            if (!is_numeric($index) || $index < 0 || $index > 100) {
                log_security_event($_SESSION['user_id'], 'suspicious_index', "Invalid array index: {$index}");
                $error_count++;
                continue;
            }
            
            // Verify all expected array keys exist for this index
            $required_keys = ['region', 'province', 'city', 'barangay', 'type', 'classification', 'roadSection', 'status'];
            foreach ($required_keys as $key) {
                if (!isset($_POST[$key][$index])) {
                    log_security_event($_SESSION['user_id'], 'missing_array_key', "Missing key {$key} for index {$index}");
                    $error_count++;
                    continue 2; // Skip to next iteration
                }
            }
            
            // Sanitize inputs
            $region = sanitize($region);
            $province = sanitize($_POST['province'][$index] ?? '');
            $city = sanitize($_POST['city'][$index] ?? '');
            $barangay = sanitize($_POST['barangay'][$index] ?? $user_barangay);
            $type = sanitize($_POST['type'][$index] ?? '');
            $classification = sanitize($_POST['classification'][$index] ?? '');
            $road_section = sanitize($_POST['roadSection'][$index] ?? '');
            $status = sanitize($_POST['status'][$index] ?? '');
            $date_not_passable = !empty($_POST['dateNotPassable'][$index]) ? date('Y-m-d H:i:s', strtotime($_POST['dateNotPassable'][$index])) : null;
            $date_passable = !empty($_POST['datePassable'][$index]) ? date('Y-m-d H:i:s', strtotime($_POST['datePassable'][$index])) : null;
            $remarks = sanitize($_POST['remarks'][$index] ?? '');
            
            // Validate required fields
            if (empty($region) || empty($province) || empty($city) || empty($barangay) || 
                empty($type) || empty($classification) || empty($road_section) || empty($status)) {
                log_security_event($_SESSION['user_id'], 'validation_skip', "Skipped entry index {$index}: missing required fields");
                $error_count++;
                continue;
            }
            
            // Enhanced enum value validation
            if (!validate_annex8_enum_values($type, $classification, $status)) {
                log_security_event($_SESSION['user_id'], 'validation_skip', "Skipped entry index {$index}: invalid enum values");
                $error_count++;
                continue;
            }
            
            // SECURITY FIX: Enhanced secure image upload with re-encoding
            $image_path = null;
            if (isset($_FILES['image']['name'][$index]) && $_FILES['image']['error'][$index] === UPLOAD_ERR_OK) {
                // Reconstruct single-file array for the validator
                $single_file = [
                    'name' => $_FILES['image']['name'][$index],
                    'tmp_name' => $_FILES['image']['tmp_name'][$index],
                    'size' => $_FILES['image']['size'][$index],
                    'error' => $_FILES['image']['error'][$index],
                    'type' => $_FILES['image']['type'][$index] ?? ''
                ];

                // Use secure upload function (validates, re-encodes, and sanitizes)
                $upload_result = safe_image_upload($single_file, 'annex8', "roadbridge_{$barangay}");

                if ($upload_result['success']) {
                    $image_path = $upload_result['path'];
                    log_audit_action(
                        $_SESSION['user_id'],
                        'annex8_image_uploaded',
                        "Uploaded image for entry index {$index}: {$upload_result['filename']}"
                    );
                } else {
                    log_security_event(
                        $_SESSION['user_id'],
                        'image_upload_failed',
                        "Failed to upload image for entry index {$index}: {$upload_result['error']}"
                    );
                }
            }
            
            // Insert into database
            $stmt = db_query("
                INSERT INTO annex8_road_bridge_status (
                    region, province, city, barangay, type, classification, 
                    road_section, status, date_not_passable, date_passable, 
                    remarks, image_path, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $region, $province, $city, $barangay, $type, $classification,
                $road_section, $status, $date_not_passable, $date_passable,
                $remarks, $image_path, $_SESSION['user_id']
            ]);
            
            // Treat success only if rows were affected
            if ($stmt && $stmt->rowCount() > 0) {
                $success_count++;
                $submitted_barangays[] = $barangay;

                // Get the ID of the newly inserted record
                $new_record_id = $pdo->lastInsertId();

                // Log initial status to history
                $status_date = ($status === 'Not Passable') ? $date_not_passable : $date_passable;

                // Use current time if no date provided
                if (!$status_date) {
                    $status_date = date('Y-m-d H:i:s');
                }

                // Log the initial status change
                $status_logged = log_status_change(
                    $new_record_id,
                    $status,
                    $status_date,
                    $remarks,
                    $_SESSION['user_id']
                );

                if (!$status_logged) {
                    error_log("Failed to log initial status for Annex8 record #{$new_record_id}");
                }

                // Log successful submission for this entry
                log_audit_action(
                    $_SESSION['user_id'],
                    'annex8_submission',
                    "Submitted road/bridge status for {$barangay}: {$type} - {$road_section} ({$status})"
                );

                // Create notification for admins and staff
                $notification_message = "{$type}: {$road_section} - Status: {$status}";
                create_notification(
                    'annex8_submission',
                    '8',
                    'Status of Roads and Bridges',
                    'created',
                    $barangay,
                    $_SESSION['user_id'],
                    $_SESSION['full_name'] ?? 'Unknown User',
                    $new_record_id,
                    $notification_message,
                    is_priority_annex('8')
                );
            } else {
                log_security_event($_SESSION['user_id'], 'db_insert_failed', "Failed to insert entry index {$index} for barangay {$barangay}");
                $error_count++;
            }
        }
    }
    
    $pdo->commit();
    
    // Log overall submission summary
    if ($success_count > 0) {
        $barangays_str = implode(', ', array_unique($submitted_barangays));
        log_audit_action(
            $_SESSION['user_id'],
            'annex8_batch_submission',
            "Successfully submitted {$success_count} road/bridge status reports for barangays: {$barangays_str}"
        );
        
        set_flash("Successfully saved $success_count road/bridge status report(s)", 'success');
    }
    
    if ($error_count > 0) {
        set_flash("$error_count entries failed to save due to validation or insertion errors", 'warning');
    }
    
    if ($success_count === 0 && $error_count === 0) {
        set_flash('No valid entries to save', 'warning');
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Annex8 save error for user {$_SESSION['user_id']}");
    
    // Log the error
    log_security_event(
        $_SESSION['user_id'],
        'annex8_submission_error',
        "Database error during submission"
    );
    
    set_flash('An error occurred while saving reports. Please try again.', 'error');
} finally {
    // Always release concurrent request
    release_concurrent_request($_SESSION['user_id'], 'annex8_submission');
}

header('Location: ../public/annex8.php');
exit;