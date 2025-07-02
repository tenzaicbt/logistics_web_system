<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$role = $_SESSION['role'] ?? 'user';
$userId = $_SESSION['user_id'] ?? null;

// Unified Filters
$search = trim($_GET['q'] ?? $_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$type = $_GET['type'] ?? '';

$where = [];
$params = [];

if ($role === 'admin' || $role === 'manager') {
    if ($search) {
        $where[] = '(s.shipment_id LIKE ? OR s.origin LIKE ? OR s.destination LIKE ?)';
        array_push($params, "%$search%", "%$search%", "%$search%");
    }
    if ($status) {
        $where[] = 's.status = ?';
        $params[] = $status;
    }
    if ($type) {
        $where[] = 's.delivery_type = ?';
        $params[] = $type;
    }
} else {
    $where[] = 's.user_id = ?';
    $params[] = $userId;

    if ($search) {
        $where[] = '(s.shipment_id LIKE ? OR s.origin LIKE ? OR s.destination LIKE ?)';
        array_push($params, "%$search%", "%$search%", "%$search%");
    }
    if ($status) {
        $where[] = 's.status = ?';
        $params[] = $status;
    }
    if ($type) {
        $where[] = 's.delivery_type = ?';
        $params[] = $type;
    }
}

$sql = "SELECT s.*, u.username, c.container_no
        FROM shipments s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN containers c ON s.container_id = c.id";

if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$shipments = $stmt->fetchAll();

// Handle status update (admin only)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $role === 'admin' &&
    isset($_POST['update_status'], $_POST['shipment_id'], $_POST['new_status'])
) {
    $shipmentId = (int) $_POST['shipment_id'];
    $newStatus = $_POST['new_status'];
    $allowedStatuses = ['Pending', 'In Transit', 'Delivered', 'Cancelled'];

    if (in_array($newStatus, $allowedStatuses)) {
        $stmt = $pdo->prepare("UPDATE shipments SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $shipmentId]);
        header("Location: manage_shipments.php");
        exit;
    }
}
?>

<style>
    .btn,
    .form-control,
    .form-select {
        font-size: 0.85rem;
    }

    .btn {
        padding: 0.25rem 0.75rem;
    }

    .btn-danger {
        background-color: #e30613;
        border: none;
    }

    .btn-danger:hover {
        background-color: #b6050e;
    }

    .btn-secondary {
        background-color: #666;
        border: none;
    }

    .btn-secondary:hover {
        background-color: #444;
    }


</style>


<div class="container my-5">
    <h2 class="fw-bold mb-4">Manage Shipments</h2>
<!-- Shipment Filters -->
<form class="row gy-2 gx-3 align-items-center mb-4" method="get">

  <!-- Search -->
  <div class="col-md-3">
    <div class="input-group input-group-sm">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="text"
             name="q"
             value="<?= htmlspecialchars($search) ?>"
             class="form-control"
             placeholder="Search: shipment, origin, destination">
    </div>
  </div>

  <!-- Status -->
  <div class="col-md-2">
    <select name="status" class="form-select form-select-sm">
      <option value="">All Status</option>
      <option value="Pending"     <?= $status === 'Pending'     ? 'selected' : '' ?>>🟡 Pending</option>
      <option value="In Transit"  <?= $status === 'In Transit'  ? 'selected' : '' ?>>🔵 In Transit</option>
      <option value="Delivered"   <?= $status === 'Delivered'   ? 'selected' : '' ?>>🟢 Delivered</option>
      <option value="Cancelled"   <?= $status === 'Cancelled'   ? 'selected' : '' ?>>🔴 Cancelled</option>
    </select>
  </div>

  <!-- Delivery Type -->
  <div class="col-md-2">
    <select name="type" class="form-select form-select-sm">
      <option value="">All Delivery Types</option>
      <option value="Standard"   <?= $type === 'Standard'   ? 'selected' : '' ?>>Standard</option>
      <option value="Express"    <?= $type === 'Express'    ? 'selected' : '' ?>>Express</option>
      <option value="Overnight"  <?= $type === 'Overnight'  ? 'selected' : '' ?>>Overnight</option>
    </select>
  </div>

  <!-- Filter Button -->
  <div class="col-md-2 d-grid">
    <button class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-funnel-fill me-1"></i> Filter
    </button>
  </div>

  <!-- Create Shipment (Admin only) -->
  <?php if ($role === 'admin'): ?>
    <div class="col-md-3 text-end">
      <a href="create_shipment.php" class="btn btn-sm btn-danger">
        <i class="bi bi-plus-circle me-1"></i> Create Shipment
      </a>
    </div>
  <?php endif; ?>

</form>

    <div class="table-responsive card shadow-sm">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Shipment ID</th>
                    <th>Container</th>
                    <?php if ($role !== 'user'): ?><th>User</th><?php endif; ?>
                    <th>Origin → Destination</th>
                    <th>Dates</th>
                    <th>Delivery Type</th>
                    <th>Status</th>

                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$shipments): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No shipments found.</td>
                    </tr>
                    <?php else: foreach ($shipments as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['shipment_id']) ?></td>
                            <td><?= htmlspecialchars($s['container_no'] ?? '-') ?></td> <!-- ✅ Container value displayed -->
                            <?php if ($role !== 'user'): ?><td><?= htmlspecialchars($s['username']) ?></td><?php endif; ?>
                            <td><?= htmlspecialchars($s['origin']) ?> → <?= htmlspecialchars($s['destination']) ?></td>
                            <td><?= date('Y-m-d', strtotime($s['departure_date'])) ?> → <?= date('Y-m-d', strtotime($s['arrival_date'])) ?></td>
                            <td><?= htmlspecialchars($s['delivery_type']) ?></td>
                            <td>
                                <?php if ($role === 'admin'): ?>
                                    <form method="POST" class="m-0">
                                        <input type="hidden" name="shipment_id" value="<?= $s['id'] ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <?php foreach (['Pending', 'In Transit', 'Delivered', 'Cancelled'] as $option): ?>
                                                <option value="<?= $option ?>" <?= ($s['status'] === $option ? 'selected' : '') ?>><?= $option ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                <?php else: ?>
                                    <span class="badge <?= $s['status'] === 'Delivered' ? 'bg-success' : ($s['status'] === 'In Transit' ? 'bg-primary' : ($s['status'] === 'Pending' ? 'bg-warning text-dark' : 'bg-danger')) ?>">
                                        <?= htmlspecialchars($s['status']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="text-nowrap">
                                <a href="view_shipment_details.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <?php if ($role === 'admin' || ($role === 'user' && $s['status'] === 'Pending')): ?>
                                    <a href="edit_shipment.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <a href="cancel_shipment.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this shipment?')">Cancel</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>