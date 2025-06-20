<?php
session_start();

// Include constants and auth check helper (optional here if you want)
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is logged in and their role
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = $_SESSION['role'];

    if ($role === ROLE_ADMIN || $role === ROLE_SUB_ADMIN) {
        // Redirect admins and sub-admins to admin dashboard
        header('Location: admin/dashboard.php');
        exit();
    } elseif ($role === ROLE_USER) {
        // Redirect regular users to user dashboard
        header('Location: user/dashboard.php');
        exit();
    } else {
        // Unknown role - log out for safety
        session_destroy();
        header('Location: auth/login.php');
        exit();
    }
} else {
    // Not logged in - redirect to login page
    header('Location: auth/login.php');
    exit();
}
