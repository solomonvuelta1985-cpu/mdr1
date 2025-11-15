<?php
// includes/config.php - UPDATED SECURE VERSION

// ============================================================
// DEVELOPMENT MODE CONFIGURATION
// ============================================================
// Set to TRUE for development (shows errors), FALSE for production (hides errors)
define('DEBUG_MODE', true); // CHANGE TO false IN PRODUCTION

// ============================================================
// PRODUCTION ERROR CONFIGURATION (SECURITY CRITICAL)
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', DEBUG_MODE ? 1 : 0);  // Show errors in dev, hide in production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/../logs')) {
    @mkdir(__DIR__ . '/../logs', 0755, true);
}

// Custom error handler for production
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno]: $errstr in $errfile:$errline");

    // In debug mode, let PHP show errors normally
    if (DEBUG_MODE) {
        return false; // Use default error handler
    }

    // In production, suppress error output
    return true;
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

// SECURITY FIX: Custom exception handler to suppress verbose SQL errors
// Only catch PDOException to avoid breaking other error handling
set_exception_handler(function($exception) {
    // Log the full error for debugging
    error_log('CRITICAL ERROR: ' . get_class($exception) . ': ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());

    // Only show generic error for database exceptions
    if ($exception instanceof PDOException) {
        http_response_code(500);
        die('<!DOCTYPE html><html><head><title>Database Error</title></head><body><h1>Database Error</h1><p>We encountered a database error. Please try again later.</p></body></html>');
    }

    // For other exceptions, allow normal error handling
    throw $exception;
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

        // Update session fingerprint after regeneration
        if (isset($_SESSION['fingerprint'])) {
            $_SESSION['fingerprint'] = hash('sha256',
                ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') .
                ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0') .
                session_id()
            );
        }
    }
}

// Optional timezone default
date_default_timezone_set('Asia/Manila');

// ============================================================
// SITE CONFIGURATION
// ============================================================
define('SITE_NAME', 'NDRRMC Annex Management System');

// Include security headers
require_once 'security_headers.php';

// Include functions
require_once 'functions.php';
?>