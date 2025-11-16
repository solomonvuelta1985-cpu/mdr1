<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/notifications.php';

header('Content-Type: application/json');

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Only admin and staff can access
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'staff')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

try {
    // Get enhanced barangay activity (with login + submission data)
    $activity = get_barangay_activity_enhanced(3);

    // Calculate statistics
    $total_barangays = count($activity);
    $active_online_count = 0;   // Online AND recently submitted
    $online_quiet_count = 0;     // Online but no recent submission
    $active_count = 0;           // Offline but recently active
    $quiet_count = 0;            // 3-6 hours
    $critical_count = 0;         // > 6 hours
    $no_activity_count = 0;      // No activity

    $active_online = [];
    $online_quiet = [];
    $active = [];
    $quiet = [];
    $critical = [];
    $no_activity = [];

    foreach ($activity as $item) {
        switch ($item['status']) {
            case 'active_online':
                $active_online_count++;
                $active_online[] = $item;
                break;
            case 'online_quiet':
                $online_quiet_count++;
                $online_quiet[] = $item;
                break;
            case 'active':
                $active_count++;
                $active[] = $item;
                break;
            case 'quiet':
                $quiet_count++;
                $quiet[] = $item;
                break;
            case 'critical':
                $critical_count++;
                $critical[] = $item;
                break;
            case 'no_activity':
            default:
                $no_activity_count++;
                $no_activity[] = $item;
                break;
        }
    }

    // Calculate alert count (online_quiet + quiet + critical)
    $alert_count = $online_quiet_count + $quiet_count + $critical_count;

    echo json_encode([
        'success' => true,
        'total' => $total_barangays,
        'active_online_count' => $active_online_count,
        'online_quiet_count' => $online_quiet_count,
        'active_count' => $active_count,
        'quiet_count' => $quiet_count,
        'critical_count' => $critical_count,
        'no_activity_count' => $no_activity_count,
        'alert_count' => $alert_count,
        'active_online' => $active_online,
        'online_quiet' => $online_quiet,
        'active' => $active,
        'quiet' => $quiet,
        'critical' => $critical,
        'no_activity' => $no_activity
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch barangay activity']);
}
