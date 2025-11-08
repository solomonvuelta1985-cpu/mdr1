<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

/**
 * Get client IP address
 */
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Track user session in database
 */
function track_user_session($user_id, $action = 'login') {
    global $pdo;

    if ($action === 'login') {
        $session_token = bin2hex(random_bytes(32));
        $ip_address = get_client_ip();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt = db_query("
            INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent) 
            VALUES (?, ?, ?, ?)
        ", [$user_id, $session_token, $ip_address, $user_agent]);

        if ($stmt) {
            $_SESSION['session_token'] = $session_token;
            return true;
        }
    } elseif ($action === 'update') {
        if (isset($_SESSION['session_token'])) {
            db_query("
                UPDATE user_sessions 
                SET last_activity = CURRENT_TIMESTAMP 
                WHERE session_token = ? AND is_active = TRUE
            ", [$_SESSION['session_token']]);
        }
    } elseif ($action === 'logout') {
        if (isset($_SESSION['session_token'])) {
            db_query("
                UPDATE user_sessions 
                SET is_active = FALSE 
                WHERE session_token = ?
            ", [$_SESSION['session_token']]);
        }
        
        // Clear admin barangay session if admin is logging out
        if (isset($_SESSION['admin_barangay'])) {
            $barangay = $_SESSION['admin_barangay'];
            if (isset($_SESSION['admin_barangay_sessions'][$barangay])) {
                unset($_SESSION['admin_barangay_sessions'][$barangay]);
            }
            unset($_SESSION['admin_barangay']);
        }
    }

    return false;
}

/**
 * Clean up old sessions (older than 30 days)
 */
function cleanup_old_sessions() {
    db_query("
        UPDATE user_sessions 
        SET is_active = FALSE 
        WHERE last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
}

/**
 * Get active session count for a user
 */
function get_active_session_count($user_id) {
    $stmt = db_query("
        SELECT COUNT(*) as session_count 
        FROM user_sessions 
        WHERE user_id = ? AND is_active = TRUE
    ", [$user_id]);
    $result = $stmt->fetch();
    return $result ? $result['session_count'] : 0;
}

/**
 * Terminate all active sessions for a user
 */
function terminate_all_sessions($user_id) {
    return db_query("
        UPDATE user_sessions 
        SET is_active = FALSE 
        WHERE user_id = ? AND is_active = TRUE
    ", [$user_id]);
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require user to be logged in
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('Please log in to access this page', 'error');
        header('Location: login.php');
        exit;
    }
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require admin privileges
 */
function require_admin() {
    require_login();

    if (!is_admin()) {
        set_flash('Access denied. Administrator privileges required.', 'error');
        header('Location: dashboard.php');
        exit;
    }
}

/**
 * =============================
 * ENHANCED DATABASE BARANGAY LOCKING
 * =============================
 */

/**
 * Lock barangay in database (works across all browsers)
 */
function lock_barangay($barangay, $admin_id) {
    // Get admin name for the lock record
    $stmt = db_query("SELECT full_name FROM users WHERE id = ?", [$admin_id]);
    $admin = $stmt->fetch();
    $admin_name = $admin ? $admin['full_name'] : 'Administrator';
    
    // Remove any existing locks for this admin (one admin can only lock one barangay at a time)
    db_query("DELETE FROM barangay_locks WHERE admin_id = ?", [$admin_id]);
    
    // Create new lock
    $result = db_query("
        INSERT INTO barangay_locks (barangay, admin_id, admin_name, lock_time) 
        VALUES (?, ?, ?, NOW())
    ", [$barangay, $admin_id, $admin_name]);
    
    return $result !== false;
}

/**
 * Unlock barangay (when admin logs out or changes barangay)
 */
function unlock_barangay($admin_id) {
    return db_query("DELETE FROM barangay_locks WHERE admin_id = ?", [$admin_id]);
}

/**
 * Unlock specific barangay
 */
function unlock_specific_barangay($barangay) {
    return db_query("DELETE FROM barangay_locks WHERE barangay = ?", [$barangay]);
}

/**
 * Check if barangay is locked (database check - works across browsers)
 */
function is_barangay_locked($barangay) {
    $stmt = db_query("
        SELECT bl.*, u.full_name as admin_name 
        FROM barangay_locks bl 
        JOIN users u ON bl.admin_id = u.id 
        WHERE bl.barangay = ? AND bl.lock_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ", [$barangay]);
    
    $lock = $stmt->fetch();
    return $lock ? $lock : false;
}

/**
 * Get currently locked barangay for admin
 */
function get_admin_locked_barangay($admin_id) {
    $stmt = db_query("
        SELECT barangay FROM barangay_locks 
        WHERE admin_id = ? AND lock_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ", [$admin_id]);
    
    $result = $stmt->fetch();
    return $result ? $result['barangay'] : null;
}

/**
 * Enhanced can_user_login with database locking
 */
function can_user_login($username) {
    if (empty($username)) return true;

    $stmt = db_query("SELECT id, barangay, user_role FROM users WHERE username = ?", [$username]);
    $user = $stmt->fetch();

    // Admins can always log in
    if (!$user || $user['user_role'] === 'admin') {
        return true;
    }

    $user_barangay = $user['barangay'];
    
    // Check database locks
    $lock = is_barangay_locked($user_barangay);
    if ($lock) {
        // Barangay is locked by admin
        return false;
    }

    return true;
}

/**
 * Get barangay lock message
 */
function get_barangay_lock_message($username) {
    $stmt = db_query("SELECT barangay FROM users WHERE username = ?", [$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        $barangay = $user['barangay'];
        $lock = is_barangay_locked($barangay);
        
        if ($lock) {
            return "The administrator <strong>{$lock['admin_name']}</strong> is currently using <strong>{$barangay}</strong> barangay. Please try again later.";
        }
    }
    
    return "The administrator is currently using this barangay. Please try again later.";
}

/**
 * Clean expired locks (older than 1 hour)
 */
function clean_expired_locks() {
    db_query("DELETE FROM barangay_locks WHERE lock_time < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
}

/**
 * =============================
 * ORIGINAL BARANGAY LOCKING MECHANISM
 * =============================
 */

/**
 * Check barangay access for users
 * Prevents login if admin is using the barangay
 */
function check_barangay_access($user_barangay) {
    // If admin locked this barangay, deny access
    if (isset($_SESSION['admin_barangay_sessions'][$user_barangay])) {
        return false;
    }
    return true;
}

/**
 * Set admin barangay session (called when admin selects a barangay)
 */
function set_admin_barangay_session($barangay) {
    if (!isset($_SESSION['admin_barangay_sessions'])) {
        $_SESSION['admin_barangay_sessions'] = [];
    }
    $_SESSION['admin_barangay_sessions'][$barangay] = time();
    $_SESSION['admin_barangay'] = $barangay; // store for reference
    
    // LOCK IN DATABASE (this works across browsers)
    return lock_barangay($barangay, $_SESSION['user_id']);
}

/**
 * Clean old barangay locks older than 1 hour
 */
function clean_old_barangay_sessions() {
    if (isset($_SESSION['admin_barangay_sessions'])) {
        $one_hour_ago = time() - 3600;
        foreach ($_SESSION['admin_barangay_sessions'] as $barangay => $timestamp) {
            if ($timestamp < $one_hour_ago) {
                unset($_SESSION['admin_barangay_sessions'][$barangay]);
            }
        }
    }
}

/**
 * Check if admin is logged to specific barangay
 */
function is_admin_logged_to_barangay($barangay) {
    return isset($_SESSION['admin_barangay_sessions'][$barangay]);
}

/**
 * Return barangay currently locked by admin
 */
function get_admin_locked_barangay_session(): ?string {
    return $_SESSION['admin_barangay'] ?? null;
}

/**
 * Prevent two admins from using same barangay
 */
function check_barangay_access_for_admin(string $barangay): bool {
    $locked_barangay = get_admin_locked_barangay_session();
    if ($locked_barangay && strcasecmp($locked_barangay, $barangay) === 0) {
        return false;
    }
    
    // Also check database
    $lock_stmt = db_query("SELECT * FROM barangay_locks WHERE barangay = ?", [$barangay]);
    $lock = $lock_stmt->fetch();
    
    if ($lock && $lock['admin_id'] != $_SESSION['user_id']) {
        return false;
    }
    
    return true;
}

/**
 * Get current user's barangay and role
 */
function get_user_location_data($user_id) {
    $stmt = db_query("
        SELECT barangay, user_role 
        FROM users 
        WHERE id = ?
    ", [$user_id]);
    return $stmt->fetch();
}

/**
 * Barangay list for Baggao, Cagayan
 */
function get_baggao_barangays() {
    return [
        'Adaoag', 'Agaman (Proper)', 'Agaman Norte', 'Agaman Sur', 'Alba', 'Annayatan',
        'Asassi', 'Asinga-Via', 'Awallan', 'Bacagan', 'Bagunot', 'Barsat East',
        'Barsat West', 'Bitag Grande', 'Bitag Pequeño', 'Bunugan', 'C. Verzosa (Valley Cove)',
        'Canagatan', 'Carupian', 'Catugay', 'Dabbac Grande', 'Dalin', 'Dalla',
        'Hacienda Intal', 'Ibulo', 'Immurung', 'J. Pallagao', 'Lasilat', 'Mabini',
        'Masical', 'Mocag', 'Nangalinan', 'Poblacion (Centro)', 'Remus', 'San Antonio',
        'San Francisco', 'San Isidro', 'San Jose', 'San Miguel', 'San Vicente',
        'Santa Margarita', 'Santor', 'Taguing', 'Taguntungan', 'Tallang', 'Taytay',
        'Temblique', 'Tungel'
    ];
}

/**
 * Logout user and clear sessions
 */
function logout_user() {
    if (isset($_SESSION['user_id'])) {
        // Unlock barangay if admin
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            unlock_barangay($_SESSION['user_id']);
        }
        
        track_user_session($_SESSION['user_id'], 'logout');
    }
    
    // Clear all session variables
    $_SESSION = [];
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * =====================================
 * AUTO-MAINTENANCE EXECUTION ON LOAD
 * =====================================
 */

// Clean expired database locks
clean_expired_locks();

// Clean old barangay sessions
clean_old_barangay_sessions();

// Occasionally clean old DB sessions (1% chance)
if (rand(1, 100) === 1) {
    cleanup_old_sessions();
}

// Update last activity timestamp
if (is_logged_in() && isset($_SESSION['session_token'])) {
    track_user_session($_SESSION['user_id'], 'update');
}

/**
 * Force unlock all barangays (for emergency or testing)
 */
function force_unlock_all_barangays() {
    return db_query("DELETE FROM barangay_locks");
}

/**
 * Check if admin has any active lock
 */
function has_admin_active_lock($admin_id) {
    $stmt = db_query("
        SELECT barangay FROM barangay_locks 
        WHERE admin_id = ? AND lock_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ", [$admin_id]);
    
    return $stmt->fetch();
}

?>