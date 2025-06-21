<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout duration in seconds (30 minutes)
$timeout_duration = 1800;

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Session timeout check
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: /login.php?timeout=1');
    exit;
}

// Refresh activity timestamp
$_SESSION['last_activity'] = time();

/**
 * Role authorization
 *
 * @param string|array $allowed_roles
 */
if (!function_exists('authorize')) {
    function authorize($allowed_roles) {
        if (!isset($_SESSION['role'])) {
            header('Location: /unauthorized.php');
            exit;
        }

        $roles = is_array($allowed_roles) ? $allowed_roles : [$allowed_roles];

        if (!in_array($_SESSION['role'], $roles)) {
            header('Location: /unauthorized.php');
            exit;
        }
    }
}

function requireLogin($role = null) {
    // Check login
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        // Redirect to login with redirect param to come back after login
        header("Location: /login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }

    // Check role if specified
    if ($role !== null && (!isset($_SESSION['role']) || $_SESSION['role'] !== $role)) {
        header("Location: /unauthorized.php");
        exit;
    }

    // Session timeout (30 min)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_unset();
        session_destroy();
        header("Location: /login.php?timeout=1");
        exit;
    }

    $_SESSION['last_activity'] = time();
}
