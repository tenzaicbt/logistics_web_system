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

// Handle filters
$typeFilter = $_GET['type'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$searchQuery = trim($_GET['q'] ?? '');

$where = [];
$params = [];

if ($typeFilter) {
  $where[] = 'type = ?';
  $params[] = $typeFilter;
}
if ($statusFilter) {
  $where[] = 'status = ?';
  $params[] = $statusFilter;
}
if ($searchQuery) {
  $where[] = '(fleet_name LIKE ? OR registration_no LIKE ?)';
  $params[] = "%$searchQuery%";
  $params[] = "%$searchQuery%";
}

$sql = "SELECT * FROM fleets";
if (!empty($where)) {
  $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY updated_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$fleets = $stmt->fetchAll();
?>

<style>
  .btn {
    font-size: 0.8rem;
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

  .container {
    font-size: 0.85rem;
  }
</style>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Manage Fleet</h2>
    <?php if (in_array($currentRole, ['admin', 'manager'])): ?>
      <a href="fleet_form.php" class="btn btn-danger">Add Fleet</a>
    <?php endif; ?>
  </div>

  <div class="card p-3 shadow-sm mb-4">
    <form class="row g-2" method="GET">
      <div class="col-md-3">
        <select class="form-select" name="type">
          <option value="">All Types</option>
          <option value="Truck" <?= $typeFilter === 'Truck' ? 'selected' : '' ?>>Truck</option>
          <option value="Vessel" <?= $typeFilter === 'Vessel' ? 'selected' : '' ?>>Vessel</option>
        </select>
      </div>
      <div class="col-md-3">
        <select class="form-select" name="status">
          <option value="">All Statuses</option>
          <option value="Active" <?= $statusFilter === 'Active' ? 'selected' : '' ?>>Active</option>
          <option value="Inactive" <?= $statusFilter === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
          <option value="Under Maintenance" <?= $statusFilter === 'Under Maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
        </select>
      </div>
      <div class="col-md-4">
        <input type="text" name="q" class="form-control" placeholder="Search by name or registration" value="<?= htmlspecialchars($searchQuery) ?>">
      </div>
      <div class="col-md-2">
        <button class="btn btn-outline-secondary w-100">Filter</button>
      </div>
    </form>
  </div>

  <div class="table-responsive card shadow-sm">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>Name</th>
          <th>Type</th>
          <th>Status</th>
          <th>Capacity</th>
          <th>Reg. No</th>
          <th>Manufacturer</th>
          <th>Model</th>
          <th>Year</th>
          <th>Location</th>
          <th>Updated</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($fleets)): ?>
          <?php foreach ($fleets as $fleet): ?>
            <tr>
              <td><?= htmlspecialchars($fleet['fleet_name']) ?></td>
              <td><?= htmlspecialchars($fleet['type']) ?></td>
              <td>
                <span class="badge 
                  <?= $fleet['status'] === 'Active' ? 'bg-success' : ($fleet['status'] === 'Inactive' ? 'bg-secondary' : 'bg-warning text-dark') ?>">
                  <?= htmlspecialchars($fleet['status']) ?>
                </span>
              </td>
              <td><?= (int)$fleet['capacity'] ?></td>
              <td><?= htmlspecialchars($fleet['registration_no']) ?></td>
              <td><?= htmlspecialchars($fleet['manufacturer'] ?? '-') ?></td>
              <td><?= htmlspecialchars($fleet['model'] ?? '-') ?></td>
              <td><?= htmlspecialchars($fleet['year_built'] ?? '-') ?></td>
              <td><?= htmlspecialchars($fleet['location'] ?? '-') ?></td>
              <td><?= date('M d, Y', strtotime($fleet['updated_at'])) ?></td>
              <td class="text-nowrap">
                <a href="fleet_view.php?id=<?= $fleet['id'] ?>" class="btn btn-sm btn-outline-danger">View</a>
                <?php if (in_array($currentRole, ['admin', 'manager'])): ?>
                  <a href="fleet_edit.php?id=<?= $fleet['id'] ?>" class="btn btn-sm btn-outline-danger">Edit</a>
                <?php endif; ?>
                <?php if ($currentRole === 'admin'): ?>
                  <a href="fleet_delete.php?id=<?= $fleet['id'] ?>" onclick="return confirm('Delete this fleet?')" class="btn btn-sm btn-outline-danger">Delete</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="11" class="text-center text-muted">No fleets found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>