<?php
/**
 * Authentication Middleware for YK Digital Hub
 * 
 * Provides session-based authentication functions for the admin area.
 * All admin pages should include this file and call requireAdmin().
 */

// Ensure session is started (idempotent)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the current user is logged in.
 * 
 * @return bool True if authenticated, false otherwise.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Retrieve the currently authenticated user's data.
 * 
 * @return array|null Associative array with user data or null if not logged in.
 */
function currentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'role'  => $_SESSION['user_role']
    ];
}

/**
 * Require that the user is authenticated.
 * If not, redirect to the login page.
 * 
 * @param string $redirectTo Optional custom redirect URL (default: admin/login.php)
 */
function requireAdmin($redirectTo = null) {
    if (!isLoggedIn()) {
        if ($redirectTo === null) {
            if (defined('BASE_URL')) {
                $redirectTo = BASE_URL . '/admin/login.php';
            } else {
                $script = $_SERVER['SCRIPT_NAME'] ?? '';
                $admin_pos = strpos($script, '/admin/');
                $redirectTo = $admin_pos !== false
                    ? substr($script, 0, $admin_pos + strlen('/admin')) . '/login.php'
                    : '../login.php';
            }
        }
        header('Location: ' . $redirectTo);
        exit;
    }
}

/**
 * Log out the current user: destroy session data.
 * 
 * @return void
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];
    
    // If a session cookie exists, delete it
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * (Optional) Check if the user is an admin (role = 'admin').
 * Can be used later for authorization.
 */
function isAdmin() {
    return isLoggedIn() && ($_SESSION['user_role'] === 'admin');
}