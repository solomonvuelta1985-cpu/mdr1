<?php
/**
 * Main Entry Point for MDR1 NDRRMC System
 * Redirects to the appropriate page based on user session
 */

// Start session to check if user is already logged in
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    // User is logged in, redirect to dashboard
    header('Location: public/dashboard.php');
    exit;
} else {
    // User is not logged in, redirect to login page
    header('Location: public/login.php');
    exit;
}
