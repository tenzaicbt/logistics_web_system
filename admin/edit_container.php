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

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    echo "<div class='alert alert-warning m-5'>Invalid container ID.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

// Fetch container
$stmt = $pdo->prepare("SELECT * FROM containers WHERE id = ?");
$stmt->execute([$id]);
$container = $stmt->fetch();

if (!$container) {
    echo "<div class='alert alert-warning m-5'>Container not found.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

// Fetch fleets for dropdown
$fleets = $pdo->query("SELECT id, fleet_name FROM fleets WHERE status='Active' ORDER BY fleet_name")->fetchAll();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $container_no = trim($_POST['container_no'] ?? '');
    $type = $_POST['type'] ?? '';
    $status = $_POST['status'] ?? 'Available';
    $location = trim($_POST['location'] ?? '');
    $assigned_fleet_id = $_POST['assigned_fleet_id'] ?? null;
    $last_inspected_at = $_POST['last_inspected_at'] ?? null;

    if (!$container_no) {
        $errors[] = "Container number is required.";
    }
    if (!$type) {
        $errors[] = "Container type is required.";
    }

    // Check for duplicate container number
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM containers WHERE container_no = ? AND id != ?");
    $checkStmt->execute([$container_no, $id]);
    if ($checkStmt->fetchColumn() > 0) {
        $errors[] = "Container number already exists.";
    }

    if (empty($errors)) {
        $updateStmt = $pdo->prepare("
            UPDATE containers 
            SET container_no = ?, type = ?, status = ?, location = ?, assigned_fleet_id = ?, last_inspected_at = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([
            $container_no,
            $type,
            $status,
            $location ?: null,
            $assigned_fleet_id ?: null,
            $last_inspected_at ?: null,
            $id
        ]);
        $success = true;

        // Refresh container data
        $stmt->execute([$id]);
        $container = $stmt->fetch();
    }
}
?>

<style>
    .form-control, .form-select, .btn {
        font-size: 0.85rem;
    }
    .btn {
        font-size: 0.8rem;
        padding: 0.3rem 0.75rem;
    }
    .form-label {
        font-size: 0.85rem;
        font-weight: 500;
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
    <h2 class="fw-bold mb-4">Edit Container</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">Container updated successfully.</div>
    <?php elseif ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="mt-3">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Container Number</label>
                <input type="text" name="container_no" class="form-control" value="<?= htmlspecialchars($container['container_no']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Container Type</label>
                <select name="type" class="form-select" required>
                    <?php
                    $types = ['20ft', '40ft', 'Reefer', 'Open Top', 'Tank'];
                    foreach ($types as $t):
                    ?>
                        <option value="<?= $t ?>" <?= $container['type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <?php
                    $statuses = ['Available', 'In Use', 'Under Maintenance', 'Damaged'];
                    foreach ($statuses as $s):
                    ?>
                        <option value="<?= $s ?>" <?= $container['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($container['location']) ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Assigned Fleet (Optional)</label>
                <select name="assigned_fleet_id" class="form-select">
                    <option value="">-- None --</option>
                    <?php foreach ($fleets as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= $container['assigned_fleet_id'] == $f['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['fleet_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Last Inspected Date</label>
                <input type="date" name="last_inspected_at" class="form-control" value="<?= $container['last_inspected_at'] ? date('Y-m-d', strtotime($container['last_inspected_at'])) : '' ?>">
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="manage_containers.php" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-danger">Update Container</button>
        </div>
    </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
