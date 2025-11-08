<?php
// includes/config.php - UPDATED SECURE VERSION

// ============================================================
// PRODUCTION ERROR CONFIGURATION (SECURITY CRITICAL)
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);  // NEVER display errors to users
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/../logs')) {
    @mkdir(__DIR__ . '/../logs', 0755, true);
}

// Custom error handler for production
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno]: $errstr in $errfile:$errline");

    // Generic message to users (never expose details)
    if (!ini_get('display_errors')) {
        // Don't output anything - let the application handle errors gracefully
        return true;
    }
    return false;
});

// ============================================================
// DATABASE CREDENTIALS
// ============================================================
$db_host = '127.0.0.1';
$db_name = 'annex_management_system';
$db_user = 'root';
$db_pass = ''; // TODO: SET A STRONG PASSWORD!
$db_charset = 'utf8mb4';

$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";
$pdo_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false, // Security: use real prepared statements
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $pdo_options);
} catch (Exception $e) {
    // SECURITY FIX: Log error without exposing details to users
    error_log('CRITICAL: Database connection failed - ' . $e->getMessage());
    http_response_code(503); // Service Unavailable
    die('<!DOCTYPE html><html><head><title>Service Unavailable</title></head><body><h1>Service Temporarily Unavailable</h1><p>We are experiencing technical difficulties. Please try again later.</p></body></html>');
}

// SECURITY FIX: Custom PDO error handler to suppress verbose SQL errors
set_exception_handler(function($exception) {
    // Log the full error for debugging
    error_log('CRITICAL ERROR: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());

    // Show generic error to users
    http_response_code(500);
    die('<!DOCTYPE html><html><head><title>Error</title></head><body><h1>An Error Occurred</h1><p>We encountered an unexpected error. Please try again later.</p></body></html>');
});

// ============================================================
// SECURITY FIX: ENHANCED SESSION COOKIE HARDENING
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session name (not default PHPSESSID)
    session_name('NDRRMC_SESSION'); // Custom session name

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;

    session_set_cookie_params([
        'lifetime' => 0,        // Session cookie (expires when browser closes)
        'path' => '/',
        'domain' => '',         // Empty = current domain only
        'secure' => $secure,    // Only transmit over HTTPS (when available)
        'httponly' => true,     // Prevent JavaScript access (XSS protection)
        'samesite' => 'Strict'  // Strict CSRF protection
    ]);

    // Additional session security settings
    ini_set('session.use_strict_mode', 1);          // Reject uninitialized session IDs
    ini_set('session.use_only_cookies', 1);         // Don't accept session IDs via URL
    ini_set('session.use_trans_sid', 0);            // Don't pass session ID in URL
    ini_set('session.cookie_httponly', 1);          // HttpOnly flag
    ini_set('session.cookie_secure', $secure ? 1 : 0); // Secure flag when HTTPS
    ini_set('session.cookie_samesite', 'Strict');   // SameSite=Strict
    ini_set('session.sid_length', 48);              // Longer session ID (more entropy)
    ini_set('session.sid_bits_per_character', 6);   // More bits per character

    session_start();

    // Regenerate session ID periodically (every 30 minutes)
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Optional timezone default
date_default_timezone_set('Asia/Manila');

// Include security headers
require_once 'security_headers.php';

// Include functions
require_once 'functions.php';
?>