<?php
/**
 * Security Headers Configuration for Annex 7 Module
 *
 * This file sets HTTP security headers to protect against common web vulnerabilities
 * Include this file at the top of all public-facing Annex 7 pages
 *
 * USAGE: require_once __DIR__ . '/../includes/security_headers_annex7.php';
 */

// Ensure this file is only included once
if (defined('SECURITY_HEADERS_LOADED')) {
    return;
}
define('SECURITY_HEADERS_LOADED', true);

// 1. PREVENT CLICKJACKING ATTACKS
// Prevents the page from being loaded in an iframe/frame
// Protects against clickjacking attacks where attackers overlay invisible iframes
header("X-Frame-Options: DENY");

// 2. PREVENT MIME TYPE SNIFFING
// Prevents browsers from MIME-sniffing a response away from the declared content-type
// Reduces exposure to drive-by download attacks
header("X-Content-Type-Options: nosniff");

// 3. XSS PROTECTION (Legacy Browser Support)
// Enables browser's built-in XSS filter (mainly for older IE/Chrome versions)
// Modern browsers use CSP instead, but this provides defense-in-depth
header("X-XSS-Protection: 1; mode=block");

// 4. REFERRER POLICY
// Controls how much referrer information is sent with requests
// "strict-origin-when-cross-origin" sends full URL for same-origin, only origin for cross-origin
header("Referrer-Policy: strict-origin-when-cross-origin");

// 5. CONTENT SECURITY POLICY (CSP)
// The most powerful security header - defines approved sources of content
// This policy allows:
// - Scripts from same origin, inline scripts (for Bootstrap/jQuery), and CDN
// - Styles from same origin, inline styles (for custom CSS), Google Fonts, and CDN
// - Fonts from Google Fonts and CDN
// - Images from same origin and data: URIs (for base64 images)
// - AJAX/Fetch requests to same origin only
header("Content-Security-Policy: " .
    "default-src 'self'; " .                                                  // Default: only same origin
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .  // Scripts: same origin + inline + CDN
    "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net fonts.googleapis.com https://cdnjs.cloudflare.com; " .  // Styles: same origin + inline + Google Fonts + CDN
    "font-src 'self' fonts.gstatic.com cdn.jsdelivr.net data:; " .           // Fonts: Google Fonts + CDN + data URIs
    "img-src 'self' data: https:; " .                                         // Images: same origin + data URIs + any HTTPS
    "connect-src 'self'; " .                                                  // AJAX: same origin only
    "frame-ancestors 'none'; " .                                              // Iframe embedding: none (same as X-Frame-Options)
    "base-uri 'self'; " .                                                     // Base tag: same origin only
    "form-action 'self';"                                                     // Form submissions: same origin only
);

// 6. PERMISSIONS POLICY (formerly Feature-Policy)
// Controls which browser features and APIs can be used
// Disables potentially dangerous features like camera, microphone, geolocation
header("Permissions-Policy: " .
    "geolocation=(), " .        // Disable geolocation API
    "microphone=(), " .         // Disable microphone access
    "camera=(), " .             // Disable camera access
    "payment=(), " .            // Disable Payment Request API
    "usb=(), " .                // Disable WebUSB
    "magnetometer=(), " .       // Disable magnetometer
    "gyroscope=(), " .          // Disable gyroscope
    "accelerometer=()"          // Disable accelerometer
);

// 7. STRICT TRANSPORT SECURITY (HSTS)
// IMPORTANT: Only enable when HTTPS is properly configured!
// Forces browsers to always use HTTPS for this domain
// Uncomment the following lines ONLY after HTTPS is configured:
/*
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    // max-age=31536000 = 1 year
    // includeSubDomains = applies to all subdomains
    // preload = allow inclusion in browser's HSTS preload list
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}
*/

// 8. CROSS-ORIGIN POLICIES
// Prevent cross-origin sharing of resources
header("Cross-Origin-Embedder-Policy: require-corp");
header("Cross-Origin-Opener-Policy: same-origin");
header("Cross-Origin-Resource-Policy: same-origin");

// 9. CACHE CONTROL FOR SENSITIVE PAGES
// Prevents caching of sensitive data
// Use this for pages with personal/financial information
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// 10. REMOVE SERVER SIGNATURE (if not already done in php.ini)
// Hides PHP version information from response headers
header_remove("X-Powered-By");

// 11. SECURITY FIX: X-Download-Options Header (IE8+ protection)
// Prevents IE from executing downloads in the context of your site
header("X-Download-Options: noopen");

// 12. SECURITY FIX: X-Permitted-Cross-Domain-Policies
// Restricts Adobe Flash and PDF cross-domain requests
header("X-Permitted-Cross-Domain-Policies: none");

/**
 * SECURITY LOGGING
 * Log that security headers have been applied (optional, for audit purposes)
 */
if (function_exists('log_security_event') && isset($_SESSION['user_id'])) {
    // Only log once per session to avoid flooding logs
    if (!isset($_SESSION['security_headers_applied_annex7'])) {
        log_security_event(
            $_SESSION['user_id'],
            'security_headers_applied',
            'Security headers loaded for Annex 7 module'
        );
        $_SESSION['security_headers_applied_annex7'] = true;
    }
}

/**
 * DEVELOPMENT MODE WARNING
 * In development, CSP violations can be reported to console
 */
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    // In production, remove 'unsafe-inline' and 'unsafe-eval' for maximum security
    // Replace with nonce-based or hash-based CSP
    error_log("SECURITY NOTICE: Annex 7 security headers loaded in DEBUG mode");
}

/**
 * NOTES FOR FUTURE HARDENING:
 *
 * 1. CSP NONCES: For maximum security, replace 'unsafe-inline' with nonce-based CSP
 *    Example: <script nonce="<?php echo $csp_nonce; ?>">
 *
 * 2. SUBRESOURCE INTEGRITY (SRI): Add integrity hashes to CDN resources
 *    Example: <script src="cdn.js" integrity="sha384-xxx" crossorigin="anonymous">
 *
 * 3. CERTIFICATE TRANSPARENCY: Enable Expect-CT header when using HTTPS
 *    header("Expect-CT: max-age=86400, enforce");
 *
 * 4. PUBLIC KEY PINNING: Consider HTTP Public Key Pinning (HPKP) - advanced use only
 *    WARNING: HPKP can lock users out if misconfigured. Use with extreme caution.
 */
