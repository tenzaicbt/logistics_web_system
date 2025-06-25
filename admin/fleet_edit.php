<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$currentRole = $_SESSION['role'] ?? 'user';
if (!in_array($currentRole, ['admin', 'manager'])) {
  echo "<div class='alert alert-danger m-5'>Access denied.</div>";
  require_once '../includes/admin_footer.php';
  exit;
}

$errors = [];
$success = false;

// Get fleet ID from query param
$fleetId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($fleetId <= 0) {
  echo "<div class='alert alert-danger m-5'>Invalid Fleet ID.</div>";
  require_once '../includes/admin_footer.php';
  exit;
}

// Fetch existing fleet data
$stmt = $pdo->prepare("SELECT * FROM fleets WHERE id = ?");
$stmt->execute([$fleetId]);
$fleet = $stmt->fetch();

if (!$fleet) {
  echo "<div class='alert alert-danger m-5'>Fleet not found.</div>";
  require_once '../includes/admin_footer.php';
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fleet_name      = trim($_POST['fleet_name']);
  $type            = $_POST['type'];
  $registration_no = trim($_POST['registration_no']);
  $capacity        = (int)$_POST['capacity'];
  $status          = $_POST['status'];
  $manufacturer    = trim($_POST['manufacturer']);
  $model           = trim($_POST['model']);
  $year_built      = (int)$_POST['year_built'];
  $location        = trim($_POST['location']);
  $notes           = trim($_POST['notes']);

  if (!$fleet_name) $errors[] = "Fleet name is required.";
  if (!in_array($type, ['Vessel', 'Truck'])) $errors[] = "Invalid type.";
  if (!in_array($status, ['Active', 'Inactive', 'Under Maintenance'])) $errors[] = "Invalid status.";

  if (empty($errors)) {
    $updateStmt = $pdo->prepare("
      UPDATE fleets SET 
        fleet_name = ?, type = ?, registration_no = ?, capacity = ?, status = ?,
        manufacturer = ?, model = ?, year_built = ?, location = ?, notes = ?,
        updated_at = NOW()
      WHERE id = ?
    ");
    if ($updateStmt->execute([
      $fleet_name,
      $type,
      $registration_no,
      $capacity,
      $status,
      $manufacturer,
      $model,
      $year_built,
      $location,
      $notes,
      $fleetId
    ])) {
      $success = true;

      // Refresh fleet data after update
      $stmt->execute([$fleetId]);
      $fleet = $stmt->fetch();
    } else {
      $errors[] = "Failed to update fleet.";
    }
  }
}
?>

<style>
  .alert a {
    text-decoration: none;
  }

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
</style>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Edit Fleet: <?= htmlspecialchars($fleet['fleet_name']) ?></h2>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php elseif ($success): ?>
    <div class="alert alert-success">Fleet successfully updated. <a href="manage_fleet.php">View all</a></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Fleet Name</label>
      <input type="text" name="fleet_name" class="form-control" required value="<?= htmlspecialchars($fleet['fleet_name']) ?>">
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-select" required>
          <option value="">Select</option>
          <option value="Truck" <?= $fleet['type'] === 'Truck' ? 'selected' : '' ?>>Truck</option>
          <option value="Vessel" <?= $fleet['type'] === 'Vessel' ? 'selected' : '' ?>>Vessel</option>
        </select>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
          <option value="Active" <?= $fleet['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
          <option value="Inactive" <?= $fleet['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
          <option value="Under Maintenance" <?= $fleet['status'] === 'Under Maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
        </select>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Capacity</label>
        <input type="number" name="capacity" class="form-control" value="<?= (int)$fleet['capacity'] ?>">
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Registration Number</label>
        <input type="text" name="registration_no" class="form-control" value="<?= htmlspecialchars($fleet['registration_no']) ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Manufacturer</label>
        <input type="text" name="manufacturer" class="form-control" value="<?= htmlspecialchars($fleet['manufacturer']) ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Model</label>
        <input type="text" name="model" class="form-control" value="<?= htmlspecialchars($fleet['model']) ?>">
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Year Built</label>
        <input type="number" name="year_built" class="form-control" placeholder="e.g. 2020" value="<?= (int)$fleet['year_built'] ?>">
      </div>
      <div class="col-md-8 mb-3">
        <label class="form-label">Location</label>
        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($fleet['location']) ?>">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($fleet['notes']) ?></textarea>
    </div>

    <div class="mt-4 d-flex justify-content-between">
      <a href="manage_fleet.php" class="btn btn-secondary">Back</a>
      <button type="submit" class="btn btn-danger">Update Fleet</button>
    </div>
  </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>