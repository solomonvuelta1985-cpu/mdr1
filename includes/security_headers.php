<?php
/**
 * Global Security Headers Configuration
 * SECURITY FIX: Comprehensive HTTP security headers for all pages
 *
 * This file sets HTTP security headers to protect against common web vulnerabilities
 * Include this file at the top of all public-facing pages
 *
 * USAGE: require_once __DIR__ . '/../includes/security_headers.php';
 *
 * @version 2.0
 * @date 2025-01-08
 */

// Ensure this file is only included once
if (defined('SECURITY_HEADERS_LOADED')) {
    return;
}
define('SECURITY_HEADERS_LOADED', true);

// 1. PREVENT CLICKJACKING ATTACKS
header("X-Frame-Options: DENY");

// 2. PREVENT MIME TYPE SNIFFING
header("X-Content-Type-Options: nosniff");

// 3. XSS PROTECTION (Legacy Browser Support)
header("X-XSS-Protection: 1; mode=block");

// 4. REFERRER POLICY
header("Referrer-Policy: strict-origin-when-cross-origin");

// 5. CONTENT SECURITY POLICY (CSP)
// SECURITY FIX: Strengthened CSP policy
header("Content-Security-Policy: " .
    "default-src 'self'; " .
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
    "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net fonts.googleapis.com https://cdnjs.cloudflare.com; " .
    "font-src 'self' fonts.gstatic.com cdn.jsdelivr.net data:; " .
    "img-src 'self' data: https:; " .
    "connect-src 'self'; " .
    "frame-ancestors 'none'; " .
    "base-uri 'self'; " .
    "form-action 'self'; " .
    "upgrade-insecure-requests;"
);

// 6. PERMISSIONS POLICY
header("Permissions-Policy: " .
    "geolocation=(), " .
    "microphone=(), " .
    "camera=(), " .
    "payment=(), " .
    "usb=(), " .
    "magnetometer=(), " .
    "gyroscope=(), " .
    "accelerometer=()"
);

// 7. STRICT TRANSPORT SECURITY (HSTS)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}

// 8. CROSS-ORIGIN POLICIES
header("Cross-Origin-Embedder-Policy: require-corp");
header("Cross-Origin-Opener-Policy: same-origin");
header("Cross-Origin-Resource-Policy: same-origin");

// 9. CACHE CONTROL FOR SENSITIVE PAGES
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// 10. REMOVE SERVER SIGNATURE
header_remove("X-Powered-By");

// 11. X-Download-Options Header
header("X-Download-Options: noopen");

// 12. X-Permitted-Cross-Domain-Policies
header("X-Permitted-Cross-Domain-Policies: none");

// 13. X-DNS-Prefetch-Control
header("X-DNS-Prefetch-Control: off");

// 14. Expect-CT (Certificate Transparency)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header("Expect-CT: max-age=86400, enforce");
}
?>
