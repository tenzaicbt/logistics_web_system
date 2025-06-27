<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

$role = $_SESSION['role'] ?? '';
if ($role !== 'admin') {
    echo "Access denied.";
    exit;
}

$shipmentId = $_GET['id'] ?? null;
if (!$shipmentId || !is_numeric($shipmentId)) {
    header("Location: manage_shipments.php?error=invalid_id");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM shipments WHERE id = ?");
$success = $stmt->execute([$shipmentId]);

if ($success) {
    header("Location: manage_shipments.php?msg=deleted");
} else {
    header("Location: manage_shipments.php?error=delete_failed");
}
exit;
?>
