<?php
require_once __DIR__ . '/../includes/hash.php';

$plain_password = $_POST['password'];
$hashed_password = hash_password($plain_password);

// Save $hashed_password to the database
$stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)");
$stmt->execute([$email, $hashed_password, $role]);

if (password_verify($input_password, $user['password_hash'])) {
    // Login success
} else {
    // Invalid password
}

?>