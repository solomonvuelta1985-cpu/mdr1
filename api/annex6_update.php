<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

require_login();

// Verify CSRF token
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex6 update CSRF token mismatch');
    echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
    exit;
}

// Rate limiting check
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex6_update', 20, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex6 update rate limit exceeded');
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
    $check_stmt = db_query("SELECT * FROM annex6_infrastructure_damage WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
    $existing_record = $check_stmt->fetch();

    if (!$existing_record) {
        log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to update Annex6 record #{$record_id} without permission");
        echo json_encode(['success' => false, 'message' => 'Record not found or access denied']);
        exit;
    }
} else {
    $check_stmt = db_query("SELECT * FROM annex6_infrastructure_damage WHERE id = ?", [$record_id]);
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
$type = sanitize($_POST['type'] ?? '');
$classification = sanitize($_POST['classification'] ?? '');
$name = sanitize($_POST['name'] ?? '');
$totally_damaged = filter_var($_POST['totally_damaged'] ?? 0, FILTER_VALIDATE_INT);
$partially_damaged = filter_var($_POST['partially_damaged'] ?? 0, FILTER_VALIDATE_INT);
$total_damaged = filter_var($_POST['total_damaged'] ?? 0, FILTER_VALIDATE_INT);
$unit = sanitize($_POST['unit'] ?? '');
$quantity = filter_var($_POST['quantity'] ?? 0, FILTER_VALIDATE_FLOAT);
$cost = sanitize($_POST['cost'] ?? '');
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
if (empty($type)) {
    $errors[] = 'Infrastructure type is required';
}

// Validate type enum
$valid_types = ['Road', 'Bridge', 'Flood Control', 'Government Facilities',
               'Health Facilities', 'Schools', 'Cultural Heritage',
               'Utility Service Facilities', 'Private'];
if (!in_array($type, $valid_types)) {
    $errors[] = 'Invalid infrastructure type';
}

// Validate classification enum
if (!empty($classification)) {
    $valid_classifications = ['National', 'Local'];
    if (!in_array($classification, $valid_classifications)) {
        $errors[] = 'Invalid classification';
    }
}

if ($totally_damaged === false || $totally_damaged < 0) {
    $errors[] = 'Invalid totally damaged value';
}
if ($partially_damaged === false || $partially_damaged < 0) {
    $errors[] = 'Invalid partially damaged value';
}
if ($quantity === false || $quantity < 0) {
    $errors[] = 'Invalid quantity';
}

if (strlen($name) > 255) {
    $errors[] = 'Infrastructure name too long';
}
if (strlen($unit) > 50) {
    $errors[] = 'Unit too long';
}
if (strlen($cost) > 100) {
    $errors[] = 'Cost too long';
}
if (strlen($remarks) > 500) {
    $errors[] = 'Remarks too long';
}

if (!empty($errors)) {
    log_security_event($_SESSION['user_id'], 'validation_failure', 'Annex6 update validation failed: ' . implode(', ', $errors));
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    // Update record
    $sql = "UPDATE annex6_infrastructure_damage SET
            region = ?,
            province = ?,
            city = ?,
            barangay = ?,
            type = ?,
            classification = ?,
            name = ?,
            totally_damaged = ?,
            partially_damaged = ?,
            total_damaged = ?,
            unit = ?,
            quantity = ?,
            cost = ?,
            remarks = ?,
            updated_at = NOW()
            WHERE id = ?";

    $stmt = db_query($sql, [
        $region,
        $province,
        $city,
        $barangay,
        $type,
        $classification,
        $name,
        $totally_damaged,
        $partially_damaged,
        $total_damaged,
        $unit,
        $quantity,
        $cost,
        $remarks,
        $record_id
    ]);

    if ($stmt && $stmt->rowCount() > 0) {
        // Log the update to audit logs
        log_audit_action(
            $_SESSION['user_id'],
            'annex6_update',
            "Updated record #{$record_id} for {$barangay}: {$type} - {$name}"
        );

        // SUCCESS SECURITY LOG
        log_security_event(
            $_SESSION['user_id'],
            'annex6_update_success',
            "Successfully updated Annex6 record #{$record_id} for {$barangay}"
        );

        // Set flash message for success
        set_flash('Record updated successfully!', 'success');

        echo json_encode([
            'success' => true,
            'message' => 'Record updated successfully',
            'redirect' => '../public/annex6_records.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or update failed']);
    }

} catch (PDOException $e) {
    error_log("Annex6 update error: " . $e->getMessage());
    log_security_event($_SESSION['user_id'], 'database_error', 'Annex6 update database error');
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
