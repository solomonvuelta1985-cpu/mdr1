<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

require_login();

// Verify CSRF token
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex3 update CSRF token mismatch');
    echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
    exit;
}

// Rate limiting check
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex3_update', 20, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex3 update rate limit exceeded');
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
    $check_stmt = db_query("SELECT * FROM annex3_casualties WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
    $existing_record = $check_stmt->fetch();
    
    if (!$existing_record) {
        log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to update Annex3 record #{$record_id} without permission");
        echo json_encode(['success' => false, 'message' => 'Record not found or access denied']);
        exit;
    }
} else {
    $check_stmt = db_query("SELECT * FROM annex3_casualties WHERE id = ?", [$record_id]);
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
$category = sanitize($_POST['category'] ?? '');
$surname = sanitize($_POST['surname'] ?? '');
$first_name = sanitize($_POST['first_name'] ?? '');
$middle_name = sanitize($_POST['middle_name'] ?? '');
$age = filter_var($_POST['age'] ?? 0, FILTER_VALIDATE_INT);
$sex = sanitize($_POST['sex'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$cause = sanitize($_POST['cause'] ?? '');
$remarks = sanitize($_POST['remarks'] ?? '');
$source = sanitize($_POST['source'] ?? '');
$validated = sanitize($_POST['validated'] ?? '');

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
if (empty($category)) {
    $errors[] = 'Category is required';
}
if (empty($surname) || strlen($surname) > 100) {
    $errors[] = 'Invalid surname';
}
if (empty($first_name) || strlen($first_name) > 100) {
    $errors[] = 'Invalid first name';
}
if (strlen($middle_name) > 100) {
    $errors[] = 'Middle name too long';
}
if ($age === false || $age < 0 || $age > 120) {
    $errors[] = 'Invalid age';
}
if (empty($sex)) {
    $errors[] = 'Sex is required';
}
if (empty($address) || strlen($address) > 255) {
    $errors[] = 'Invalid address';
}
if (empty($cause) || strlen($cause) > 500) {
    $errors[] = 'Invalid cause';
}
if (strlen($remarks) > 500) {
    $errors[] = 'Remarks too long';
}
if (empty($source)) {
    $errors[] = 'Source is required';
}
if (empty($validated)) {
    $errors[] = 'Validation status is required';
}

if (!empty($errors)) {
    log_security_event($_SESSION['user_id'], 'validation_failure', 'Annex3 update validation failed: ' . implode(', ', $errors));
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    // Update record
    $sql = "UPDATE annex3_casualties SET 
            region = ?, 
            province = ?, 
            city = ?, 
            barangay = ?, 
            category = ?, 
            surname = ?, 
            first_name = ?, 
            middle_name = ?, 
            age = ?, 
            sex = ?, 
            address = ?, 
            cause = ?, 
            remarks = ?, 
            source = ?, 
            validated = ?,
            updated_at = NOW(),
            updated_by = ?
            WHERE id = ?";
    
    $stmt = db_query($sql, [
        $region,
        $province,
        $city,
        $barangay,
        $category,
        $surname,
        $first_name,
        $middle_name,
        $age,
        $sex,
        $address,
        $cause,
        $remarks,
        $source,
        $validated,
        $_SESSION['user_id'],
        $record_id
    ]);
    
    if ($stmt && $stmt->rowCount() > 0) {
        // Log the update to audit logs
        log_audit_action(
            $_SESSION['user_id'],
            'annex3_update',
            "Updated record #{$record_id} for {$barangay}: {$surname}, {$first_name}"
        );
        
        // SUCCESS SECURITY LOG
        log_security_event(
            $_SESSION['user_id'],
            'annex3_update_success',
            "Successfully updated Annex3 record #{$record_id}"
        );
        
        echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or update failed']);
    }
    
} catch (PDOException $e) {
    error_log("Annex3 update error: " . $e->getMessage());
    log_security_event($_SESSION['user_id'], 'database_error', 'Annex3 update database error');
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>