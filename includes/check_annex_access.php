<?php
/**
 * Check Annex Access Control
 * Restricts access to specific annexes based on user role
 */

// Restricted annexes - only admin and Sheila can access
$restricted_annexes = ['14', '15', '19', '20', '21'];

/**
 * Check if current user can access an annex
 * @param string $annex_number Annex number (e.g., '14', '15', '19', '20', '21')
 * @return bool
 */
function can_access_annex($annex_number) {
    global $restricted_annexes;

    // If not a restricted annex, everyone can access
    if (!in_array($annex_number, $restricted_annexes)) {
        return true;
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        return false;
    }

    // Admin always has access
    if ($_SESSION['user_role'] === 'admin') {
        return true;
    }

    // Check if user is Sheila
    if (isset($_SESSION['username']) && $_SESSION['username'] === 'sheila') {
        return true;
    }

    // Regular users cannot access restricted annexes
    return false;
}

/**
 * Require access to annex or redirect to dashboard with error
 * @param string $annex_number Annex number
 */
function require_annex_access_or_redirect($annex_number) {
    if (!can_access_annex($annex_number)) {
        $_SESSION['error_message'] = "You do not have permission to access Annex $annex_number. This annex is restricted to administrators only.";
        header('Location: dashboard.php');
        exit;
    }
}
