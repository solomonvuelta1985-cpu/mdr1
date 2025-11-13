<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

require_login();
// Annex Access Control - Only Admin and Sheila can add/edit
require_once __DIR__ . '/../includes/check_annex_access.php';
if (!can_access_annex('19')) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to modify Annex 19 records.']);
    exit;
}


// Verify CSRF token
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex19 update CSRF token mismatch');
    echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
    exit;
}

// Rate limiting check
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex19_update', 20, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex19 update rate limit exceeded');
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
    $check_stmt = db_query("SELECT * FROM annex19_families_assisted WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
    $existing_record = $check_stmt->fetch();
    
    if (!$existing_record) {
        log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to update Annex19 record #{$record_id} without permission");
        echo json_encode(['success' => false, 'message' => 'Record not found or access denied']);
        exit;
    }
} else {
    $check_stmt = db_query("SELECT * FROM annex19_families_assisted WHERE id = ?", [$record_id]);
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
$families_requiring_assistance = filter_var($_POST['families_requiring_assistance'] ?? 0, FILTER_VALIDATE_INT);
$families_assisted = filter_var($_POST['families_assisted'] ?? 0, FILTER_VALIDATE_INT);
$assistance_type = sanitize($_POST['assistance_type'] ?? '');
$assistance_provider = sanitize($_POST['assistance_provider'] ?? '');
$remarks = sanitize($_POST['remarks'] ?? '');

// Calculate percentage
$percentage_assisted = 0;
if ($families_requiring_assistance > 0) {
    $percentage_assisted = ($families_assisted / $families_requiring_assistance) * 100;
}

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
if ($families_requiring_assistance === false || $families_requiring_assistance < 0 || $families_requiring_assistance > 100000) {
    $errors[] = 'Invalid number of families requiring assistance';
}
if ($families_assisted === false || $families_assisted < 0 || $families_assisted > 100000) {
    $errors[] = 'Invalid number of families assisted';
}
if (empty($assistance_type)) {
    $errors[] = 'Assistance type is required';
}
if (empty($assistance_provider)) {
    $errors[] = 'Assistance provider is required';
}

if (!empty($errors)) {
    log_security_event($_SESSION['user_id'], 'validation_failure', 'Annex19 update validation failed: ' . implode(', ', $errors));
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    // Update record
    $sql = "UPDATE annex19_families_assisted SET 
            region = ?, 
            province = ?, 
            city = ?, 
            barangay = ?, 
            families_requiring_assistance = ?, 
            families_assisted = ?, 
            percentage_assisted = ?, 
            assistance_type = ?, 
            assistance_provider = ?, 
            remarks = ?,
            updated_at = NOW()
            WHERE id = ?";
    
    $stmt = db_query($sql, [
        $region,
        $province,
        $city,
        $barangay,
        $families_requiring_assistance,
        $families_assisted,
        $percentage_assisted,
        $assistance_type,
        $assistance_provider,
        $remarks,
        $record_id
    ]);
    
    if ($stmt && $stmt->rowCount() > 0) {
        // Log the update to audit logs
        log_audit_action(
            $_SESSION['user_id'],
            'annex19_update',
            "Updated record #{$record_id} for {$barangay}: {$assistance_type} by {$assistance_provider}"
        );
        
        // SUCCESS SECURITY LOG
        log_security_event(
            $_SESSION['user_id'],
            'annex19_update_success',
            "Successfully updated Annex19 record #{$record_id} for {$barangay}"
        );
        
        echo json_encode(['success' => true, 'message' => 'Record updated successfully', 'redirect' => '../public/annex19_records.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or update failed']);
    }
    
} catch (PDOException $e) {
    error_log("Annex19 update error: " . $e->getMessage());
    log_security_event($_SESSION['user_id'], 'database_error', 'Annex19 update database error');
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>