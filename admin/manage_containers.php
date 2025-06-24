<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Helper: Check role permission
global $pdo;
$userRole = $_SESSION['role'] ?? 'user';
function can($module, $perm) {
    global $pdo, $userRole;
    static $cache = [];
    if (!isset($cache[$userRole])) {
        $stmt = $pdo->prepare("SELECT module, can_view, can_create, can_edit, can_delete FROM roles_permissions WHERE role = ?");
        $stmt->execute([$userRole]);
        $cache[$userRole] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), null, 'module');
    }
    return isset($cache[$userRole][$module]) && $cache[$userRole][$module][$perm];
}

// Fetch data
$totalFleets = $pdo->query("SELECT COUNT(*) FROM fleets")->fetchColumn();
$totalContainers = $pdo->query("SELECT COUNT(*) FROM containers")->fetchColumn();
$fleets = can('manage_fleet','can_view') ? $pdo->query("SELECT * FROM fleets ORDER BY id DESC")->fetchAll() : [];
$containers = can('manage_containers','can_view') ? $pdo->query("SELECT * FROM containers ORDER BY id DESC")->fetchAll() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Fleet & Containers</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .dashboard-card { box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 5px solid #dc3545; }
    .table thead { background-color: #f1f1f1; }
  </style>
</head>
<body>
<div class="container py-5">
  <h2 class="mb-4 text-danger">Admin Dashboard - Fleet & Containers</h2>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="p-3 bg-white dashboard-card">
        <h6>Total Fleets</h6>
        <h3><?= $totalFleets ?></h3>
      </div>
    </div>
    <div class="col-md-6">
      <div class="p-3 bg-white dashboard-card">
        <h6>Total Containers</h6>
        <h3><?= $totalContainers ?></h3>
      </div>
    </div>
  </div>

  <?php if (!empty($fleets)): ?>
  <div class="mt-4">
    <h4 class="text-dark">Fleet Management</h4>
    <table class="table table-sm table-bordered bg-white">
      <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Reg. No</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($fleets as $f): ?>
        <tr>
          <td><?= $f['id'] ?></td>
          <td><?= htmlspecialchars($f['fleet_name']) ?></td>
          <td><?= $f['type'] ?></td>
          <td><?= $f['registration_no'] ?></td>
          <td><?= $f['capacity'] ?></td>
          <td><?= $f['status'] ?></td>
          <td>
            <?php if (can('manage_fleet','can_edit')): ?>
              <a href="fleet_form.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
            <?php endif; ?>
            <?php if (can('manage_fleet','can_delete')): ?>
              <a href="fleet_delete.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this fleet?')">Delete</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <?php if (!empty($containers)): ?>
  <div class="mt-5">
    <h4 class="text-dark">Container Management</h4>
    <table class="table table-sm table-bordered bg-white">
      <thead><tr><th>ID</th><th>Container No</th><th>Type</th><th>Status</th><th>Location</th><th>Fleet</th><th>Last Inspected</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($containers as $c):
        $fleet = $c['assigned_fleet_id'] ? $pdo->prepare("SELECT fleet_name FROM fleets WHERE id = ?") : null;
        $fleetName = '';
        if ($fleet) {
          $fleet->execute([$c['assigned_fleet_id']]);
          $fleetName = $fleet->fetchColumn();
        }
      ?>
        <tr>
          <td><?= $c['id'] ?></td>
          <td><?= htmlspecialchars($c['container_no']) ?></td>
          <td><?= $c['type'] ?></td>
          <td><?= $c['status'] ?></td>
          <td><?= htmlspecialchars($c['location']) ?></td>
          <td><?= htmlspecialchars($fleetName) ?></td>
          <td><?= $c['last_inspected_at'] ?></td>
          <td>
            <?php if (can('manage_containers','can_edit')): ?>
              <a href="container_form.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
            <?php endif; ?>
            <?php if (can('manage_containers','can_delete')): ?>
              <a href="container_delete.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this container?')">Delete</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
