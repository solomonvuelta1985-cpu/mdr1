<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

//   Ensure user is logged in
require_login();

//   Validate request type and required parameter
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty($_GET['id'])) {
    http_response_code(400);
    log_security_event($_SESSION['user_id'], 'invalid_method', 'Annex1 delete accessed with invalid method or missing ID');
    exit('Invalid request');
}

//   Verify CSRF token
if (!verify_token($_GET['csrf_token'] ?? '')) {
    log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex1 delete CSRF token mismatch');
    set_flash('Security token validation failed', 'error');
    header('Location: ../public/annex1_records.php');
    exit;
}

$record_id = (int) $_GET['id'];
$user_id   = $_SESSION['user_id'];
$is_admin  = ($_SESSION['user_role'] === 'admin');

//   Rate limit — max 10 deletes/hour per user
if (!check_rate_limit($user_id, 'annex1_delete', 10, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex1 delete rate limit exceeded');
    set_flash('Too many delete attempts. Please try again later.', 'error');
    header('Location: ../public/annex1_records.php');
    exit;
}

//   Verify ownership for non-admin users
if (!$is_admin) {
    $stmt   = db_query("SELECT created_by FROM annex1_related_incidents WHERE id = ?", [$record_id]);
    $record = $stmt->fetch();

    if (!$record || $record['created_by'] != $user_id) {
        log_security_event($user_id, 'unauthorized_access', "Attempted to delete Annex1 record #{$record_id} without permission");
        set_flash('You are not authorized to delete this record.', 'error');
        header('Location: ../public/annex1_records.php');
        exit;
    }
}

//   Soft delete (archive) inside transaction
try {
    $pdo->beginTransaction();

    $stmt = db_query("UPDATE annex1_related_incidents SET is_archived = 1 WHERE id = ?", [$record_id]);

    if ($stmt && $stmt->rowCount() > 0) {
        // 🧾 Log successful action
        log_audit_action($user_id, 'annex1_delete', "Archived (soft-deleted) record #{$record_id}");
        log_security_event($user_id, 'annex1_delete_success', "Successfully archived Annex1 record #{$record_id}");

        $pdo->commit();
        set_flash('Record deleted successfully (archived).', 'success');
    } else {
        $pdo->rollBack();
        log_security_event($user_id, 'annex1_delete_failed', "Failed to delete Annex1 record #{$record_id}");
        set_flash('Failed to delete record.', 'error');
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Annex1 delete error: " . $e->getMessage());
    log_security_event($user_id, 'annex1_delete_exception', "Error during delete: " . $e->getMessage());
    set_flash('A system error occurred while deleting the record.', 'error');
}

//   Redirect back to records page
header('Location: ../public/annex1_records.php');
exit;
?>
