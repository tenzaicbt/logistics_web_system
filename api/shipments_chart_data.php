<?php
// api/monthly_shipments.php
require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->query("
  SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count
  FROM shipments
  GROUP BY month
  ORDER BY month
");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = array_column($data, 'month');
$counts = array_map('intval', array_column($data, 'count'));

header('Content-Type: application/json');
echo json_encode([
  'labels' => $labels,
  'data' => $counts
]);
