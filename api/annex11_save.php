<?php
/**
 * API Handler: Save Communication Lines Report (Annex 11)
 * FULLY SECURED with Audit Logs, Rate Limiting, and Security Logging
 */

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;");

// Load dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// SECURITY LOG: Access attempt
log_audit_action(
    $_SESSION['user_id'],
    'annex11_save_access',
    "Accessed Annex 11 save handler"
);

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_security_event($_SESSION['user_id'], 'invalid_request_method', 'Attempted non-POST request to annex11_save.php');
    set_flash('Invalid request method', 'error');
    header('Location: ../public/annex11.php');
    exit;
}

// CSRF token verification
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event(
        $_SESSION['user_id'],
        'csrf_validation_failed',
        'CSRF token validation failed for Annex 11 submission'
    );
    set_flash('Security token validation failed. Please try again.', 'error');
    header('Location: ../public/annex11.php');
    exit;
}

// Rate limiting check
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex11_save', 20, 3600)) {
    log_security_event(
        $user_id,
        'rate_limit_exceeded',
        'Annex 11 submission rate limit exceeded (20 per hour)'
    );
    set_flash('Too many submissions. Please wait before submitting again.', 'error');
    header('Location: ../public/annex11.php');
    exit;
}

// Get user data
$user_id = $_SESSION['user_id'];
$user_data = get_user_location_data($user_id);
$user_barangay = sanitize($_POST['user_barangay'] ?? '');

// Verify barangay access for admins
if (is_admin()) {
    $current_lock = get_admin_locked_barangay($user_id);
    if (!$current_lock) {
        log_security_event(
            $user_id,
            'barangay_lock_missing',
            'Admin attempted Annex 11 submission without barangay lock'
        );
        set_flash('Please select a barangay from the admin dashboard first.', 'error');
        header('Location: ../admin/barangay_locking.php');
        exit;
    }
    if ($current_lock !== $user_barangay) {
        log_security_event(
            $user_id,
            'barangay_mismatch',
            "Barangay mismatch: Locked=$current_lock, Submitted=$user_barangay"
        );
        set_flash('Invalid barangay selection. Please select a barangay from the admin dashboard.', 'error');
        header('Location: ../admin/barangay_locking.php');
        exit;
    }
}

// Validate input arrays exist
$required_arrays = ['region', 'province', 'city', 'barangay', 'telecom', 'interruptionDate'];
foreach ($required_arrays as $field) {
    if (!isset($_POST[$field]) || !is_array($_POST[$field]) || empty($_POST[$field])) {
        log_security_event(
            $user_id,
            'invalid_form_data',
            "Missing or invalid field: $field in Annex 11 submission"
        );
        set_flash("Missing or invalid field: $field", 'error');
        header('Location: ../public/annex11.php');
        exit;
    }
}

// Get array data
$regions = $_POST['region'];
$provinces = $_POST['province'];
$cities = $_POST['city'];
$barangays = $_POST['barangay'];
$telecoms = $_POST['telecom'];
$interruptionDates = $_POST['interruptionDate'];

// Optional fields
$restoredDates = $_POST['restoredDate'] ?? [];
$remarks = $_POST['remarks'] ?? [];

// Validate all arrays have same length
$entryCount = count($regions);
if (
    count($provinces) !== $entryCount ||
    count($cities) !== $entryCount ||
    count($barangays) !== $entryCount ||
    count($telecoms) !== $entryCount ||
    count($interruptionDates) !== $entryCount
) {
    log_security_event(
        $user_id,
        'data_manipulation_detected',
        'Array length mismatch in Annex 11 submission - possible data manipulation'
    );
    set_flash('Data mismatch detected. Please try again.', 'error');
    header('Location: ../public/annex11.php');
    exit;
}

// Limit number of entries (prevent abuse)
if ($entryCount > 50) {
    log_security_event(
        $user_id,
        'excessive_entries',
        "Attempted to submit $entryCount entries (limit: 50)"
    );
    set_flash('Maximum 50 entries allowed per submission.', 'error');
    header('Location: ../public/annex11.php');
    exit;
}

// Check for suspiciously low entry count (possible bot)
if ($entryCount === 0) {
    log_security_event(
        $user_id,
        'empty_submission',
        'Attempted to submit form with zero entries'
    );
    set_flash('Please add at least one entry.', 'error');
    header('Location: ../public/annex11.php');
    exit;
}

// Valid telecom providers
$valid_telecoms = ['PLDT', 'Globe', 'Smart', 'DITO', 'Other'];

// Begin transaction
try {
    $pdo->beginTransaction();
    
    // Prepare insert statement
    $stmt = $pdo->prepare("
        INSERT INTO annex11_communication_lines (
            created_by, region, province, city, barangay,
            telecom_provider, interruption_date, restored_date, remarks,
            created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            NOW(), NOW()
        )
    ");
    
    $inserted_count = 0;
    $inserted_ids = [];
    $errors = [];
    $validation_failures = [];
    
    // Process each entry
    for ($i = 0; $i < $entryCount; $i++) {
        // Sanitize and validate required fields
        $region = sanitize($regions[$i]);
        $province = sanitize($provinces[$i]);
        $city = sanitize($cities[$i]);
        $barangay = sanitize($barangays[$i]);
        $telecom = sanitize($telecoms[$i]);
        $interruptionDate = sanitize($interruptionDates[$i]);
        
        // Validation checks
        if (empty($region) || strlen($region) > 100) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid region";
            $validation_failures[] = "Entry " . ($i + 1) . ": region validation failed";
            continue;
        }
        
        if (empty($province) || strlen($province) > 100) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid province";
            $validation_failures[] = "Entry " . ($i + 1) . ": province validation failed";
            continue;
        }
        
        if (empty($city) || strlen($city) > 100) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid city/municipality";
            $validation_failures[] = "Entry " . ($i + 1) . ": city validation failed";
            continue;
        }
        
        if (empty($barangay) || strlen($barangay) > 100) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid barangay";
            $validation_failures[] = "Entry " . ($i + 1) . ": barangay validation failed";
            continue;
        }
        
        // CRITICAL: Verify barangay matches user's barangay (prevent data manipulation)
        if ($barangay !== $user_barangay) {
            $errors[] = "Entry " . ($i + 1) . ": Barangay mismatch";
            $validation_failures[] = "Entry " . ($i + 1) . ": barangay mismatch - attempted $barangay, expected $user_barangay";
            log_security_event(
                $user_id,
                'barangay_manipulation_attempt',
                "User attempted to submit data for barangay '$barangay' but locked to '$user_barangay'"
            );
            continue;
        }
        
        if (!in_array($telecom, $valid_telecoms)) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid telecom provider";
            $validation_failures[] = "Entry " . ($i + 1) . ": invalid telecom provider '$telecom'";
            continue;
        }
        
        // Validate datetime format for interruption date
        $interruptionDateTime = DateTime::createFromFormat('Y-m-d\TH:i', $interruptionDate);
        if (!$interruptionDateTime) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid interruption date format";
            $validation_failures[] = "Entry " . ($i + 1) . ": interruption date format validation failed";
            continue;
        }
        $interruptionDateFormatted = $interruptionDateTime->format('Y-m-d H:i:s');
        
        // Optional fields - sanitize and validate
        $restoredDate = null;
        if (isset($restoredDates[$i]) && !empty($restoredDates[$i])) {
            $restoredDateTime = DateTime::createFromFormat('Y-m-d\TH:i', sanitize($restoredDates[$i]));
            if ($restoredDateTime) {
                $restoredDate = $restoredDateTime->format('Y-m-d H:i:s');
                
                // Validate that restored date is after interruption date
                if ($restoredDateTime < $interruptionDateTime) {
                    $errors[] = "Entry " . ($i + 1) . ": Restored date cannot be before interruption date";
                    $validation_failures[] = "Entry " . ($i + 1) . ": restored date before interruption date";
                    continue;
                }
            }
        }
        
        $remark = isset($remarks[$i]) && !empty($remarks[$i]) ? sanitize(substr($remarks[$i], 0, 500)) : null;
        
        // Execute insert
        $result = $stmt->execute([
            $user_id, $region, $province, $city, $barangay,
            $telecom, $interruptionDateFormatted, $restoredDate, $remark
        ]);
        
        if ($result) {
            // Get the last inserted ID
            $last_id = $pdo->lastInsertId();
            $inserted_ids[] = $last_id;
            $inserted_count++;
            
            // SUCCESS SECURITY LOG - Individual record
            log_security_event(
                $user_id,
                'annex11_submission_success',
                "Successfully submitted Annex11 record #{$last_id} for {$barangay}: {$telecom} communication line"
            );
            
            // Debug log
            error_log("Annex11 - Entry " . ($i + 1) . " inserted with ID: $last_id");
        } else {
            $errors[] = "Entry " . ($i + 1) . ": Failed to insert";
            error_log("Annex 11 insert failed for entry " . ($i + 1) . " - User: $user_id");
        }
    }

    // Log validation failures if any
    if (!empty($validation_failures)) {
        log_security_event(
            $user_id,
            'validation_failures',
            'Annex 11 submission had validation failures: ' . implode('; ', $validation_failures)
        );
    }
    
    // Commit transaction if at least one entry was successful
    if ($inserted_count > 0) {
        $pdo->commit();
        
        // Format inserted IDs for logging
        $id_list = implode(', ', $inserted_ids);
        
        // AUDIT LOG: Successful submission
        log_audit_action(
            $user_id,
            'annex11_submission',
            "Successfully submitted communication lines report with $inserted_count entries (IDs: $id_list) for barangay: $user_barangay" . 
            (count($errors) > 0 ? " (" . count($errors) . " entries failed validation)" : "")
        );
        
        // SUCCESS SECURITY LOG - Batch summary
        log_security_event(
            $user_id,
            'annex11_submission_success',
            "Successfully submitted Annex11 records #{$id_list} for {$user_barangay}"
        );
        
        // Debug log
        error_log("Annex11 - Total inserted: $inserted_count, IDs: $id_list");
        
        if (count($errors) > 0) {
            set_flash("Report saved with $inserted_count entries. " . count($errors) . " entries had errors.", 'warning');
        } else {
            set_flash("Communication lines report saved successfully! $inserted_count entries recorded.", 'success');
        }
        
        // Redirect to records page after successful submission
        header('Location: ../public/annex11_records.php');
        exit;
    } else {
        $pdo->rollBack();
        
        // AUDIT LOG: Failed submission
        log_audit_action(
            $user_id,
            'annex11_submission_failed',
            "Failed to submit Annex 11 report - all entries had validation errors"
        );
        
        set_flash('Failed to save report. Please check your entries and try again.', 'error');
        header('Location: ../public/annex11.php');
        exit;
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // SECURITY LOG: Database error
    log_security_event(
        $user_id,
        'database_error',
        'Database error in Annex 11 submission: ' . $e->getMessage()
    );
    
    error_log("Annex 11 save error: " . $e->getMessage());
    set_flash('Database error occurred. Please try again later.', 'error');
    header('Location: ../public/annex11.php');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // SECURITY LOG: Unexpected error
    log_security_event(
        $user_id,
        'unexpected_error',
        'Unexpected error in Annex 11 submission: ' . $e->getMessage()
    );
    
    error_log("Annex 11 unexpected error: " . $e->getMessage());
    set_flash('An unexpected error occurred. Please try again later.', 'error');
    header('Location: ../public/annex11.php');
    exit;
}

// Final redirect fallback
header('Location: ../public/annex11.php');
exit;
?>