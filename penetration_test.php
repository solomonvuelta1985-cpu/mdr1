<?php
// penetration_test.php - FOR AUTHORIZED TESTING ONLY
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Penetration Test Results</h1>";

// Test 1: Session Hijacking
function testSessionHijacking() {
    echo "<h3>Session Hijacking Test</h3>";
    
    // Simulate session data change
    $_SESSION['test'] = 'original';
    $original_fingerprint = $_SESSION['fingerprint'] ?? null;
    
    // Change user agent (simulating hijack)
    $original_ua = $_SERVER['HTTP_USER_AGENT'];
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Attack Bot)';
    
    $is_secure = validate_session_fingerprint();
    
    // Restore original
    $_SERVER['HTTP_USER_AGENT'] = $original_ua;
    
    echo "Session Hijacking Protection: " . ($is_secure ? "BLOCKED" : "VULNERABLE") . "<br>";
}

// Test 2: Brute Force Protection
function testBruteForceProtection() {
    echo "<h3>Brute Force Protection Test</h3>";
    
    $user_id = 1;
    $action = 'login_attempt';
    
    for ($i = 1; $i <= 10; $i++) {
        $allowed = check_rate_limit($user_id, $action, 5, 60);
        echo "Attempt $i: " . ($allowed ? "ALLOWED" : "BLOCKED") . "<br>";
        
        if (!$allowed) {
            echo "✅ Rate limiting working correctly after $i attempts<br>";
            break;
        }
    }
}

// Test 3: Input Validation
function testInputValidation() {
    echo "<h3>Input Validation Test</h3>";
    
    $malicious_inputs = [
        'sql_injection' => "'; DROP TABLE users; --",
        'xss' => "<script>alert('hack')</script>",
        'path_traversal' => "../../../etc/passwd",
        'command_injection' => "; rm -rf /"
    ];
    
    foreach ($malicious_inputs as $type => $input) {
        $sanitized = sanitize($input);
        $is_safe = ($sanitized === htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8'));
        
        echo "$type: " . ($is_safe ? "SAFE" : "VULNERABLE") . "<br>";
        echo "Original: " . htmlspecialchars($input) . "<br>";
        echo "Sanitized: " . $sanitized . "<br><br>";
    }
}

// Run tests
testSessionHijacking();
testBruteForceProtection();
testInputValidation();
?>