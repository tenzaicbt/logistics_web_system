<?php
// File: admin/create_admin.php

// Show errors (for development only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

// Function to hash password securely
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Admin user data - change these to your preferred admin credentials
$name = 'Admin User';
$email = 'admin@northport.com';
$password = 'admin123';  // CHANGE this to a strong password before production
$role = 'admin';

try {
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "Admin user with email '$email' already exists.<br>";
        echo "<a href='/northport/auth/login.php'>Go to login</a>";
        exit;
    }

    // Insert admin user
    $password_hash = hash_password($password);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $password_hash, $role]);

    echo "✅ Admin user created successfully!<br>";
    echo "Email: <b>$email</b><br>";
    echo "Password: <b>$password</b><br>";
    echo "<a href='/northport/auth/login.php'>Go to login</a>";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
