<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';

if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'];
    
    // If admin, unlock their barangay
    if ($user_role === 'admin') {
        unlock_barangay($user_id);
    }
    
    // Track logout
    track_user_session($user_id, 'logout');
}

// Clear all session data
$_SESSION = [];

// Destroy session
session_destroy();

// Redirect to login
set_flash('You have been logged out successfully.', 'info');
header('Location: login.php');
exit;
?>