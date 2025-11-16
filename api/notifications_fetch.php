<?php
/**
 * API endpoint for fetching user notifications
 * Returns JSON array of notifications
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/notifications.php';

require_login();

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'user';

// Only admins and staff can receive notifications
if ($user_role !== 'admin' && $user_role !== 'staff') {
    exit(json_encode([
        'count' => 0,
        'notifications' => []
    ]));
}

// Get unread count
$unread_count = get_unread_notification_count($user_id);

// Get recent notifications (50 most recent)
$notifications = get_notifications($user_id, 50, false);

// Format notifications for JSON response
$formatted_notifications = array_map(function($notif) {
    return [
        'id' => $notif['id'],
        'type' => $notif['type'],
        'annex_number' => $notif['annex_number'],
        'annex_name' => $notif['annex_name'],
        'action' => $notif['action'],
        'action_text' => format_notification_action($notif['action']),
        'barangay' => $notif['barangay'],
        'submitted_by_name' => $notif['submitted_by_name'],
        'record_id' => $notif['record_id'],
        'message' => $notif['message'],
        'is_priority' => (bool)$notif['is_priority'],
        'is_read' => (bool)$notif['is_read'],
        'created_at' => $notif['created_at'],
        'time_ago' => time_ago($notif['created_at'])
    ];
}, $notifications);

echo json_encode([
    'count' => $unread_count,
    'notifications' => $formatted_notifications
]);

/**
 * Format timestamp as "time ago" string
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = round($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = round($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = round($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $timestamp);
    }
}
