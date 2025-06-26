<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$redirect = $_GET['redirect'] ?? 'manage_users.php';

if ($id && $id !== $_SESSION['user_id']) {
    // Invalidate the session (requires you store sessions in DB or track login status)
    $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
    $stmt->execute([$id]);
    // Optional: log action or notify user
}

header("Location: $redirect");
exit;
