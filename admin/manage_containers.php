<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$currentRole = $_SESSION['role'] ?? 'user';

if (!in_array($currentRole, ['admin', 'manager', 'employer'])) {
    echo "<div class='alert alert-danger m-5'>Access denied.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

// Filters
$typeFilter = $_GET['type'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$searchQuery = trim($_GET['q'] ?? '');

$where = [];
$params = [];

if ($typeFilter) {
    $where[] = 'c.type = ?';
    $params[] = $typeFilter;
}
if ($statusFilter) {
    $where[] = 'c.status = ?';
    $params[] = $statusFilter;
}
if ($searchQuery) {
    $where[] = '(c.container_no LIKE ? OR f.fleet_name LIKE ? OR c.location LIKE ?)';
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$sql = "SELECT c.*, f.fleet_name 
        FROM containers c
        LEFT JOIN fleets f ON c.assigned_fleet_id = f.id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY c.updated_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$containers = $stmt->fetchAll();
?>

<style>
  .btn {
    font-size: 0.85rem;
    padding: 0.3rem 0.8rem;
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
  /* .container {
    font-size: 0.85rem;
  } */
  h2 {
    font-size: 1.25rem;
  }
</style>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Manage Containers</h2>
    <?php if (in_array($currentRole, ['admin', 'manager'])): ?>
      <a href="add_container.php" class="btn btn-danger">Add Container</a>
    <?php endif; ?>
  </div>

  <div class="card p-3 shadow-sm mb-4">
    <form class="row g-2" method="GET">
      <div class="col-md-3">
        <select class="form-select" name="type">
          <option value="">All Types</option>
          <option value="20ft" <?= $typeFilter === '20ft' ? 'selected' : '' ?>>20ft</option>
          <option value="40ft" <?= $typeFilter === '40ft' ? 'selected' : '' ?>>40ft</option>
          <option value="Reefer" <?= $typeFilter === 'Reefer' ? 'selected' : '' ?>>Reefer</option>
          <option value="Open Top" <?= $typeFilter === 'Open Top' ? 'selected' : '' ?>>Open Top</option>
          <option value="Tank" <?= $typeFilter === 'Tank' ? 'selected' : '' ?>>Tank</option>
        </select>
      </div>
      <div class="col-md-3">
        <select class="form-select" name="status">
          <option value="">All Statuses</option>
          <option value="Available" <?= $statusFilter === 'Available' ? 'selected' : '' ?>>Available</option>
          <option value="In Use" <?= $statusFilter === 'In Use' ? 'selected' : '' ?>>In Use</option>
          <option value="Under Maintenance" <?= $statusFilter === 'Under Maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
          <option value="Damaged" <?= $statusFilter === 'Damaged' ? 'selected' : '' ?>>Damaged</option>
        </select>
      </div>
      <div class="col-md-4">
        <input type="text" name="q" class="form-control" placeholder="Search container no, fleet or location" value="<?= htmlspecialchars($searchQuery) ?>">
      </div>
      <div class="col-md-2">
        <button class="btn btn-outline-secondary w-100" type="submit">Filter</button>
      </div>
    </form>
  </div>

  <div class="table-responsive card shadow-sm">
    <table class="table table-hover mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th>Container No</th>
          <th>Type</th>
          <th>Status</th>
          <th>Location</th>
          <th>Fleet</th>
          <th>Last Inspected</th>
          <th>Created</th>
          <th>Updated</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($containers)): ?>
          <?php foreach ($containers as $c): ?>
            <tr>
              <td><?= htmlspecialchars($c['container_no']) ?></td>
              <td><?= htmlspecialchars($c['type']) ?></td>
              <td>
                <span class="badge 
                  <?= $c['status'] === 'Available' ? 'bg-success' : ($c['status'] === 'In Use' ? 'bg-primary' : ($c['status'] === 'Under Maintenance' ? 'bg-warning text-dark' : 'bg-danger')) ?>">
                  <?= htmlspecialchars($c['status']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($c['location'] ?? '-') ?></td>
              <td><?= htmlspecialchars($c['fleet_name'] ?? '-') ?></td>
              <td><?= $c['last_inspected_at'] ? date('Y-m-d', strtotime($c['last_inspected_at'])) : '-' ?></td>
              <td><?= date('Y-m-d', strtotime($c['created_at'])) ?></td>
              <td><?= date('Y-m-d', strtotime($c['updated_at'])) ?></td>
              <td class="text-nowrap">
                <a href="edit_container.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger">Edit</a>
                <?php if ($currentRole === 'admin'): ?>
                  <a href="delete_container.php?id=<?= $c['id'] ?>" onclick="return confirm('Delete this container?')" class="btn btn-sm btn-outline-danger">Delete</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" class="text-center text-muted">No containers found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
