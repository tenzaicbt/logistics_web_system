<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

$currentRole = $_SESSION['role'] ?? 'user';

if ($currentRole !== 'admin') {
    echo "<div class='alert alert-danger m-5'>Access denied.</div>";
    exit;
}

// Validate ID from GET
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: manage_containers.php?error=invalid_id");
    exit;
}

// Check if container exists
$stmt = $pdo->prepare("SELECT * FROM containers WHERE id = ?");
$stmt->execute([$id]);
$container = $stmt->fetch();

if (!$container) {
    header("Location: manage_containers.php?error=not_found");
    exit;
}

// Delete the container
$deleteStmt = $pdo->prepare("DELETE FROM containers WHERE id = ?");
$deleteStmt->execute([$id]);

header("Location: manage_containers.php?deleted=1");
exit;
?>
