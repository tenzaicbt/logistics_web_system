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
    $stmt = $pdo->prepare("
      INSERT INTO fleets 
      (fleet_name, type, registration_no, capacity, status, manufacturer, model, year_built, location, notes) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if ($stmt->execute([
      $fleet_name, $type, $registration_no, $capacity, $status,
      $manufacturer, $model, $year_built, $location, $notes
    ])) {
      $success = true;
    } else {
      $errors[] = "Failed to add fleet.";
    }
  }
}
?>

<style>
  .alert a {
  text-decoration: none;
}

</style>


<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Add New Fleet</h2>
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
    <div class="alert alert-success">Fleet successfully added. <a href="manage_fleet.php">View all</a></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Fleet Name</label>
      <input type="text" name="fleet_name" class="form-control" required>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-select" required>
          <option value="">Select</option>
          <option value="Truck">Truck</option>
          <option value="Vessel">Vessel</option>
        </select>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
          <option value="Under Maintenance">Under Maintenance</option>
        </select>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Capacity</label>
        <input type="number" name="capacity" class="form-control">
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Registration Number</label>
        <input type="text" name="registration_no" class="form-control">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Manufacturer</label>
        <input type="text" name="manufacturer" class="form-control">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Model</label>
        <input type="text" name="model" class="form-control">
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Year Built</label>
        <input type="number" name="year_built" class="form-control" placeholder="e.g. 2020">
      </div>
      <div class="col-md-8 mb-3">
        <label class="form-label">Location</label>
        <input type="text" name="location" class="form-control">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" rows="3"></textarea>
    </div>

    <div class="mt-4 d-flex justify-content-between">
      <a href="manage_fleet.php" class="btn btn-secondary">Back</a>
      <button type="submit" class="btn btn-danger">Save Fleet</button>
    </div>
  </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
