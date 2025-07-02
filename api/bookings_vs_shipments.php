<?php
// api/shipments_status.php
require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->query("
  SELECT status, COUNT(*) AS count
  FROM shipments
  GROUP BY status
");

$rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$all = ['Pending', 'In Transit', 'Delivered', 'Cancelled'];
$data = [];
foreach ($all as $status) {
  $data[$status] = isset($rows[$status]) ? (int)$rows[$status] : 0;
}

header('Content-Type: application/json');
echo json_encode([
  'labels' => array_keys($data),
  'data' => array_values($data)
]);
