<?php
// Database configuration - PDO

$host = 'localhost';             // Hostname
$dbname = 'northport';     // Your database name
$username = 'root';              // Your MySQL username
$password = '';                  // Your MySQL password (blank if using XAMPP default)
$charset = 'utf8mb4';            // Character set

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES => false,               // Use real prepared statements
];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=$charset",
        $username,
        $password,
        $options
    );
} catch (PDOException $e) {
    // Show clean error (or log to file instead in production)
    die('Database connection failed: ' . $e->getMessage());
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
