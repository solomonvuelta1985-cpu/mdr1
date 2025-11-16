<?php
// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();
// Annex Access Control - Only Admin and Sheila can add/edit
require_once __DIR__ . '/../includes/check_annex_access.php';
if (!can_access_annex('14')) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to modify Annex 14 records.']);
    exit;
}


// Validate session fingerprint
if (!validate_session_fingerprint()) {
    log_security_event($_SESSION['user_id'] ?? 'unknown', 'session_hijack_attempt', 'Annex 14 update session validation failed');
    json_response(['success' => false, 'message' => 'Security violation detected'], 403);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_security_event($_SESSION['user_id'], 'invalid_method', 'Annex 14 update with non-POST method');
    set_flash('Invalid request method', 'error');
    header('Location: ../public/annex14.php');
    exit;
}

// Check concurrent requests
if (!check_concurrent_requests($_SESSION['user_id'], 'annex14_update', 2)) {
    log_security_event($_SESSION['user_id'], 'concurrent_limit', 'Too many Annex 14 update requests');
    set_flash('Too many simultaneous requests. Please wait a moment.', 'error');
    header('Location: ../public/annex14_records.php');
    exit;
}

// Verify CSRF token with operation tracking
if (!validate_csrf_with_operation($_POST['csrf_token'] ?? '', 'annex14_update')) {
    log_security_event($_SESSION['user_id'], 'csrf_validation_failed', 'Annex 14 update CSRF failure');
    set_flash('Security token validation failed', 'error');
    header('Location: ../public/annex14.php');
    exit;
}

// Rate limiting for updates
if (!check_rate_limit($_SESSION['user_id'], 'annex14_update', 10, 300)) {
    log_security_event($_SESSION['user_id'], 'rate_limit_exceeded', 'Annex 14 update rate limit exceeded');
    set_flash('Too many update attempts. Please wait a few minutes.', 'error');
    header('Location: ../public/annex14_records.php');
    exit;
}

// Check if record ID is provided
if (!isset($_POST['record_id'])) {
    log_security_event($_SESSION['user_id'], 'missing_record_id', 'Annex 14 update without record ID');
    set_flash('Record ID is required', 'error');
    header('Location: ../public/annex14.php');
    exit;
}

$record_id = sanitize($_POST['record_id']);
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_role'] === 'admin');

// Check if user owns the record (unless admin)
if (!$is_admin) {
    $check_stmt = db_query("SELECT id, barangay FROM annex14_suspension_work WHERE id = ? AND created_by = ?", [$record_id, $user_id]);
    $existing_record = $check_stmt->fetch();
    if (!$existing_record) {
        log_security_event($user_id, 'unauthorized_record_access', 
            "Attempted to update Annex 14 record #$record_id without permission");
        set_flash('Record not found or access denied', 'error');
        header('Location: ../public/annex14_records.php');
        exit;
    }
} else {
    // For admin, just verify record exists
    $check_stmt = db_query("SELECT id, barangay FROM annex14_suspension_work WHERE id = ?", [$record_id]);
    $existing_record = $check_stmt->fetch();
    if (!$existing_record) {
        log_security_event($user_id, 'record_not_found', "Admin attempted to update non-existent Annex 14 record #$record_id");
        set_flash('Record not found', 'error');
        header('Location: ../public/annex14_records.php');
        exit;
    }
}

// Sanitize input data
$type = sanitize($_POST['type'] ?? '');
$suspension_date = sanitize($_POST['suspension_date'] ?? '');
$resumption_date = !empty($_POST['resumption_date']) ? sanitize($_POST['resumption_date']) : null;
$remarks = sanitize($_POST['remarks'] ?? '');

// Validate required fields
if (empty($type) || empty($suspension_date)) {
    log_security_event($user_id, 'validation_failed', 'Annex 14 update missing required fields');
    set_flash('Type and Suspension Date are required', 'error');
    header('Location: ../public/annex14_records.php');
    exit;
}

// Validate type
$valid_types = ['Government', 'Private', 'All'];
if (!in_array($type, $valid_types)) {
    log_security_event($user_id, 'invalid_type', "Annex 14 update with invalid type: $type");
    set_flash('Invalid suspension type', 'error');
    header('Location: ../public/annex14_records.php');
    exit;
}

// Validate dates
if (!strtotime($suspension_date)) {
    log_security_event($user_id, 'invalid_date', "Annex 14 update with invalid suspension date: $suspension_date");
    set_flash('Invalid suspension date format', 'error');
    header('Location: ../public/annex14_records.php');
    exit;
}

if (!empty($resumption_date) && !strtotime($resumption_date)) {
    log_security_event($user_id, 'invalid_date', "Annex 14 update with invalid resumption date: $resumption_date");
    set_flash('Invalid resumption date format', 'error');
    header('Location: ../public/annex14_records.php');
    exit;
}

// Validate date logic
if (!empty($resumption_date) && strtotime($resumption_date) <= strtotime($suspension_date)) {
    log_security_event($user_id, 'invalid_date_logic', "Annex 14 update with resumption before suspension");
    set_flash('Resumption date must be after suspension date', 'error');
    header('Location: ../public/annex14_records.php');
    exit;
}

// Log the update attempt
log_audit_action($user_id, 'annex14_update_attempt', 
    "Attempting to update record #$record_id (Barangay: " . ($existing_record['barangay'] ?? 'Unknown') . ")");

// Update the record
$stmt = db_query("
    UPDATE annex14_suspension_work 
    SET type = ?, suspension_date = ?, resumption_date = ?, remarks = ?, updated_at = CURRENT_TIMESTAMP 
    WHERE id = ?
", [$type, $suspension_date, $resumption_date, $remarks, $record_id]);

if ($stmt) {
    // Log successful update
    log_audit_action($user_id, 'annex14_update_success', 
        "Successfully updated record #$record_id (Type: $type, Barangay: " . ($existing_record['barangay'] ?? 'Unknown') . ")");
    set_flash('Record updated successfully', 'success');
} else {
    // Log update failure
    log_security_event($user_id, 'update_failed', "Failed to update Annex 14 record #$record_id");
    set_flash('Failed to update record', 'error');
}

// Release concurrent request
release_concurrent_request($_SESSION['user_id'], 'annex14_update');

header('Location: ../public/annex14_records.php');
exit;
?>