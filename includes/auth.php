<?php
// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set session timeout duration (30 minutes)
$timeout_duration = 1800;

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: /login.php?timeout=1');
    exit;
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

/**
 * Authorize access based on role(s)
 * 
 * @param string|array $allowed_roles - Single role or array of allowed roles
 */
if (!function_exists('authorize')) {
    function authorize($allowed_roles) {
        if (!isset($_SESSION['role'])) {
            header('Location: /unauthorized.php');
            exit;
        }

        // Normalize to array
        if (!is_array($allowed_roles)) {
            $allowed_roles = [$allowed_roles];
        }

        if (!in_array($_SESSION['role'], $allowed_roles)) {
            header('Location: /unauthorized.php');
            exit;
        }
    }
}
