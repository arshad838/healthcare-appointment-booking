<?php
// includes/auth.php
// Secure session initialization and role-based access control.

if (session_status() == PHP_SESSION_NONE) {
    // Enable secure sessions
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // If running on HTTPS, uncomment the next line
    // ini_set('session.cookie_secure', 1);
    session_start();
}

// Auto-detect base URL to support both XAMPP subfolder and Docker web root
if (!defined('BASE_URL')) {
    if (strpos($_SERVER['REQUEST_URI'], '/healthcare-booking') !== false) {
        define('BASE_URL', '/healthcare-booking/');
    } else {
        define('BASE_URL', '/');
    }
}

/**
 * Checks if a user is logged in. If not, redirects to login page.
 */
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        // Remember requested page to redirect after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: /healthcare-booking/auth/login.php");
        exit();
    }
}

/**
 * Checks if the logged-in user has one of the allowed roles.
 * If not, redirects to unauthorized access or dashboard.
 * 
 * @param array $allowed_roles Array of strings (e.g. ['admin', 'doctor'])
 */
function check_role($allowed_roles) {
    check_login();
    
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        // Prevent unauthorized role access by redirecting to appropriate dashboard
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: /healthcare-booking/admin/dashboard.php");
                break;
            case 'doctor':
                header("Location: /healthcare-booking/doctor/dashboard.php");
                break;
            case 'patient':
                header("Location: /healthcare-booking/patient/dashboard.php");
                break;
            default:
                header("Location: /healthcare-booking/index.php");
                break;
        }
        exit();
    }
}

/**
 * Generate a CSRF token and store it in session.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify form CSRF token against session token.
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("CSRF validation failed. Request blocked.");
    }
    return true;
}
?>
