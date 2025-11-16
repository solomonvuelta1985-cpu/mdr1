<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/notifications.php'; // Notification system

require_login();

try {
    //   Ensure request method and ID
    if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id'])) {
        http_response_code(400);
        log_security_event($_SESSION['user_id'], 'invalid_method', 'Annex8 delete accessed without valid GET/ID');
        exit('Invalid request');
    }

    //   Verify CSRF token
    if (!verify_token($_GET['csrf_token'] ?? '')) {
        set_flash('Security token validation failed', 'error');
        log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex8 delete CSRF token mismatch');
        header('Location: ../public/annex8.php');
        exit;
    }

    $report_id = (int) $_GET['id'];

    //   Apply rate limiting (30 deletes/hour)
    $action = 'annex8_delete';
    if (!check_rate_limit($_SESSION['user_id'], $action, 30, 3600)) {
        set_flash('Too many delete attempts. Please try again later.', 'error');
        log_security_event($_SESSION['user_id'], 'rate_limit_exceeded', 'Annex8 delete rate limit exceeded');
        header('Location: ../public/annex8.php');
        exit;
    }

    //   Verify ownership and fetch record details for notification
    $stmt = db_query("
        SELECT a.created_by, a.barangay, a.type, a.road_section
        FROM annex8_road_bridge_status a
        WHERE a.id = ?
    ", [$report_id]);
    $report = $stmt ? $stmt->fetch() : null;

    if (!is_admin()) {
        if (!$report || $report['created_by'] != $_SESSION['user_id']) {
            set_flash('You are not authorized to delete this report', 'error');
            log_security_event($_SESSION['user_id'], 'unauthorized_delete_attempt', "User tried to delete Annex8 ID {$report_id}");
            header('Location: ../public/annex8.php');
            exit;
        }
    }

    if (!$report) {
        set_flash('Report not found', 'error');
        header('Location: ../public/annex8.php');
        exit;
    }

    //   Soft delete the record
    $stmt = db_query("UPDATE annex8_road_bridge_status SET is_archived = 1 WHERE id = ?", [$report_id]);

    if ($stmt && $stmt->rowCount() > 0) {
        // Audit log for successful deletion
        log_audit_action(
            $_SESSION['user_id'],
            'annex8_delete',
            "Deleted Annex8 report ID {$report_id}"
        );

        // Create notification for admins and staff
        $notification_message = "{$report['type']}: {$report['road_section']} - Deleted";
        create_notification(
            'annex8_delete',
            '8',
            'Status of Roads and Bridges',
            'deleted',
            $report['barangay'],
            $_SESSION['user_id'],
            $_SESSION['full_name'] ?? 'Unknown User',
            $report_id,
            $notification_message,
            is_priority_annex('8')
        );

        // Count this attempt for rate tracking
        check_rate_limit($_SESSION['user_id'], 'annex8_delete', 30, 3600);

        set_flash('Report deleted successfully', 'success');
    } else {
        set_flash('Failed to delete report', 'error');
        log_security_event($_SESSION['user_id'], 'annex8_delete_failed', "Failed deletion attempt for ID {$report_id}");
    }

} catch (Exception $e) {
    //   Handle unexpected errors safely
    error_log("Annex8 delete error: " . $e->getMessage());
    log_security_event(
        $_SESSION['user_id'],
        'annex8_delete_error',
        "Database or runtime error during Annex8 delete: " . $e->getMessage()
    );
    set_flash('An internal error occurred while deleting the report.', 'error');
}

header('Location: ../public/annex8.php');
exit;
