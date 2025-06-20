<?php
require_once '../includes/auth_check.php';
requireRole([ROLE_ADMIN]);

require_once '../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: manage_shipments.php');
    exit;
}

// Fetch shipment details with user, fleet, container info
$sql = "SELECT s.*, u.name AS user_name, u.email AS user_email, 
               f.name AS fleet_name, f.type AS fleet_type, f.capacity AS fleet_capacity,
               c.code AS container_code, c.size AS container_size, c.current_location
        FROM shipments s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN fleet f ON s.fleet_id = f.id
        LEFT JOIN containers c ON s.container_id = c.id
        WHERE s.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$shipment = $stmt->fetch();

if (!$shipment) {
    header('Location: manage_shipments.php');
    exit;
}

include '../includes/header.php';
?>

<h2>Shipment Details - #<?= $shipment['id'] ?></h2>

<table class="table table-bordered">
    <tr><th>Tracking Number</th><td><?= htmlspecialchars($shipment['tracking_number']) ?></td></tr>
    <tr><th>User</th><td><?= htmlspecialchars($shipment['user_name']) ?> (<?= htmlspecialchars($shipment['user_email']) ?>)</td></tr>
    <tr><th>Origin</th><td><?= htmlspecialchars($shipment['origin']) ?></td></tr>
    <tr><th>Destination</th><td><?= htmlspecialchars($shipment['destination']) ?></td></tr>
    <tr><th>Schedule Date</th><td><?= htmlspecialchars($shipment['schedule_date']) ?></td></tr>
    <tr><th>Delivery Status</th><td><?= ucfirst(str_replace('_',' ', $shipment['delivery_status'])) ?></td></tr>
    <tr><th>Cargo Details</th><td><?= nl2br(htmlspecialchars($shipment['cargo_details'])) ?></td></tr>
    <tr><th>Fleet</th><td><?= $shipment['fleet_name'] ? htmlspecialchars($shipment['fleet_name']) . " ({$shipment['fleet_type']}, Capacity: {$shipment['fleet_capacity']})" : '-' ?></td></tr>
    <tr><th>Container</th><td><?= $shipment['container_code'] ? htmlspecialchars($shipment['container_code']) . " (Size: {$shipment['container_size']}, Location: {$shipment['current_location']})" : '-' ?></td></tr>
    <tr><th>Created At</th><td><?= $shipment['created_at'] ?></td></tr>
</table>

<a href="manage_shipments.php" class="btn btn-secondary">Back to Shipments</a>

<?php include '../includes/footer.php'; ?>
