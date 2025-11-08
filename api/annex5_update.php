<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

require_login();

// Verify CSRF token
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex5 update CSRF token mismatch');
    echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
    exit;
}

// Rate limiting check - FIXED: Use actual user_id
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex5_update', 20, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex5 update rate limit exceeded');
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
    $check_stmt = db_query("SELECT * FROM annex5_agriculture_damage WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
    $existing_record = $check_stmt->fetch();
    
    if (!$existing_record) {
        log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to update Annex5 record #{$record_id} without permission");
        echo json_encode(['success' => false, 'message' => 'Record not found or access denied']);
        exit;
    }
} else {
    $check_stmt = db_query("SELECT * FROM annex5_agriculture_damage WHERE id = ?", [$record_id]);
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
$affected_people = filter_var($_POST['affected_people'] ?? 0, FILTER_VALIDATE_INT);
$damage_value = filter_var($_POST['damage_value'] ?? 0, FILTER_VALIDATE_FLOAT);

// Classification-specific fields
$no_recovery_area = filter_var($_POST['no_recovery_area'] ?? 0, FILTER_VALIDATE_FLOAT) ?: null;
$with_recovery_area = filter_var($_POST['with_recovery_area'] ?? 0, FILTER_VALIDATE_FLOAT) ?: null;
$total_crop_area = filter_var($_POST['total_crop_area'] ?? 0, FILTER_VALIDATE_FLOAT) ?: null;
$production_loss_volume = filter_var($_POST['production_loss_volume'] ?? 0, FILTER_VALIDATE_FLOAT) ?: null;
$animal_heads = filter_var($_POST['animal_heads'] ?? 0, FILTER_VALIDATE_INT) ?: null;
$fisheries_details = sanitize($_POST['fisheries_details'] ?? '');
$totally_damaged = filter_var($_POST['totally_damaged'] ?? 0, FILTER_VALIDATE_INT) ?: null;
$partially_damaged = filter_var($_POST['partially_damaged'] ?? 0, FILTER_VALIDATE_INT) ?: null;
$total_damaged = filter_var($_POST['total_damaged'] ?? 0, FILTER_VALIDATE_INT) ?: null;

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
if ($affected_people === false || $affected_people < 0 || $affected_people > 1000000) {
    $errors[] = 'Invalid number of affected people';
}
if ($damage_value === false || $damage_value < 0) {
    $errors[] = 'Invalid damage value';
}

if (!empty($errors)) {
    log_security_event($_SESSION['user_id'], 'validation_failure', 'Annex5 update validation failed: ' . implode(', ', $errors));
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    // Update record
    $sql = "UPDATE annex5_agriculture_damage SET 
            region = ?, 
            province = ?, 
            city = ?, 
            barangay = ?, 
            classification = ?, 
            type = ?, 
            affected_people = ?, 
            damage_value = ?, 
            no_recovery_area = ?, 
            with_recovery_area = ?, 
            total_crop_area = ?, 
            production_loss_volume = ?, 
            animal_heads = ?, 
            fisheries_details = ?, 
            totally_damaged = ?, 
            partially_damaged = ?, 
            total_damaged = ?,
            updated_at = NOW()
            WHERE id = ?";
    
    $stmt = db_query($sql, [
        $region,
        $province,
        $city,
        $barangay,
        $classification,
        $type,
        $affected_people,
        $damage_value,
        $no_recovery_area,
        $with_recovery_area,
        $total_crop_area,
        $production_loss_volume,
        $animal_heads,
        $fisheries_details,
        $totally_damaged,
        $partially_damaged,
        $total_damaged,
        $record_id
    ]);
    
    if ($stmt && $stmt->rowCount() > 0) {
        // Log the update to audit logs
        log_audit_action(
            $_SESSION['user_id'],
            'annex5_update',
            "Updated record #{$record_id} for {$barangay}: {$classification} - {$type}"
        );
        
        // SUCCESS SECURITY LOG
        log_security_event(
            $_SESSION['user_id'],
            'annex5_update_success',
            "Successfully updated Annex5 record #{$record_id} for {$barangay}"
        );
        
        // Set flash message for success
        set_flash('Record updated successfully!', 'success');
        
  header("Location: /mdri/public/annex11_records.php");
exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or update failed']);
    }
    
} catch (PDOException $e) {
    error_log("Annex5 update error: " . $e->getMessage());
    log_security_event($_SESSION['user_id'], 'database_error', 'Annex5 update database error');
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>