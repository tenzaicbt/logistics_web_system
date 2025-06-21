<?php
require_once '../includes/db.php'; // Adjust path if needed

$email = 'master@northport';
$password = 'master123';

// Check if user already exists
$stmt = $pdo->prepare("SELECT * FROM master_admins WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    die("Master admin with this email already exists.\n");
}

// Hash the password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert into database
$stmt = $pdo->prepare("INSERT INTO master_admins (email, password_hash) VALUES (?, ?)");
if ($stmt->execute([$email, $hash])) {
    echo "Master admin created successfully.\n";
} else {
    echo "Failed to create master admin.\n";
}
