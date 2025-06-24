<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Allow only admin or sub-admin
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'sub-admin'])) {
    header("Location: ../unauthorized.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$redirect = $_GET['redirect'] ?? 'manage_users.php';

if ($id && $id !== $_SESSION['user_id']) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: $redirect");
exit;
