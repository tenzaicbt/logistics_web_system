<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
authorize(['admin', 'sub-admin']);

$id = (int) ($_GET['id'] ?? 0);
$redirect = $_GET['redirect'] ?? 'manage_users.php';

if ($id) {
    $defaultPassword = password_hash("123456", PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$defaultPassword, $id]);
}

header("Location: $redirect");
exit;
