<?php
/**
 * Annex Access Control Functions
 * Functions to manage user access to specific annexes
 */

if (!function_exists('user_can_access_annex')) {
    /**
     * Check if user has access to a specific annex
     * @param int $user_id User ID
     * @param string $annex_number Annex number (e.g., '14', '15', '19', '20', '21')
     * @return bool True if user has access, false otherwise
     */
    function user_can_access_annex($user_id, $annex_number) {
        global $pdo;

        // Admin always has access
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            return true;
        }

        // Get user's allowed annexes
        try {
            $stmt = $pdo->prepare('SELECT allowed_annexes FROM users WHERE id = ?');
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || empty($user['allowed_annexes'])) {
                // If no specific annexes set, user has access to all (default behavior)
                return true;
            }

            $allowed = json_decode($user['allowed_annexes'], true);
            if (!is_array($allowed)) {
                return true;
            }

            return in_array($annex_number, $allowed);

        } catch (PDOException $e) {
            error_log('Error checking annex access: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('require_annex_access')) {
    /**
     * Require annex access or redirect
     * @param string $annex_number Annex number
     */
    function require_annex_access($annex_number) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../public/login.php');
            exit;
        }

        if (!user_can_access_annex($_SESSION['user_id'], $annex_number)) {
            $_SESSION['error'] = 'You do not have permission to access Annex ' . $annex_number . '.';
            header('Location: ../public/dashboard.php');
            exit;
        }
    }
}
