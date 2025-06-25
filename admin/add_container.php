<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$errors = [];
$success = false;

// Initialize variables to avoid undefined warnings
$container_no = '';
$type = '';
$status = 'Available';
$location = '';
$assigned_fleet_id = '';
$last_inspected_at = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $container_no = trim($_POST['container_no'] ?? '');
    $type = $_POST['type'] ?? '';
    $status = $_POST['status'] ?? 'Available';
    $location = trim($_POST['location'] ?? '');
    $assigned_fleet_id = $_POST['assigned_fleet_id'] ?? null;
    $last_inspected_at = $_POST['last_inspected_at'] ?? null;

    // Validation
    if (!$container_no) {
        $errors[] = "Container number is required.";
    }
    if (!$type) {
        $errors[] = "Container type is required.";
    }

    // Check for duplicate container_no
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM containers WHERE container_no = ?");
        $check->execute([$container_no]);
        if ($check->fetch()) {
            $errors[] = "This container number already exists.";
        }
    }

    // If no errors, insert into DB
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO containers (container_no, type, status, location, assigned_fleet_id, last_inspected_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $container_no,
            $type,
            $status,
            $location ?: null,
            $assigned_fleet_id ?: null,
            $last_inspected_at ?: null
        ]);

        $success = true;

        // Reset form values
        $container_no = $type = $location = '';
        $status = 'Available';
        $assigned_fleet_id = $last_inspected_at = '';
    }
}

// Load fleets for dropdown
$fleets = $pdo->query("SELECT id, fleet_name FROM fleets WHERE status='Active' ORDER BY fleet_name")->fetchAll();
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
    <h2 class="fw-bold mb-4">Add New Container</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">Container added successfully.</div>
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
                <input type="text" name="container_no" class="form-control" required value="<?= htmlspecialchars($container_no) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Container Type</label>
                <select name="type" class="form-select" required>
                    <option value="">-- Select Type --</option>
                    <option value="20ft" <?= $type === '20ft' ? 'selected' : '' ?>>20ft</option>
                    <option value="40ft" <?= $type === '40ft' ? 'selected' : '' ?>>40ft</option>
                    <option value="Reefer" <?= $type === 'Reefer' ? 'selected' : '' ?>>Reefer</option>
                    <option value="Open Top" <?= $type === 'Open Top' ? 'selected' : '' ?>>Open Top</option>
                    <option value="Tank" <?= $type === 'Tank' ? 'selected' : '' ?>>Tank</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Available" <?= $status === 'Available' ? 'selected' : '' ?>>Available</option>
                    <option value="In Use" <?= $status === 'In Use' ? 'selected' : '' ?>>In Use</option>
                    <option value="Under Maintenance" <?= $status === 'Under Maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
                    <option value="Damaged" <?= $status === 'Damaged' ? 'selected' : '' ?>>Damaged</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($location) ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Assigned Fleet (Optional)</label>
                <select name="assigned_fleet_id" class="form-select">
                    <option value="">-- None --</option>
                    <?php foreach ($fleets as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= $assigned_fleet_id == $f['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['fleet_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Last Inspected Date</label>
                <input type="date" name="last_inspected_at" class="form-control" value="<?= htmlspecialchars($last_inspected_at) ?>">
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="manage_containers.php" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-danger">Add Container</button>
        </div>
    </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
