<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

require_login();
// Annex Access Control - Only Admin and Sheila can add/edit
require_once __DIR__ . '/../includes/check_annex_access.php';
if (!can_access_annex('15')) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to modify Annex 15 records.']);
    exit;
}


// Verify CSRF token
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex15 update CSRF token mismatch');
    echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
    exit;
}

// Rate limiting check
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex15_update', 20, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex15 update rate limit exceeded');
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
    $check_stmt = db_query("SELECT * FROM annex15_suspension_classes WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
    $existing_record = $check_stmt->fetch();
    
    if (!$existing_record) {
        log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to update Annex15 record #{$record_id} without permission");
        echo json_encode(['success' => false, 'message' => 'Record not found or access denied']);
        exit;
    }
} else {
    $check_stmt = db_query("SELECT * FROM annex15_suspension_classes WHERE id = ?", [$record_id]);
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
$level = sanitize($_POST['level'] ?? '');
$type = sanitize($_POST['type'] ?? '');
$suspension_date = sanitize($_POST['suspension_date'] ?? '');
$resumption_date = !empty($_POST['resumption_date']) ? sanitize($_POST['resumption_date']) : null;
$suspension_reason = sanitize($_POST['suspension_reason'] ?? '');
$issuing_authority = sanitize($_POST['issuing_authority'] ?? '');
$suspension_scope = sanitize($_POST['suspension_scope'] ?? '');
$alternative_delivery = sanitize($_POST['alternative_delivery'] ?? '');
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
if (empty($level)) {
    $errors[] = 'Level is required';
}
if (empty($type)) {
    $errors[] = 'Type is required';
}
if (empty($suspension_date)) {
    $errors[] = 'Suspension date is required';
}
if (empty($suspension_reason)) {
    $errors[] = 'Suspension reason is required';
}
if (empty($issuing_authority)) {
    $errors[] = 'Issuing authority is required';
}
if (empty($suspension_scope)) {
    $errors[] = 'Suspension scope is required';
}
if (empty($alternative_delivery)) {
    $errors[] = 'Alternative delivery is required';
}

if (!empty($errors)) {
    log_security_event($_SESSION['user_id'], 'validation_failure', 'Annex15 update validation failed: ' . implode(', ', $errors));
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    // Update record
    $sql = "UPDATE annex15_suspension_classes SET 
            region = ?, 
            province = ?, 
            city = ?, 
            barangay = ?, 
            level = ?, 
            type = ?, 
            suspension_date = ?, 
            resumption_date = ?, 
            suspension_reason = ?, 
            issuing_authority = ?, 
            suspension_scope = ?, 
            alternative_delivery = ?, 
            remarks = ?,
            updated_at = NOW()
            WHERE id = ?";
    
    $stmt = db_query($sql, [
        $region,
        $province,
        $city,
        $barangay,
        $level,
        $type,
        $suspension_date,
        $resumption_date,
        $suspension_reason,
        $issuing_authority,
        $suspension_scope,
        $alternative_delivery,
        $remarks,
        $record_id
    ]);
    
    if ($stmt && $stmt->rowCount() > 0) {
        // Log the update to audit logs
        log_audit_action(
            $_SESSION['user_id'],
            'annex15_update',
            "Updated record #{$record_id} for {$barangay}: {$level} - {$type}"
        );
        
        // SUCCESS SECURITY LOG
        log_security_event(
            $_SESSION['user_id'],
            'annex15_update_success',
            "Successfully updated Annex15 record #{$record_id} for {$barangay}"
        );
        
        // Set flash message for success
        set_flash('Record updated successfully!', 'success');
        
        echo json_encode(['success' => true, 'redirect' => '../public/annex15_records.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or update failed']);
    }
    
} catch (PDOException $e) {
    error_log("Annex15 update error: " . $e->getMessage());
    log_security_event($_SESSION['user_id'], 'database_error', 'Annex15 update database error');
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>