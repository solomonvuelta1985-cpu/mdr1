<?php
// includes/config.php - UPDATED SECURE VERSION
// Database credentials (edit for your environment)
$db_host = '127.0.0.1';
$db_name = 'annex_management_system';
$db_user = 'root';
$db_pass = ''; // set your DB password
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
    error_log('DB connection error: ' . $e->getMessage());
    http_response_code(500);
    echo "Server database error.";
    exit;
}

// --- ENHANCED Session cookie hardening ---
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict' // Enhanced from 'Lax' to 'Strict'
    ]);
    session_start();
}

// Optional timezone default
date_default_timezone_set('Asia/Manila');

// Include security headers
require_once 'security_headers.php';

// Include functions
require_once 'functions.php';
?>