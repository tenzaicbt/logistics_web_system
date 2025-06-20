<?php
require_once '../includes/auth_check.php';
requireRole([ROLE_ADMIN]);

require_once '../config/db.php';
require_once '../includes/functions.php';

$errors = [];
$success = '';

// Helper to generate unique tracking number
function generateTrackingNumber($pdo) {
    do {
        $code = 'NP' . strtoupper(bin2hex(random_bytes(5)));
        $stmt = $pdo->prepare("SELECT id FROM shipments WHERE tracking_number = ?");
        $stmt->execute([$code]);
    } while ($stmt->fetch());
    return $code;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $schedule_date = $_POST['schedule_date'] ?? '';
    $cargo_details = trim($_POST['cargo_details'] ?? '');

    if ($user_id <= 0) $errors[] = "Please select a user.";
    if ($origin === '') $errors[] = "Origin is required.";
    if ($destination === '') $errors[] = "Destination is required.";
    if ($schedule_date === '') $errors[] = "Schedule date is required.";

    if (!$errors) {
        $tracking_number = generateTrackingNumber($pdo);
        $stmt = $pdo->prepare("INSERT INTO shipments (user_id, origin, destination, schedule_date, delivery_status, tracking_number, cargo_details) VALUES (?, ?, ?, ?, 'pending', ?, ?)");
        $stmt->execute([$user_id, $origin, $destination, $schedule_date, $tracking_number, $cargo_details]);

        $success = "Shipment created successfully. Tracking Number: $tracking_number";
    }
}

// Fetch users for dropdown
$users = $pdo->query("SELECT id, name FROM users WHERE status = 'active' AND role = 'user' ORDER BY name")->fetchAll();

include '../includes/header.php';
?>

<h2>Create New Shipment</h2>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
    </div>
<?php endif; ?>

<form method="post" class="mb-4">
    <div class="mb-3">
        <label for="user_id" class="form-label">User</label>
        <select id="user_id" name="user_id" class="form-select" required>
            <option value="">Select User</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>" <?= (isset($_POST['user_id']) && $_POST['user_id'] == $u['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="origin" class="form-label">Origin</label>
        <input type="text" id="origin" name="origin" class="form-control" value="<?= htmlspecialchars($_POST['origin'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label for="destination" class="form-label">Destination</label>
        <input type="text" id="destination" name="destination" class="form-control" value="<?= htmlspecialchars($_POST['destination'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label for="schedule_date" class="form-label">Schedule Date</label>
        <input type="date" id="schedule_date" name="schedule_date" class="form-control" value="<?= htmlspecialchars($_POST['schedule_date'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label for="cargo_details" class="form-label">Cargo Details</label>
        <textarea id="cargo_details" name="cargo_details" class="form-control"><?= htmlspecialchars($_POST['cargo_details'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Create Shipment</button>
</form>

<?php include '../includes/footer.php'; ?>
