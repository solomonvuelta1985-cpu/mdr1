<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Only admin and staff can clear notifications
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'staff')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'clear_all') {
        // Admin/staff can clear all notifications
        // Delete all notifications from the system
        $stmt = db_query("
            DELETE FROM notifications
        ");

        if ($stmt !== false) {
            echo json_encode([
                'success' => true,
                'message' => 'All notifications cleared',
                'deleted' => $stmt->rowCount()
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to clear notifications']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
