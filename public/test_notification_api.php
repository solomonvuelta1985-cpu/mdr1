<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/notifications.php';

require_login(); // Use the same auth system

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    die('Not logged in. Please log in first.');
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'unknown';

echo "<h2>Notification System Test</h2>";
echo "<p>Current User ID: {$user_id}</p>";
echo "<p>Current User Role: {$user_role}</p>";
echo "<p>User Name: " . ($_SESSION['full_name'] ?? 'Unknown') . "</p>";
echo "<hr>";

// Check if user should see notifications
$should_see = ($user_role === 'admin' || $user_role === 'staff');
echo "<p><strong>Should see notifications:</strong> " . ($should_see ? 'YES' : 'NO') . "</p>";
echo "<hr>";

// Get unread count
$count = get_unread_notification_count($user_id);
echo "<p><strong>Unread notification count:</strong> {$count}</p>";
echo "<hr>";

// Get notifications
$notifications = get_notifications($user_id, 10, false);
echo "<h3>All Notifications (up to 10):</h3>";
echo "<pre>";
print_r($notifications);
echo "</pre>";
?>
