<?php
/**
 * API Handler: Update Communication Line Record
 * Validates and updates existing record
 * FIXED: Absolute redirect path
 */

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_security_event($_SESSION['user_id'], 'invalid_request_method', 'Attempted non-POST request to annex11_update.php');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// CSRF token verification
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event(
        $_SESSION['user_id'],
        'csrf_validation_failed',
        'CSRF token validation failed for Annex 11 update'
    );
    echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
    exit;
}

// Rate limiting check
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex11_update', 20, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex11 update rate limit exceeded');
    echo json_encode(['success' => false, 'message' => 'Too many update attempts. Please try again later.']);
    exit;
}

$record_id = filter_var($_POST['record_id'] ?? 0, FILTER_VALIDATE_INT);
$is_admin = ($_SESSION['user_role'] === 'admin');

if (!$record_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
    exit;
}

// Check if user owns the record
if (!$is_admin) {
    $check_stmt = db_query("SELECT * FROM annex11_communication_lines WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
    $existing_record = $check_stmt->fetch();
    
    if (!$existing_record) {
        log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to update Annex11 record #{$record_id} without permission");
        echo json_encode(['success' => false, 'message' => 'Record not found or access denied']);
        exit;
    }
} else {
    $check_stmt = db_query("SELECT * FROM annex11_communication_lines WHERE id = ?", [$record_id]);
    $existing_record = $check_stmt->fetch();
}

if (!$existing_record) {
    echo json_encode(['success' => false, 'message' => 'Record not found']);
    exit;
}

// Sanitize and validate inputs
$region = sanitize($_POST['region'] ?? '');
$province = sanitize($_POST['province'] ?? '');
$city = sanitize($_POST['city'] ?? '');
$barangay = sanitize($_POST['barangay'] ?? '');
$telecom_provider = sanitize($_POST['telecom_provider'] ?? '');
$interruption_date = sanitize($_POST['interruption_date'] ?? '');
$restored_date = sanitize($_POST['restored_date'] ?? '');
$remarks = sanitize($_POST['remarks'] ?? '');

// Validation
$errors = [];

if (empty($region) || strlen($region) > 100) {
    $errors[] = 'Invalid region';
}
if (empty($province) || strlen($province) > 100) {
    $errors[] = 'Invalid province';
}
if (empty($city) || strlen($city) > 100) {
    $errors[] = 'Invalid city/municipality';
}
if (empty($barangay) || strlen($barangay) > 100) {
    $errors[] = 'Invalid barangay';
}

// Valid telecom providers
$valid_telecoms = ['PLDT', 'Globe', 'Smart', 'DITO', 'Other'];
if (!in_array($telecom_provider, $valid_telecoms)) {
    $errors[] = 'Invalid telecom provider';
}

// Validate datetime format for interruption date
$interruptionDateTime = DateTime::createFromFormat('Y-m-d\TH:i', $interruption_date);
if (!$interruptionDateTime) {
    $errors[] = 'Invalid interruption date format';
} else {
    $interruption_date_formatted = $interruptionDateTime->format('Y-m-d H:i:s');
}

// Validate restored date if provided
$restored_date_formatted = null;
if (!empty($restored_date)) {
    $restoredDateTime = DateTime::createFromFormat('Y-m-d\TH:i', $restored_date);
    if (!$restoredDateTime) {
        $errors[] = 'Invalid restored date format';
    } else {
        $restored_date_formatted = $restoredDateTime->format('Y-m-d H:i:s');
        
        // Validate that restored date is after interruption date
        if ($restoredDateTime < $interruptionDateTime) {
            $errors[] = 'Restored date cannot be before interruption date';
        }
    }
}

// Validate remarks length
if (strlen($remarks) > 500) {
    $errors[] = 'Remarks too long (max 500 characters)';
}

if (!empty($errors)) {
    log_security_event($_SESSION['user_id'], 'validation_failure', 'Annex11 update validation failed: ' . implode(', ', $errors));
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    // Update record
    $sql = "UPDATE annex11_communication_lines SET 
            region = ?, 
            province = ?, 
            city = ?, 
            barangay = ?, 
            telecom_provider = ?, 
            interruption_date = ?, 
            restored_date = ?, 
            remarks = ?,
            updated_at = NOW()
            WHERE id = ?";
    
    $stmt = db_query($sql, [
        $region,
        $province,
        $city,
        $barangay,
        $telecom_provider,
        $interruption_date_formatted,
        $restored_date_formatted,
        $remarks,
        $record_id
    ]);
    
    if ($stmt && $stmt->rowCount() > 0) {
        // Log the update to audit logs
        log_audit_action(
            $_SESSION['user_id'],
            'annex11_update',
            "Updated record #{$record_id} for {$barangay}: {$telecom_provider}"
        );
        
        // SUCCESS SECURITY LOG
        log_security_event(
            $_SESSION['user_id'],
            'annex11_update_success',
            "Successfully updated Annex11 record #{$record_id} for {$barangay}"
        );
        
        // Set flash message for success
        set_flash('Record updated successfully!', 'success');
        
        // ✅ FIXED: Use absolute path from root
header("Location: /mdr1/public/annex11_records.php");
exit();
   
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or update failed']);
    }
    
} catch (PDOException $e) {
    error_log("Annex11 update error: " . $e->getMessage());
    log_security_event($_SESSION['user_id'], 'database_error', 'Annex11 update database error');
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>