<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();

// Only admins can lock barangays
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'lock_barangay') {
        // Verify CSRF token
        if (!verify_token($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
            exit;
        }
        
        $barangay = sanitize($_POST['barangay'] ?? '');
        
        if (empty($barangay)) {
            echo json_encode(['success' => false, 'message' => 'Barangay is required']);
            exit;
        }
        
        // Check if barangay is already locked by another admin
        $existing_lock = is_barangay_locked($barangay);
        if ($existing_lock && $existing_lock['admin_id'] != $_SESSION['user_id']) {
            echo json_encode([
                'success' => false, 
                'message' => "Barangay {$barangay} is currently being used by administrator {$existing_lock['admin_name']}"
            ]);
            exit;
        }
        
        // Lock the barangay immediately
        if (lock_barangay($barangay, $_SESSION['user_id'])) {
            // Also set session
            if (!isset($_SESSION['admin_barangay_sessions'])) {
                $_SESSION['admin_barangay_sessions'] = [];
            }
            $_SESSION['admin_barangay_sessions'][$barangay] = time();
            $_SESSION['admin_barangay'] = $barangay;
            
            echo json_encode([
                'success' => true, 
                'message' => "Barangay {$barangay} locked successfully"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to lock barangay']);
        }
    }
    
    elseif ($action === 'unlock_barangay') {
        // Verify CSRF token
        if (!verify_token($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
            exit;
        }
        
        $barangay = sanitize($_POST['barangay'] ?? '');
        unlock_specific_barangay($barangay);
        
        // Remove from session
        if (isset($_SESSION['admin_barangay_sessions'][$barangay])) {
            unset($_SESSION['admin_barangay_sessions'][$barangay]);
        }
        if (isset($_SESSION['admin_barangay']) && $_SESSION['admin_barangay'] === $barangay) {
            unset($_SESSION['admin_barangay']);
        }
        
        echo json_encode(['success' => true, 'message' => "Barangay {$barangay} unlocked"]);
    }
    
    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}
?>