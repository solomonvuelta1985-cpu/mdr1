<?php
// security_headers_check.php
function checkSecurityHeaders($url) {
    $headers = get_headers($url, 1);
    
    $required_headers = [
        'X-Frame-Options' => 'Prevents clickjacking',
        'X-Content-Type-Options' => 'Prevents MIME sniffing',
        'X-XSS-Protection' => 'XSS protection',
        'Strict-Transport-Security' => 'HT enforcement',
        'Content-Security-Policy' => 'Content security'
    ];
    
    echo "<h3>Security Headers Check</h3>";
    
    foreach ($required_headers as $header => $description) {
        if (isset($headers[$header])) {
            echo "✅ $header: {$headers[$header]}<br>";
        } else {
            echo "❌ $header: MISSING ($description)<br>";
        }
    }
}

// Check your application
checkSecurityHeaders('http://localhost/mdr1/');
?>