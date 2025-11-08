<?php
/**
 * Prevent multiple inclusions - MUST BE FIRST LINE AFTER <?php
 */
if (defined('FUNCTIONS_LOADED')) {
    return;
}
define('FUNCTIONS_LOADED', true);

if (!function_exists('sanitize')) {
    /**
     * Sanitize input data
     */
    function sanitize($data) {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('db_query')) {
    /**
     * Execute database query with prepared statements
     */
    function db_query($sql, $params = []) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date
     */
    function format_date($date, $format = 'Y-m-d H:i:s') {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return 'Invalid date';
        }
        return date($format, $timestamp);
    }
}

if (!function_exists('set_flash')) {
    /**
     * Set flash message
     */
    function set_flash($message, $type = 'info') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
}

if (!function_exists('show_flash')) {
    /**
     * Show flash message
     */
    function show_flash() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            $type = $_SESSION['flash_type'] ?? 'info';
            
            $alert_class = match($type) {
                'success' => 'alert-success',
                'error' => 'alert-danger',
                'warning' => 'alert-warning',
                default => 'alert-info'
            };
            
            echo "<div class='alert $alert_class alert-dismissible fade show' role='alert'>
                    $message
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
            
            // Clear flash message
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        }
    }
}

if (!function_exists('generate_token')) {
    /**
     * Generate CSRF token with expiration
     */
    function generate_token() {
        if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || 
            (time() - $_SESSION['csrf_token_time']) > 3600) { // 1 hour expiration
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_token')) {
    /**
     * Verify CSRF token with expiration check
     */
    function verify_token($token) {
        if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
            error_log("CSRF Error: No token in session");
            return false;
        }
        
        // Check token expiration (1 hour)
        if ((time() - $_SESSION['csrf_token_time']) > 3600) {
            error_log("CSRF Error: Token expired");
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            return false;
        }
        
        // Verify token match
        $isValid = hash_equals($_SESSION['csrf_token'], $token);
        if (!$isValid) {
            error_log("CSRF Error: Token mismatch");
        }
        
        return $isValid;
    }
}
if (!function_exists('is_ajax')) {
    /**
     * Check if request is AJAX
     */
    function is_ajax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

if (!function_exists('json_response')) {
    /**
     * JSON response helper
     */
    function json_response($data, $status_code = 200) {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

if (!function_exists('log_audit_action')) {
    /**
     * Log user actions to audit_logs table
     */
    function log_audit_action($user_id, $action, $details = null) {
        global $pdo;
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        try {
            $stmt = db_query("
                INSERT INTO audit_logs (user_id, action, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)
            ", [$user_id, $action, $details, $ip_address, $user_agent]);
            
            return $stmt !== false;
        } catch (Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('check_rate_limit')) {
    /**
     * Check rate limiting for actions
     */
    function check_rate_limit($user_id, $action, $max_attempts = 5, $time_window = 3600) {
        global $pdo;
        
        $current_time = time();
        $window_start = $current_time - $time_window;
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // FIX: Ensure we have a valid user_id from session if provided user_id is invalid
        if (empty($user_id) || $user_id <= 0) {
            $user_id = $_SESSION['user_id'] ?? 0;
        }
        
        // If still no valid user_id, use a placeholder but log it
        if (empty($user_id) || $user_id <= 0) {
            error_log("Rate limit warning: No valid user_id provided for action: $action");
            // Use a placeholder to avoid user_id = 0
            $user_id = -1; // Use -1 for unauthenticated/unidentifiable users
        }
        
        try {
            // Clean old rate limit entries
            db_query("DELETE FROM rate_limits WHERE attempt_time < ?", [$window_start]);
            
            // Count attempts in time window
            $stmt = db_query("
                SELECT COUNT(*) as attempt_count 
                FROM rate_limits 
                WHERE user_id = ? AND action = ? AND attempt_time > ?
            ", [$user_id, $action, $window_start]);
            
            $result = $stmt->fetch();
            $attempt_count = $result ? $result['attempt_count'] : 0;
            
            if ($attempt_count >= $max_attempts) {
                return false; // Rate limit exceeded
            }
            
            // Record this attempt - FIX: Use the validated user_id
            db_query("
                INSERT INTO rate_limits (user_id, action, attempt_time, ip_address) 
                VALUES (?, ?, ?, ?)
            ", [$user_id, $action, $current_time, $ip_address]);
            
            return true;
        } catch (Exception $e) {
            error_log("Rate limit check error: " . $e->getMessage());
            return true; // Allow on error
        }
    }
}

if (!function_exists('get_remaining_attempts')) {
    /**
     * Get remaining attempts for rate limiting
     */
    function get_remaining_attempts($user_id, $action, $max_attempts = 5, $time_window = 3600) {
        global $pdo;
        
        $window_start = time() - $time_window;
        
        try {
            $stmt = db_query("
                SELECT COUNT(*) as attempt_count 
                FROM rate_limits 
                WHERE user_id = ? AND action = ? AND attempt_time > ?
            ", [$user_id, $action, $window_start]);
            
            $result = $stmt->fetch();
            $attempt_count = $result ? $result['attempt_count'] : 0;
            
            return max(0, $max_attempts - $attempt_count);
        } catch (Exception $e) {
            error_log("Remaining attempts error: " . $e->getMessage());
            return $max_attempts;
        }
    }
}

if (!function_exists('log_security_event')) {
    /**
     * Log security events
     */
    function log_security_event($user_id, $event_type, $details = null) {
        global $pdo;
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        try {
            $stmt = db_query("
                INSERT INTO security_logs (user_id, event_type, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)
            ", [$user_id, $event_type, $details, $ip_address, $user_agent]);
            
            return $stmt !== false;
        } catch (Exception $e) {
            error_log("Security log error: " . $e->getMessage());
            return false;
        }
    }
}

// Replace the unprotected functions (around line 282) with:

if (!function_exists('validate_session_fingerprint')) {
    /**
     * Validate session fingerprint to detect session hijacking
     */
    function validate_session_fingerprint() {
        if (!isset($_SESSION['fingerprint'])) {
            // Create fingerprint on first validation
            $_SESSION['fingerprint'] = md5(
                $_SERVER['HTTP_USER_AGENT'] . 
                (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '') .
                session_id()
            );
            return true;
        }
        
        $current_fingerprint = md5(
            $_SERVER['HTTP_USER_AGENT'] . 
            (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '') .
            session_id()
        );
        
        return hash_equals($_SESSION['fingerprint'], $current_fingerprint);
    }
}

if (!function_exists('validate_csrf_with_operation')) {
    /**
     * Enhanced CSRF validation with operation tracking
     */
    function validate_csrf_with_operation($token, $operation) {
        if (!verify_token($token)) {
            return false;
        }
        
        // Additional: Validate that token hasn't been reused for different operations
        if (isset($_SESSION['last_csrf_operation']) && $_SESSION['last_csrf_operation'] === $operation) {
            log_security_event($_SESSION['user_id'] ?? 'unknown', 'csrf_reuse', "Potential CSRF token reuse for operation: {$operation}");
            return false;
        }
        
        $_SESSION['last_csrf_operation'] = $operation;
        return true;
    }
}

if (!function_exists('validate_annex8_enum_values')) {
    /**
     * Validate Annex8 enum values
     */
    function validate_annex8_enum_values($type, $classification, $status) {
        $valid_types = ['Road', 'Bridge'];
        $valid_classifications = ['National', 'Provincial', 'City/Municipal', 'Barangay'];
        $valid_statuses = ['Passable', 'Not Passable', 'Passable to Heavy Vehicles Only', 'Passable to Light Vehicles Only'];
        
        return in_array($type, $valid_types) && 
               in_array($classification, $valid_classifications) && 
               in_array($status, $valid_statuses);
    }
}

if (!function_exists('check_concurrent_requests')) {
    /**
     * Check concurrent requests to prevent flooding
     */
    function check_concurrent_requests($user_id, $action, $max_concurrent = 3) {
        $key = "concurrent_{$user_id}_{$action}";
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = 0;
        }
        
        $current = $_SESSION[$key];
        
        if ($current >= $max_concurrent) {
            log_security_event($user_id, 'concurrent_limit', "Too many concurrent {$action} requests");
            return false;
        }
        
        $_SESSION[$key] = $current + 1;
        return true;
    }
}

if (!function_exists('release_concurrent_request')) {
    /**
     * Release concurrent request counter
     */
    function release_concurrent_request($user_id, $action) {
        $key = "concurrent_{$user_id}_{$action}";
        if (isset($_SESSION[$key]) && $_SESSION[$key] > 0) {
            $_SESSION[$key]--;
        }
    }
}

if (!function_exists('validate_uploaded_file')) {
    /**
     * Enhanced file upload validation
     */
    function validate_uploaded_file($file_tmp, $file_name, $file_size) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Check file size
        if ($file_size > 5 * 1024 * 1024) {
            return false;
        }
        
        // Get MIME type
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $file_mime = finfo_file($file_info, $file_tmp);
        finfo_close($file_info);
        
        // Get file extension
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Double validation: MIME type and file extension
        if (!in_array($file_mime, $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
            return false;
        }
        
        // Additional image integrity checks
        if ($file_mime === 'image/jpeg' || $file_mime === 'image/jpg') {
            return @imagecreatefromjpeg($file_tmp) !== false;
        } elseif ($file_mime === 'image/png') {
            return @imagecreatefrompng($file_tmp) !== false;
        } elseif ($file_mime === 'image/gif') {
            return @imagecreatefromgif($file_tmp) !== false;
        }
        
        return false;
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize filename for secure storage
     */
    function sanitize_filename($filename) {
        // Remove path traversal characters
        $filename = basename($filename);
        
        // Replace special characters with underscores
        $sanitized = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $filename);
        
        // Limit length
        if (strlen($sanitized) > 255) {
            $sanitized = substr($sanitized, 0, 255);
        }
        
        return $sanitized;
    }
}

if (!function_exists('generate_secure_filename')) {
    /**
     * Generate secure filename with timestamp
     */
    function generate_secure_filename($original_name) {
        $sanitized_name = sanitize_filename($original_name);
        $extension = strtolower(pathinfo($sanitized_name, PATHINFO_EXTENSION));
        $name_without_ext = pathinfo($sanitized_name, PATHINFO_FILENAME);
        
        $unique_id = uniqid();
        $timestamp = date('Ymd_His');
        
        return "{$unique_id}_{$timestamp}_{$name_without_ext}.{$extension}";
    }
}