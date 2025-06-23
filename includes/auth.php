<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php'; // Make sure DB connection ($pdo) is available

// === CONFIGURABLE SETTINGS ===
define('SESSION_TIMEOUT', 1800); // 30 minutes

// === 1. SESSION & TIMEOUT CHECK ===
function check_session() {
    if (!isset($_SESSION['user_id'])) {
        redirect_to_login();
    }

    // Auto logout after timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_destroy();
        header("Location: /login.php?timeout=1");
        exit();
    }

    $_SESSION['last_activity'] = time();
}

// === 2. REDIRECT FUNCTION ===
function redirect_to_login() {
    header("Location: /login.php");
    exit();
}

// === 3. ROLE CHECK ===
function require_role($roles = []) {
    check_session();

    if (!in_array($_SESSION['role'], (array)$roles)) {
        http_response_code(403);
        die("Access denied. Your role does not have permission to access this page.");
    }
}

// === 4. PERMISSION CHECK ===
function has_permission($module, $action = 'can_view') {
    check_session();

    global $pdo;

    $stmt = $pdo->prepare("SELECT `$action` FROM roles_permissions WHERE role = ? AND module = ?");
    $stmt->execute([$_SESSION['role'], $module]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return ($result && $result[$action] == 1);
}

// === 5. OPTIONAL: ENFORCE PERMISSION IN PAGE ===
function enforce_permission($module, $action = 'can_view') {
    if (!has_permission($module, $action)) {
        http_response_code(403);
        die("Access denied. You do not have the required permission to access this functionality.");
    }
}
