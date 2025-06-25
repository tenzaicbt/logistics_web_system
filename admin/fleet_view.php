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

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
  echo "<div class='alert alert-danger m-5'>Invalid fleet ID.</div>";
  require_once '../includes/admin_footer.php';
  exit;
}

// Fetch fleet details from DB
$stmt = $pdo->prepare("SELECT * FROM fleets WHERE id = ?");
$stmt->execute([$id]);
$fleet = $stmt->fetch();

if (!$fleet) {
  echo "<div class='alert alert-warning m-5'>Fleet not found.</div>";
  require_once '../includes/admin_footer.php';
  exit;
}
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
</style>

<div class="container my-5">
  <h2 class="fw-bold mb-4">Fleet Details: <?= htmlspecialchars($fleet['fleet_name']) ?></h2>

  <div class="row g-3">
    <div class="col-md-6">
      <dl class="row">
        <dt class="col-sm-4">Fleet Name:</dt>
        <dd class="col-sm-8"><?= htmlspecialchars($fleet['fleet_name']) ?></dd>

        <dt class="col-sm-4">Type:</dt>
        <dd class="col-sm-8"><?= htmlspecialchars($fleet['type']) ?></dd>

        <dt class="col-sm-4">Registration Number:</dt>
        <dd class="col-sm-8"><?= htmlspecialchars($fleet['registration_no']) ?></dd>

        <dt class="col-sm-4">Capacity:</dt>
        <dd class="col-sm-8"><?= (int)$fleet['capacity'] ?></dd>

        <dt class="col-sm-4">Status:</dt>
        <dd class="col-sm-8">
          <span class="badge 
            <?= $fleet['status'] === 'Active' ? 'bg-success' : 
                ($fleet['status'] === 'Inactive' ? 'bg-secondary' : 'bg-warning text-dark') ?>">
            <?= htmlspecialchars($fleet['status']) ?>
          </span>
        </dd>

        <dt class="col-sm-4">Created At:</dt>
        <dd class="col-sm-8"><?= date('M d, Y', strtotime($fleet['created_at'])) ?></dd>

        <dt class="col-sm-4">Last Updated:</dt>
        <dd class="col-sm-8"><?= date('M d, Y', strtotime($fleet['updated_at'])) ?></dd>
      </dl>
    </div>
  </div>

  <div class="mt-4">
    <a href="manage_fleet.php" class="btn btn-secondary">Back to Fleet List</a>
    <?php if (in_array($currentRole, ['admin', 'manager'])): ?>
      <a href="fleet_edit.php?id=<?= $fleet['id'] ?>" class="btn btn-danger">Edit Fleet</a>
    <?php endif; ?>
  </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
