<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

require_login();

// RATE LIMITING: Check if user is not flooding with update requests
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex2_update', 20, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex2 update rate limit exceeded');
    echo json_encode(['success' => false, 'message' => 'Too many update attempts. Please try again later.']);
    exit;
}

// Verify CSRF token
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex2 update CSRF token mismatch');
    echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
    exit;
}

$record_id = filter_var($_POST['record_id'] ?? 0, FILTER_VALIDATE_INT);
$is_admin = ($_SESSION['user_role'] === 'admin');

if (!$record_id) {
    log_security_event($_SESSION['user_id'], 'invalid_input', 'Invalid record_id for Annex2 update');
    echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
    exit;
}

// Check if user owns the record
if (!$is_admin) {
    $check_stmt = db_query("SELECT * FROM annex2_affected_population WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
    $existing_record = $check_stmt->fetch();
    
    if (!$existing_record) {
        log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to update Annex2 record #{$record_id} without permission");
        echo json_encode(['success' => false, 'message' => 'Record not found or access denied']);
        exit;
    }
} else {
    $check_stmt = db_query("SELECT * FROM annex2_affected_population WHERE id = ?", [$record_id]);
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

// Validate number fields
$affected_families_cumulative = filter_var($_POST['affected_families_cumulative'] ?? 0, FILTER_VALIDATE_INT);
$affected_families_current = filter_var($_POST['affected_families_current'] ?? 0, FILTER_VALIDATE_INT);
$affected_persons_cumulative = filter_var($_POST['affected_persons_cumulative'] ?? 0, FILTER_VALIDATE_INT);
$affected_persons_current = filter_var($_POST['affected_persons_current'] ?? 0, FILTER_VALIDATE_INT);

$num_ecs_cumulative = filter_var($_POST['num_ecs_cumulative'] ?? 0, FILTER_VALIDATE_INT);
$num_ecs_current = filter_var($_POST['num_ecs_current'] ?? 0, FILTER_VALIDATE_INT);

$inside_families_cumulative = filter_var($_POST['inside_families_cumulative'] ?? 0, FILTER_VALIDATE_INT);
$inside_families_current = filter_var($_POST['inside_families_current'] ?? 0, FILTER_VALIDATE_INT);
$inside_persons_cumulative = filter_var($_POST['inside_persons_cumulative'] ?? 0, FILTER_VALIDATE_INT);
$inside_persons_current = filter_var($_POST['inside_persons_current'] ?? 0, FILTER_VALIDATE_INT);

$outside_families_cumulative = filter_var($_POST['outside_families_cumulative'] ?? 0, FILTER_VALIDATE_INT);
$outside_families_current = filter_var($_POST['outside_families_current'] ?? 0, FILTER_VALIDATE_INT);
$outside_persons_cumulative = filter_var($_POST['outside_persons_cumulative'] ?? 0, FILTER_VALIDATE_INT);
$outside_persons_current = filter_var($_POST['outside_persons_current'] ?? 0, FILTER_VALIDATE_INT);

$total_families_cumulative = filter_var($_POST['total_families_cumulative'] ?? 0, FILTER_VALIDATE_INT);
$total_families_current = filter_var($_POST['total_families_current'] ?? 0, FILTER_VALIDATE_INT);
$total_persons_cumulative = filter_var($_POST['total_persons_cumulative'] ?? 0, FILTER_VALIDATE_INT);
$total_persons_current = filter_var($_POST['total_persons_current'] ?? 0, FILTER_VALIDATE_INT);

$remarks = sanitize($_POST['remarks'] ?? '');

// Validation
$errors = [];

// Required fields
if (empty($region) || strlen($region) > 100) {
    $errors[] = 'Invalid region';
}
if (empty($province) || strlen($province) > 100) {
    $errors[] = 'Invalid province';
}
if (empty($city) || strlen($city) > 100) {
    $errors[] = 'Invalid city/municipality';
}
if (empty($barangay) || strlen($barangay) > 200) {
    $errors[] = 'Invalid barangay';
}

// Number validation
$number_fields = [
    'affected_families_cumulative' => $affected_families_cumulative,
    'affected_families_current' => $affected_families_current,
    'affected_persons_cumulative' => $affected_persons_cumulative,
    'affected_persons_current' => $affected_persons_current,
    'num_ecs_cumulative' => $num_ecs_cumulative,
    'num_ecs_current' => $num_ecs_current,
    'inside_families_cumulative' => $inside_families_cumulative,
    'inside_families_current' => $inside_families_current,
    'inside_persons_cumulative' => $inside_persons_cumulative,
    'inside_persons_current' => $inside_persons_current,
    'outside_families_cumulative' => $outside_families_cumulative,
    'outside_families_current' => $outside_families_current,
    'outside_persons_cumulative' => $outside_persons_cumulative,
    'outside_persons_current' => $outside_persons_current,
    'total_families_cumulative' => $total_families_cumulative,
    'total_families_current' => $total_families_current,
    'total_persons_cumulative' => $total_persons_cumulative,
    'total_persons_current' => $total_persons_current,
];

foreach ($number_fields as $field_name => $value) {
    if ($value === false || $value < 0 || $value > 999999999) {
        $errors[] = "Invalid value for {$field_name}";
    }
}

// Remarks length
if (strlen($remarks) > 500) {
    $errors[] = 'Remarks exceeds maximum length of 500 characters';
}

if (!empty($errors)) {
    log_security_event($_SESSION['user_id'], 'validation_failure', 'Annex2 update validation failed: ' . implode(', ', $errors));
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    // Update record
    $sql = "UPDATE annex2_affected_population SET 
            region = ?, 
            province = ?, 
            city = ?, 
            barangay = ?, 
            affected_families_cumulative = ?, 
            affected_families_current = ?, 
            affected_persons_cumulative = ?, 
            affected_persons_current = ?, 
            num_ecs_cumulative = ?, 
            num_ecs_current = ?, 
            inside_families_cumulative = ?, 
            inside_families_current = ?, 
            inside_persons_cumulative = ?, 
            inside_persons_current = ?, 
            outside_families_cumulative = ?, 
            outside_families_current = ?, 
            outside_persons_cumulative = ?, 
            outside_persons_current = ?, 
            total_families_cumulative = ?, 
            total_families_current = ?, 
            total_persons_cumulative = ?, 
            total_persons_current = ?, 
            remarks = ?,
            updated_at = NOW()
            WHERE id = ?";
    
    $stmt = db_query($sql, [
        $region,
        $province,
        $city,
        $barangay,
        $affected_families_cumulative,
        $affected_families_current,
        $affected_persons_cumulative,
        $affected_persons_current,
        $num_ecs_cumulative,
        $num_ecs_current,
        $inside_families_cumulative,
        $inside_families_current,
        $inside_persons_cumulative,
        $inside_persons_current,
        $outside_families_cumulative,
        $outside_families_current,
        $outside_persons_cumulative,
        $outside_persons_current,
        $total_families_cumulative,
        $total_families_current,
        $total_persons_cumulative,
        $total_persons_current,
        $remarks,
        $record_id
    ]);
    
    if ($stmt && $stmt->rowCount() > 0) {
        // AUDIT LOG: Record update
        log_audit_action(
            $_SESSION['user_id'],
            'annex2_update',
            "Updated record #{$record_id} for {$barangay}"
        );
        
        // SECURITY LOG: Success
        log_security_event(
            $_SESSION['user_id'],
            'annex2_update_success',
            "Successfully updated Annex2 record #{$record_id} for {$barangay}"
        );
        
        if ($stmt) {
            set_flash('Record updated successfully', 'success');
        } else {
            set_flash('Failed to update record', 'error');
        }

        header('Location: ../public/annex2_records.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Annex2 update error: " . $e->getMessage());
    log_security_event($_SESSION['user_id'], 'database_error', 'Annex2 update database error');
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>