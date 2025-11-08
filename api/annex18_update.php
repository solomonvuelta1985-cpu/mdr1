<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

require_login();

// Verify CSRF token
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex18 update CSRF token mismatch');
    echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
    exit;
}

// Rate limiting check
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex18_update', 20, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex18 update rate limit exceeded');
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
    $check_stmt = db_query("SELECT * FROM annex18_animal_evacuation WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
    $existing_record = $check_stmt->fetch();
    
    if (!$existing_record) {
        log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to update Annex18 record #{$record_id} without permission");
        echo json_encode(['success' => false, 'message' => 'Record not found or access denied']);
        exit;
    }
} else {
    $check_stmt = db_query("SELECT * FROM annex18_animal_evacuation WHERE id = ?", [$record_id]);
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
$classification = sanitize($_POST['classification'] ?? '');
$type = sanitize($_POST['type'] ?? '');
$quantity = filter_var($_POST['quantity'] ?? 0, FILTER_VALIDATE_INT);
$evacuation_reason = sanitize($_POST['evacuation_reason'] ?? '');
$shelter_location = sanitize($_POST['shelter_location'] ?? '');
$assistance_provider = sanitize($_POST['assistance_provider'] ?? '');
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
if (empty($classification)) {
    $errors[] = 'Classification is required';
}
if (empty($type)) {
    $errors[] = 'Type is required';
}
if ($quantity === false || $quantity < 0 || $quantity > 999999) {
    $errors[] = 'Invalid quantity';
}
if (empty($evacuation_reason)) {
    $errors[] = 'Evacuation reason is required';
}

if (!empty($errors)) {
    log_security_event($_SESSION['user_id'], 'validation_failure', 'Annex18 update validation failed: ' . implode(', ', $errors));
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    // Update record
    $sql = "UPDATE annex18_animal_evacuation SET 
            region = ?, 
            province = ?, 
            city = ?, 
            barangay = ?, 
            classification = ?, 
            type = ?, 
            quantity = ?, 
            evacuation_reason = ?, 
            shelter_location = ?, 
            assistance_provider = ?, 
            remarks = ?,
            updated_at = NOW()
            WHERE id = ?";
    
    $stmt = db_query($sql, [
        $region,
        $province,
        $city,
        $barangay,
        $classification,
        $type,
        $quantity,
        $evacuation_reason,
        $shelter_location,
        $assistance_provider,
        $remarks,
        $record_id
    ]);
    
    if ($stmt && $stmt->rowCount() > 0) {
        // Log the update to audit logs
        log_audit_action(
            $_SESSION['user_id'],
            'annex18_update',
            "Updated record #{$record_id} for {$barangay}: {$classification} - {$type}"
        );
        
        // SUCCESS SECURITY LOG
        log_security_event(
            $_SESSION['user_id'],
            'annex18_update_success',
            "Successfully updated Annex18 record #{$record_id} for {$barangay}"
        );
        
        // Set flash message for success
        set_flash('Record updated successfully!', 'success');
        
        echo json_encode(['success' => true, 'redirect' => '../public/annex18_records.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or update failed']);
    }
    
} catch (PDOException $e) {
    error_log("Annex18 update error: " . $e->getMessage());
    log_security_event($_SESSION['user_id'], 'database_error', 'Annex18 update database error');
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>