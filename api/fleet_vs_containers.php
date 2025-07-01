<?php
// api/fleets_status.php
require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->query("
  SELECT status, COUNT(*) AS cnt
    FROM fleets
   GROUP BY status
");
$rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Ensure all three statuses appear
$all = ['Active','Inactive','Under Maintenance'];
$data = [];
foreach($all as $s) {
  $data[$s] = isset($rows[$s]) ? (int)$rows[$s] : 0;
}

header('Content-Type: application/json');
echo json_encode([
  'labels' => array_keys($data),
  'data'   => array_values($data),
]);
