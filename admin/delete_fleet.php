<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

$id = $_GET['id'] ?? null;
if ($id) {
  $stmt = $pdo->prepare("DELETE FROM fleets WHERE id = ?");
  $stmt->execute([$id]);
}
header("Location: manage_fleet.php");
exit;
