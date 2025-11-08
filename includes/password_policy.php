<?php
/**
 * Strong Password Policy Validator
 * Enforces NIST 800-63B guidelines for password security
 *
 * SECURITY REQUIREMENTS:
 * - Minimum 12 characters length
 * - Mixed case (uppercase + lowercase)
 * - Numbers
 * - Special characters
 * - No common passwords
 * - No user information (username, email)
 *
 * @version 2.0
 * @date 2025-01-08
 */

/**
 * Validate password strength according to security policy
 *
 * @param string $password The password to validate
 * @param array $user_info Optional user info to prevent password containing username/email
 * @return array ['valid' => bool, 'errors' => array, 'strength_score' => int]
 */
function validate_password_strength($password, $user_info = []) {
    $errors = [];
    $strength_score = 0;

    // 1. Length requirement (minimum 12 characters)
    if (strlen($password) < 12) {
        $errors[] = "Password must be at least 12 characters long";
    } else {
        $strength_score += 20;

        // Bonus for longer passwords
        if (strlen($password) >= 16) {
            $strength_score += 10;
        }
        if (strlen($password) >= 20) {
            $strength_score += 10;
        }
    }

    // 2. Uppercase letters requirement
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    } else {
        $strength_score += 15;
    }

    // 3. Lowercase letters requirement
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    } else {
        $strength_score += 15;
    }

    // 4. Numbers requirement
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    } else {
        $strength_score += 15;
    }

    // 5. Special characters requirement
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)";
    } else {
        $strength_score += 15;
    }

    // 6. Check for common/weak passwords
    $common_passwords = [
        'password', 'password123', 'password1234',
        '123456', '12345678', '123456789', '1234567890',
        'admin', 'admin123', 'admin1234',
        'qwerty', 'qwerty123', 'qwertyuiop',
        'letmein', 'welcome', 'welcome123',
        'monkey', 'dragon', 'master',
        'sunshine', 'princess', 'starwars',
        'iloveyou', 'trustno1', 'football',
        'passw0rd', 'P@ssw0rd', 'P@ssword',
        'ndrrmc2025', 'ndrrmc123', 'disaster123'
    ];

    $password_lower = strtolower($password);
    foreach ($common_passwords as $common) {
        if ($password_lower === strtolower($common)) {
            $errors[] = "Password is too common and easily guessed";
            $strength_score = 0; // Instant fail
            break;
        }
    }

    // 7. Check for sequential characters (123, abc, etc.)
    if (preg_match('/(?:012|123|234|345|456|567|678|789|abc|bcd|cde|def)/i', $password)) {
        $errors[] = "Password should not contain sequential characters (123, abc, etc.)";
        $strength_score -= 10;
    }

    // 8. Check for repeated characters (aaa, 111, etc.)
    if (preg_match('/(.)\1{2,}/', $password)) {
        $errors[] = "Password should not contain repeated characters (aaa, 111, etc.)";
        $strength_score -= 10;
    }

    // 9. Check if password contains username or email
    if (!empty($user_info['username'])) {
        $username_lower = strtolower($user_info['username']);
        if (strlen($username_lower) >= 3 && stripos($password_lower, $username_lower) !== false) {
            $errors[] = "Password should not contain your username";
            $strength_score -= 20;
        }
    }

    if (!empty($user_info['email'])) {
        $email_parts = explode('@', $user_info['email']);
        $email_username = strtolower($email_parts[0]);
        if (strlen($email_username) >= 3 && stripos($password_lower, $email_username) !== false) {
            $errors[] = "Password should not contain parts of your email";
            $strength_score -= 20;
        }
    }

    // 10. Calculate character diversity bonus
    $unique_chars = count(array_unique(str_split($password)));
    if ($unique_chars >= strlen($password) * 0.7) {
        $strength_score += 10; // Good diversity
    }

    // Ensure score is between 0-100
    $strength_score = max(0, min(100, $strength_score));

    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'strength_score' => $strength_score,
        'strength_label' => get_strength_label($strength_score)
    ];
}

/**
 * Get password strength label
 *
 * @param int $score Strength score (0-100)
 * @return string Strength label
 */
function get_strength_label($score) {
    if ($score < 40) {
        return 'Weak';
    } elseif ($score < 70) {
        return 'Moderate';
    } elseif ($score < 90) {
        return 'Strong';
    } else {
        return 'Very Strong';
    }
}

/**
 * Generate a strong random password
 *
 * @param int $length Password length (default 16)
 * @return string Generated password
 */
function generate_strong_password($length = 16) {
    $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // Removed I, O for clarity
    $lowercase = 'abcdefghijkmnopqrstuvwxyz'; // Removed l for clarity
    $numbers = '23456789'; // Removed 0, 1 for clarity
    $special = '!@#$%^&*()_+-=[]{}';

    $password = '';

    // Ensure at least one of each required type
    $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
    $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
    $password .= $numbers[random_int(0, strlen($numbers) - 1)];
    $password .= $special[random_int(0, strlen($special) - 1)];

    // Fill the rest randomly
    $all_chars = $uppercase . $lowercase . $numbers . $special;
    for ($i = 4; $i < $length; $i++) {
        $password .= $all_chars[random_int(0, strlen($all_chars) - 1)];
    }

    // Shuffle the password
    $password = str_shuffle($password);

    return $password;
}

/**
 * Check if password has been pwned (breached)
 * Uses k-Anonymity model with Have I Been Pwned API
 *
 * @param string $password Password to check
 * @return array ['is_pwned' => bool, 'count' => int]
 */
function check_password_pwned($password) {
    // Hash the password
    $hash = strtoupper(sha1($password));
    $prefix = substr($hash, 0, 5);
    $suffix = substr($hash, 5);

    // Query Have I Been Pwned API (k-Anonymity: only send first 5 chars)
    $url = "https://api.pwnedpasswords.com/range/{$prefix}";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); // 3 second timeout
    curl_setopt($ch, CURLOPT_USERAGENT, 'NDRRMC-Security-Check');

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // If API fails, don't block the user (fail open)
    if ($http_code !== 200 || $response === false) {
        return ['is_pwned' => false, 'count' => 0, 'api_available' => false];
    }

    // Parse response
    $lines = explode("\n", $response);
    foreach ($lines as $line) {
        $parts = explode(':', trim($line));
        if (count($parts) === 2 && $parts[0] === $suffix) {
            return ['is_pwned' => true, 'count' => (int)$parts[1], 'api_available' => true];
        }
    }

    return ['is_pwned' => false, 'count' => 0, 'api_available' => true];
}

/**
 * Enforce password expiry policy
 *
 * @param int $user_id User ID
 * @param int $max_age_days Maximum password age in days (default 90)
 * @return array ['expired' => bool, 'age_days' => int, 'message' => string]
 */
function check_password_expiry($user_id, $max_age_days = 90) {
    try {
        $stmt = db_query(
            "SELECT password_changed_at FROM users WHERE id = ?",
            [$user_id]
        );

        $user = $stmt->fetch();

        if (!$user || empty($user['password_changed_at'])) {
            return [
                'expired' => true,
                'age_days' => 999,
                'message' => 'Password change date unknown. Please update your password.'
            ];
        }

        $password_date = new DateTime($user['password_changed_at']);
        $now = new DateTime();
        $age_days = $now->diff($password_date)->days;

        if ($age_days >= $max_age_days) {
            return [
                'expired' => true,
                'age_days' => $age_days,
                'message' => "Your password is {$age_days} days old and must be changed (max {$max_age_days} days)."
            ];
        }

        return [
            'expired' => false,
            'age_days' => $age_days,
            'message' => ''
        ];

    } catch (Exception $e) {
        error_log("Password expiry check error: " . $e->getMessage());
        return ['expired' => false, 'age_days' => 0, 'message' => ''];
    }
}

/**
 * Check password against previous passwords (prevent reuse)
 *
 * @param int $user_id User ID
 * @param string $new_password New password to check
 * @param int $history_count Number of previous passwords to check (default 5)
 * @return bool True if password is reused, False if password is new
 */
function is_password_reused($user_id, $new_password, $history_count = 5) {
    try {
        $stmt = db_query(
            "SELECT password_hash FROM password_history
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ?",
            [$user_id, $history_count]
        );

        while ($row = $stmt->fetch()) {
            if (password_verify($new_password, $row['password_hash'])) {
                return true; // Password has been used before
            }
        }

        return false; // Password is new

    } catch (Exception $e) {
        // If password_history table doesn't exist, just return false
        error_log("Password reuse check error: " . $e->getMessage());
        return false;
    }
}

/**
 * Save password to history
 *
 * @param int $user_id User ID
 * @param string $password_hash Password hash
 * @return bool Success status
 */
function save_password_to_history($user_id, $password_hash) {
    try {
        // Create table if it doesn't exist
        db_query("CREATE TABLE IF NOT EXISTS password_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        )");

        // Insert new password
        db_query(
            "INSERT INTO password_history (user_id, password_hash) VALUES (?, ?)",
            [$user_id, $password_hash]
        );

        // Keep only last 10 passwords
        db_query(
            "DELETE FROM password_history
             WHERE user_id = ?
             AND id NOT IN (
                 SELECT id FROM (
                     SELECT id FROM password_history
                     WHERE user_id = ?
                     ORDER BY created_at DESC
                     LIMIT 10
                 ) AS keep
             )",
            [$user_id, $user_id]
        );

        return true;

    } catch (Exception $e) {
        error_log("Save password history error: " . $e->getMessage());
        return false;
    }
}
