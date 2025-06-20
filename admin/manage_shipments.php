<?php
require_once '../includes/auth_check.php';
requireRole([ROLE_ADMIN]);

require_once '../config/db.php';
require_once '../includes/functions.php';

$success = '';
$error = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipment_id = (int)($_POST['shipment_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($shipment_id <= 0) {
        $error = "Invalid shipment ID.";
    } else {
        if ($action === 'update_status') {
            $new_status = $_POST['delivery_status'] ?? 'pending';
            $allowed_statuses = ['pending','in_transit','delivered','cancelled'];
            if (in_array($new_status, $allowed_statuses)) {
                $stmt = $pdo->prepare("UPDATE shipments SET delivery_status = ? WHERE id = ?");
                $stmt->execute([$new_status, $shipment_id]);
                $success = "Shipment status updated.";
            } else {
                $error = "Invalid status value.";
            }
        }

        if ($action === 'assign_fleet') {
            $fleet_id = (int)($_POST['fleet_id'] ?? 0);
            if ($fleet_id > 0) {
                $stmt = $pdo->prepare("UPDATE shipments SET fleet_id = ? WHERE id = ?");
                $stmt->execute([$fleet_id, $shipment_id]);
                $success = "Fleet assigned.";
            } else {
                $error = "Please select a fleet.";
            }
        }

        if ($action === 'assign_container') {
            $container_id = (int)($_POST['container_id'] ?? 0);
            if ($container_id > 0) {
                $stmt = $pdo->prepare("UPDATE shipments SET container_id = ? WHERE id = ?");
                $stmt->execute([$container_id, $shipment_id]);

                // Mark container as booked
                $pdo->prepare("UPDATE containers SET status = 'booked' WHERE id = ?")->execute([$container_id]);

                $success = "Container assigned.";
            } else {
                $error = "Please select a container.";
            }
        }

        if ($action === 'update_tracking_number') {
            $new_tracking = trim($_POST['tracking_number'] ?? '');
            if ($new_tracking === '') {
                $error = "Tracking number cannot be empty.";
            } else {
                // Check uniqueness
                $stmt = $pdo->prepare("SELECT id FROM shipments WHERE tracking_number = ? AND id != ?");
                $stmt->execute([$new_tracking, $shipment_id]);
                if ($stmt->fetch()) {
                    $error = "Tracking number already exists.";
                } else {
                    $stmt = $pdo->prepare("UPDATE shipments SET tracking_number = ? WHERE id = ?");
                    $stmt->execute([$new_tracking, $shipment_id]);
                    $success = "Tracking number updated.";
                }
            }
        }
    }
}

// Fetch shipments with related data
$sql = "SELECT s.*, u.name AS user_name, f.name AS fleet_name, c.code AS container_code
        FROM shipments s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN fleet f ON s.fleet_id = f.id
        LEFT JOIN containers c ON s.container_id = c.id
        ORDER BY s.created_at DESC";

$shipments = $pdo->query($sql)->fetchAll();

// Fetch fleets with acceptable status
$fleet_list = $pdo->query("SELECT id, name FROM fleet WHERE status IN ('active', 'maintenance')")->fetchAll();

// Fetch available containers
$containers = $pdo->query("SELECT id, code FROM containers WHERE status = 'available'")->fetchAll();

include '../includes/header.php';
?>

<h2 class="mb-4">📦 Manage Shipments</h2>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php elseif ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table table-bordered table-hover bg-white">
    <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Tracking Number</th>
            <th>User</th>
            <th>Origin → Destination</th>
            <th>Schedule Date</th>
            <th>Status</th>
            <th>Fleet</th>
            <th>Container</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($shipments as $s): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td>
                    <form method="post" class="d-flex align-items-center">
                        <input type="hidden" name="shipment_id" value="<?= $s['id'] ?>">
                        <input type="hidden" name="action" value="update_tracking_number">
                        <input type="text" name="tracking_number" value="<?= htmlspecialchars($s['tracking_number'] ?? '') ?>" class="form-control form-control-sm me-2" style="width:140px" required>
                        <button class="btn btn-sm btn-primary">Update</button>
                    </form>
                </td>
                <td><?= htmlspecialchars($s['user_name']) ?></td>
                <td><?= htmlspecialchars($s['origin']) ?> → <?= htmlspecialchars($s['destination']) ?></td>
                <td><?= htmlspecialchars($s['schedule_date']) ?></td>
                <td>
                    <form method="post" class="d-flex align-items-center">
                        <input type="hidden" name="shipment_id" value="<?= $s['id'] ?>">
                        <input type="hidden" name="action" value="update_status">
                        <select name="delivery_status" class="form-select form-select-sm me-2">
                            <?php
                            $statuses = ['pending','in_transit','delivered','cancelled'];
                            foreach ($statuses as $status) {
                                $selected = ($s['delivery_status'] === $status) ? 'selected' : '';
                                echo "<option value=\"$status\" $selected>" . ucfirst(str_replace('_',' ', $status)) . "</option>";
                            }
                            ?>
                        </select>
                        <button class="btn btn-sm btn-primary">Update</button>
                    </form>
                </td>
                <td>
                    <form method="post" class="d-flex align-items-center">
                        <input type="hidden" name="shipment_id" value="<?= $s['id'] ?>">
                        <input type="hidden" name="action" value="assign_fleet">
                        <select name="fleet_id" class="form-select form-select-sm me-2">
                            <option value="">-- Select Fleet --</option>
                            <?php foreach ($fleet_list as $f): ?>
                                <option value="<?= $f['id'] ?>" <?= ($s['fleet_id'] == $f['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-secondary">Assign</button>
                    </form>
                </td>
                <td>
                    <form method="post" class="d-flex align-items-center">
                        <input type="hidden" name="shipment_id" value="<?= $s['id'] ?>">
                        <input type="hidden" name="action" value="assign_container">
                        <select name="container_id" class="form-select form-select-sm me-2">
                            <option value="">-- Select Container --</option>
                            <?php foreach ($containers as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($s['container_id'] == $c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['code']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-secondary">Assign</button>
                    </form>
                </td>
                <td><small><?= date('Y-m-d H:i', strtotime($s['created_at'])) ?></small></td>
                <td><!-- Add any additional actions here if needed --></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
