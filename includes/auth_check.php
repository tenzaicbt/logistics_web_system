<?php
session_start();

require_once __DIR__ . '/../config/constants.php';

// Basic session check
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: /northport/auth/login.php');
    exit();
}

// Define requireRole function here
function requireRole(array $allowedRoles) {
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        http_response_code(403);
        echo "⛔ Access denied. You do not have permission to view this page.";
        exit();
    }
}
