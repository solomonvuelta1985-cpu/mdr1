<?php
/**
 * API endpoint for marking notifications as read
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/notifications.php';

require_login();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'user';

// Only admins and staff can manage notifications
if ($user_role !== 'admin' && $user_role !== 'staff') {
    http_response_code(403);
    exit(json_encode(['error' => 'Access denied']));
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid JSON input']));
}

$action = $input['action'] ?? '';

if ($action === 'mark_one') {
    // Mark single notification as read
    $notification_id = $input['notification_id'] ?? 0;

    if (!$notification_id) {
        http_response_code(400);
        exit(json_encode(['error' => 'Notification ID required']));
    }

    $success = mark_notification_read($notification_id, $user_id);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Notification not found']);
    }

} elseif ($action === 'mark_all') {
    // Mark all notifications as read
    $success = mark_all_notifications_read($user_id);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to mark notifications as read']);
    }

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
}
