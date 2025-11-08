<?php
// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('Invalid request method', 'error');
    header('Location: ../public/annex4.php');
    exit;
}

// Verify CSRF token
if (!verify_token($_POST['csrf_token'] ?? '')) {
    set_flash('Security token validation failed', 'error');
    header('Location: ../public/annex4.php');
    exit;
}

// Check if record ID is provided
if (!isset($_POST['record_id'])) {
    set_flash('Record ID is required', 'error');
    header('Location: ../public/annex4.php');
    exit;
}

$record_id = sanitize($_POST['record_id']);
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_role'] === 'admin');

// Check if user owns the record (unless admin)
if (!$is_admin) {
    $check_stmt = db_query("SELECT id FROM annex4_damaged_houses WHERE id = ? AND created_by = ?", [$record_id, $user_id]);
    if (!$check_stmt->fetch()) {
        set_flash('Record not found or access denied', 'error');
        header('Location: ../public/annex4.php');
        exit;
    }
}

// Sanitize input data
$totally_damaged = intval($_POST['totally_damaged'] ?? 0);
$partially_damaged = intval($_POST['partially_damaged'] ?? 0);
$total_damaged = intval($_POST['total_damaged'] ?? 0);
$cost = sanitize($_POST['cost'] ?? '');
$remarks = sanitize($_POST['remarks'] ?? '');

// =============================================================================
// CRITICAL FIX: Always calculate total on server side for data integrity
// =============================================================================
$calculated_total = $totally_damaged + $partially_damaged;

// Log any discrepancies for monitoring (optional)
if ($total_damaged != $calculated_total) {
    error_log("Annex4 Data Correction: Record #$record_id - Form submitted total: $total_damaged, Server calculated: $calculated_total. Using server calculation.");
}

// Use server-calculated total to ensure accuracy
$corrected_total_damaged = $calculated_total;

// Update the record with server-calculated total
$stmt = db_query("
    UPDATE annex4_damaged_houses 
    SET totally_damaged = ?, partially_damaged = ?, total_damaged = ?, cost = ?, remarks = ?, updated_at = CURRENT_TIMESTAMP 
    WHERE id = ?
", [$totally_damaged, $partially_damaged, $corrected_total_damaged, $cost, $remarks, $record_id]);

if ($stmt) {
    set_flash('Record updated successfully', 'success');
} else {
    set_flash('Failed to update record', 'error');
}

header('Location: ../public/annex4_records.php');
exit;
?>