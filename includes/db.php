<?php
// includes/db.php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,          // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Fetch assoc arrays by default
    PDO::ATTR_EMULATE_PREPARES => false,                  // Use native prepares
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('Database Connection Error: ' . $e->getMessage());
    exit('Database connection failed.');
}
