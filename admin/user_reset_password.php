<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Only allow admins and sub-admins


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$redirect = $_GET['redirect'] ?? 'manage_users.php';

// Validate ID
if ($id <= 0) {
    $_SESSION['message'] = "Invalid user ID.";
    header("Location: $redirect");
    exit;
}

// New default password
$defaultPassword = password_hash('123456', PASSWORD_DEFAULT);

// Update the password in the database
try {
    // Use correct column name for password (adjust if your column is password_hash)
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$defaultPassword, $id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = "Password reset to <strong>123456</strong> for user ID $id.";
    } else {
        $_SESSION['message'] = "No user updated. Check if the user exists.";
    }

} catch (PDOException $e) {
    $_SESSION['message'] = "Error: " . $e->getMessage();
}

header("Location: $redirect");
exit;
