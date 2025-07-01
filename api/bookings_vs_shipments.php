<?php
// api/bookings_vs_shipments.php
require_once __DIR__ . '/../includes/db.php';

$totalBookings  = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$totalShipments = (int)$pdo->query("SELECT COUNT(*) FROM shipments")->fetchColumn();

header('Content-Type: application/json');
echo json_encode([
  'labels' => ['Bookings','Shipments'],
  'data'   => [$totalBookings, $totalShipments],
]);
