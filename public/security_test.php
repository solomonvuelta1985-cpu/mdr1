<?php
// security_test.php - REMOVE THIS FILE AFTER TESTING!
session_start();
require_once '../includes/config.php';
require_once '../includes/functions1.php';

echo "<h1>Security Test Dashboard</h1>";

// Test CSRF Token Generation
echo "<h2>CSRF Token Test</h2>";
$token = generate_token();
echo "Generated Token: " . $token . "<br>";
echo "Session Token: " . ($_SESSION['csrf_token'] ?? 'Not set') . "<br>";
echo "Token Valid: " . (verify_token($token) ? 'YES' : 'NO') . "<br>";

// Test Rate Limiting
echo "<h2>Rate Limit Test</h2>";
$user_id = 1; // Test user
echo "Rate Limit Check: " . (check_rate_limit($user_id, 'test_action', 5, 60) ? 'ALLOWED' : 'BLOCKED') . "<br>";

// Test Database Connection
echo "<h2>Database Test</h2>";
try {
    $stmt = $pdo->query("SELECT 1");
    echo "Database: CONNECTED<br>";
} catch (Exception $e) {
    echo "Database: ERROR - " . $e->getMessage() . "<br>";
}

// Test Session Security
echo "<h2>Session Security Test</h2>";
echo "Session Fingerprint: " . (validate_session_fingerprint() ? 'VALID' : 'INVALID') . "<br>";

// Test Input Sanitization
echo "<h2>Input Sanitization Test</h2>";
$test_input = "<script>alert('xss')</script>";
$sanitized = sanitize($test_input);
echo "Original: " . htmlspecialchars($test_input) . "<br>";
echo "Sanitized: " . $sanitized . "<br>";
?>