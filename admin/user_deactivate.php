<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Allow only admin or sub-admin
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
    header("Location: ../unauthorized.php");
    exit;
}

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$redirect = $_GET['redirect'] ?? 'manage_users.php';

if ($userId > 0 && $userId !== $_SESSION['user_id']) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET is_active = 1 - is_active WHERE id = ?");
        $stmt->execute([$userId]);
    } catch (PDOException $e) {
        // Optionally log error
    }
}

header("Location: $redirect");
exit;
