<?php
/**
 * API Handler: Update Annex 20 Assistance Record
 * Secure update with validation and audit logging
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

require_login();
// Annex Access Control - Only Admin and Sheila can add/edit
require_once __DIR__ . '/../includes/check_annex_access.php';
if (!can_access_annex('20')) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to modify Annex 20 records.']);
    exit;
}


// Verify CSRF token
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex20 update CSRF token mismatch');
    echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
    exit;
}

// Rate limiting check
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex20_update', 20, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex20 update rate limit exceeded');
    echo json_encode(['success' => false, 'message' => 'Too many update attempts. Please try again later.']);
    exit;
}

$record_id = filter_var($_POST['record_id'] ?? 0, FILTER_VALIDATE_INT);
$is_admin = ($_SESSION['user_role'] === 'admin');

// Debug logging - see exactly what was received
error_log("=== ANNEX20 UPDATE DEBUG ===");
error_log("Record ID: " . $record_id);
error_log("Raw POST quantity: " . ($_POST['quantity'] ?? 'NOT SET'));
error_log("Raw POST cost_per_unit: " . ($_POST['cost_per_unit'] ?? 'NOT SET'));
error_log("Raw POST amount: " . ($_POST['amount'] ?? 'NOT SET'));

if (!$record_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
    exit;
}

// Check if user owns the record
if (!$is_admin) {
    $check_stmt = db_query("SELECT * FROM annex20_assistance WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
    $existing_record = $check_stmt->fetch();

    if (!$existing_record) {
        log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to update Annex20 record #{$record_id} without permission");
        echo json_encode(['success' => false, 'message' => 'Record not found or access denied']);
        exit;
    }
} else {
    $check_stmt = db_query("SELECT * FROM annex20_assistance WHERE id = ?", [$record_id]);
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
$cluster = sanitize($_POST['cluster'] ?? '');
$type = sanitize($_POST['type'] ?? '');
$quantity = filter_var($_POST['quantity'] ?? 0, FILTER_VALIDATE_INT);
$unit = sanitize($_POST['unit'] ?? '');
$cost_per_unit = filter_var($_POST['cost_per_unit'] ?? 0, FILTER_VALIDATE_FLOAT);
$amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
$remarks = sanitize($_POST['remarks'] ?? '');

// Debug logging - see parsed values
error_log("Parsed quantity: " . $quantity);
error_log("Parsed cost_per_unit: " . $cost_per_unit);
error_log("Parsed amount: " . $amount);

// Valid clusters and types
$valid_clusters = [
    'Food', 'WASH', 'Shelter', 'Health', 'Protection',
    'Education', 'Nutrition', 'Camp Management',
    'Emergency Telecommunications', 'Logistics'
];

$valid_types = [
    'Food packs', 'Hygiene kits', 'Water containers',
    'Temporary shelter materials', 'Medicines', 'Blankets',
    'Sleeping mats', 'Cooking utensils', 'Mosquito nets',
    'Water purification tablets', 'Other'
];

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
if (!in_array($cluster, $valid_clusters)) {
    $errors[] = 'Invalid cluster';
}
if (!in_array($type, $valid_types)) {
    $errors[] = 'Invalid assistance type';
}
if ($quantity === false || $quantity < 0 || $quantity > 1000000) {
    $errors[] = 'Invalid quantity';
}
if (empty($unit) || strlen($unit) > 50) {
    $errors[] = 'Invalid unit';
}
if ($cost_per_unit === false || $cost_per_unit < 0 || $cost_per_unit > 999999999.99) {
    $errors[] = 'Invalid cost per unit';
}
if ($amount === false || $amount < 0 || $amount > 999999999999.99) {
    $errors[] = 'Invalid amount';
}

// Validate calculated amount matches
$calculated_amount = $quantity * $cost_per_unit;
$difference = abs($calculated_amount - $amount);
if ($difference > 0.02) {
    error_log("Amount mismatch - Qty: $quantity, Cost: $cost_per_unit, Calculated: $calculated_amount, Submitted: $amount, Diff: $difference");
    $errors[] = "Amount calculation mismatch (expected: " . number_format($calculated_amount, 2) . ", got: " . number_format($amount, 2) . ")";
}

if (strlen($remarks) > 500) {
    $errors[] = 'Remarks too long (max 500 characters)';
}

if (!empty($errors)) {
    log_security_event($_SESSION['user_id'], 'validation_failure', 'Annex20 update validation failed: ' . implode(', ', $errors));
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    // Update record
    $sql = "UPDATE annex20_assistance SET
            region = ?,
            province = ?,
            city = ?,
            barangay = ?,
            cluster = ?,
            type = ?,
            quantity = ?,
            unit = ?,
            cost_per_unit = ?,
            amount = ?,
            remarks = ?,
            updated_at = NOW()
            WHERE id = ?";

    $stmt = db_query($sql, [
        $region,
        $province,
        $city,
        $barangay,
        $cluster,
        $type,
        $quantity,
        $unit,
        $cost_per_unit,
        $amount,
        $remarks,
        $record_id
    ]);

    if ($stmt && $stmt->rowCount() > 0) {
        // Log the update to audit logs
        log_audit_action(
            $_SESSION['user_id'],
            'annex20_update',
            "Updated assistance record #{$record_id} for {$barangay}: {$cluster} - {$type}"
        );

        // SUCCESS SECURITY LOG
        log_security_event(
            $_SESSION['user_id'],
            'annex20_update_success',
            "Successfully updated Annex20 record #{$record_id} for {$barangay}"
        );

        // Set flash message for success
        set_flash('Record updated successfully!', 'success');

        // Return success with redirect URL
        echo json_encode([
            'success' => true,
            'message' => 'Record updated successfully',
            'redirect' => '../public/annex20_records.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or update failed']);
    }

} catch (PDOException $e) {
    error_log("Annex20 update error: " . $e->getMessage());
    log_security_event($_SESSION['user_id'], 'database_error', 'Annex20 update database error');
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
