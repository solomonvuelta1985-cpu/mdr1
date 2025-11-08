<?php
// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();

//   Enforce POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_security_event($_SESSION['user_id'], 'invalid_method', 'Annex1 update accessed using non-POST method');
    set_flash('Invalid request method', 'error');
    header('Location: ../public/annex1.php');
    exit;
}

//   Verify CSRF token
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex1 update CSRF token mismatch');
    set_flash('Security token validation failed', 'error');
    header('Location: ../public/annex1.php');
    exit;
}

//   Rate limiting: Allow 20 updates per hour per user
if (!check_rate_limit($_SESSION['user_id'], 'annex1_update', 20, 3600)) {
    log_security_event($_SESSION['user_id'], 'rate_limit_exceeded', 'Annex1 update rate limit exceeded');
    set_flash('Too many update attempts. Please try again later.', 'error');
    header('Location: ../public/annex1_records.php');
    exit;
}

//   Validate record ID
if (!isset($_POST['record_id'])) {
    log_security_event($_SESSION['user_id'], 'missing_id', 'Annex1 update attempted without record ID');
    set_flash('Record ID is required', 'error');
    header('Location: ../public/annex1.php');
    exit;
}

$record_id = sanitize($_POST['record_id']);
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_role'] === 'admin');

//   Check record ownership unless admin
if (!$is_admin) {
    $check_stmt = db_query("SELECT * FROM annex1_related_incidents WHERE id = ? AND created_by = ?", [$record_id, $user_id]);
    $existing = $check_stmt->fetch();
    if (!$existing) {
        log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to update Annex1 record #{$record_id} without permission");
        set_flash('Record not found or access denied', 'error');
        header('Location: ../public/annex1.php');
        exit;
    }
} else {
    // For admin, fetch original record for logging
    $check_stmt = db_query("SELECT * FROM annex1_related_incidents WHERE id = ?", [$record_id]);
    $existing = $check_stmt->fetch();
    if (!$existing) {
        set_flash('Record not found', 'error');
        header('Location: ../public/annex1.php');
        exit;
    }
}

//   Sanitize inputs
$incident_type   = sanitize($_POST['incident_type'] ?? '');
$occurrence_date = sanitize($_POST['occurrence_date'] ?? '');
$description     = sanitize($_POST['description'] ?? '');
$actions_taken   = sanitize($_POST['actions_taken'] ?? '');
$remarks         = sanitize($_POST['remarks'] ?? '');

//   Validate required field(s)
if (empty($incident_type)) {
    set_flash('Incident type is required', 'error');
    header('Location: ../public/annex1.php');
    exit;
}

//   Prepare changes for audit comparison
$changes = [];
if ($incident_type !== $existing['incident_type']) $changes[] = "Incident Type: {$existing['incident_type']} → {$incident_type}";
if ($occurrence_date !== $existing['occurrence_date']) $changes[] = "Occurrence Date updated";
if ($description !== $existing['description']) $changes[] = "Description modified";
if ($actions_taken !== $existing['actions_taken']) $changes[] = "Actions Taken modified";
if ($remarks !== $existing['remarks']) $changes[] = "Remarks updated";

$changes_str = $changes ? implode(', ', $changes) : 'No major field changes';

//   Try update in transaction
try {
    $pdo->beginTransaction();

    $stmt = db_query("
        UPDATE annex1_related_incidents 
        SET incident_type = ?, occurrence_date = ?, description = ?, actions_taken = ?, remarks = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ", [$incident_type, $occurrence_date, $description, $actions_taken, $remarks, $record_id]);

    if ($stmt && $stmt->rowCount() > 0) {
        //   Log successful update in audit log
        log_audit_action(
            $_SESSION['user_id'],
            'annex1_update',
            "Updated record #{$record_id} ({$incident_type}) - {$changes_str}"
        );

        //   Log successful security event
        log_security_event(
            $_SESSION['user_id'],
            'annex1_update_success',
            "Successfully updated Annex1 record #{$record_id}"
        );

        $pdo->commit();
        set_flash('Record updated successfully', 'success');
    } else {
        $pdo->rollBack();
        log_security_event($_SESSION['user_id'], 'annex1_update_failed', "Annex1 update failed for record #{$record_id}");
        set_flash('Failed to update record', 'error');
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    log_security_event($_SESSION['user_id'], 'annex1_update_exception', "Error updating record #{$record_id}: " . $e->getMessage());
    set_flash('Database error occurred during update', 'error');
}

header('Location: ../public/annex1_records.php');
exit;
?>
