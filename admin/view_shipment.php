<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'user';

$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$type = $_GET['type'] ?? '';

$where = ['s.user_id = ?'];
$params = [$userId];

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

$sql = "SELECT s.*, c.container_no 
        FROM shipments s 
        LEFT JOIN containers c ON s.container_id = c.id 
        WHERE " . implode(' AND ', $where) . " 
        ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$shipments = $stmt->fetchAll();
?>

<style>
  .btn { font-size: 0.8rem; padding: 0.25rem 0.75rem; }
  .badge { font-size: 0.75rem; }
  .card-header, .table th { font-weight: 600; font-size: 0.85rem; }
</style>

<div class="container my-5">
  <h2 class="fw-bold mb-4">MY SHIPMENTS</h2>

  <form class="row g-2 mb-4">
    <div class="col-md-3">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search shipment, origin, destination">
    </div>
    <div class="col-md-2">
      <select name="status" class="form-select">
        <option value="">All Status</option>
        <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
        <option value="In Transit" <?= $status === 'In Transit' ? 'selected' : '' ?>>In Transit</option>
        <option value="Delivered" <?= $status === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
        <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
      </select>
    </div>
    <div class="col-md-2">
      <select name="type" class="form-select">
        <option value="">All Delivery Types</option>
        <option value="Standard" <?= $type === 'Standard' ? 'selected' : '' ?>>Standard</option>
        <option value="Express" <?= $type === 'Express' ? 'selected' : '' ?>>Express</option>
        <option value="Overnight" <?= $type === 'Overnight' ? 'selected' : '' ?>>Overnight</option>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-outline-secondary w-100">Filter</button>
    </div>
  </form>

  <div class="table-responsive card shadow-sm">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>Shipment No</th>
          <th>Container</th>
          <th>Origin → Destination</th>
          <th>Dates</th>
          <th>Delivery Type</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$shipments): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No shipments found.</td></tr>
        <?php else: foreach ($shipments as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['shipment_id']) ?></td>
            <td><?= htmlspecialchars($s['container_no'] ?? '-') ?></td>
            <td><?= htmlspecialchars($s['origin']) ?> → <?= htmlspecialchars($s['destination']) ?></td>
            <td>
              <?= date('Y-m-d', strtotime($s['departure_date'])) ?> → 
              <?= date('Y-m-d', strtotime($s['arrival_date'])) ?>
            </td>
            <td><?= htmlspecialchars($s['delivery_type']) ?></td>
            <td>
              <span class="badge 
                <?= $s['status'] === 'Delivered' ? 'bg-success' : 
                    ($s['status'] === 'In Transit' ? 'bg-primary' :
                    ($s['status'] === 'Pending' ? 'bg-warning text-dark' : 'bg-danger')) ?>">
                <?= htmlspecialchars($s['status']) ?>
              </span>
            </td>
            <td class="text-nowrap">
              <a href="view_single_shipment.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
              <?php if ($s['status'] === 'Pending'): ?>
                <a href="edit_shipment.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                <a href="cancel_shipment.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this shipment?')">Cancel</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
