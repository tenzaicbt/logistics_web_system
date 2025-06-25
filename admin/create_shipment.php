<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';
$errors = [];
$success = false;

if (!in_array($role, ['admin', 'manager', 'employer'])) {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? null;
    $container_id = $_POST['container_id'] ?? null;
    $fleet_id = $_POST['fleet_id'] ?? null;
    $origin = trim($_POST['origin']);
    $destination = trim($_POST['destination']);
    $status = $_POST['status'] ?? 'Pending';
    $departure_date = $_POST['departure_date'] ?? null;
    $arrival_date = $_POST['arrival_date'] ?? null;
    $notes = trim($_POST['notes'] ?? '');

    if (!$origin || !$destination) {
        $errors[] = "Origin and destination are required.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO shipments (shipment_id, user_id, booking_id, container_id, origin, destination, status, departure_date, arrival_date, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $shipmentId = uniqid("SHP-", true);
        $stmt->execute([
            $shipmentId,
            $userId,
            $booking_id ?: null,
            $container_id ?: null,
            $origin,
            $destination,
            $status,
            $departure_date ?: null,
            $arrival_date ?: null
        ]);
        $success = true;
    }
}

// Dropdown data
$bookings = $pdo->query("SELECT id, booking_ref FROM bookings ORDER BY created_at DESC")->fetchAll();
$containers = $pdo->query("SELECT id, container_no FROM containers WHERE status = 'Available'")->fetchAll();
$fleets = $pdo->query("SELECT id, fleet_name FROM fleets WHERE status = 'Active' ORDER BY fleet_name ASC")->fetchAll();
?>

<style>
    .form-label, .form-control, .form-select, .btn {
        font-size: 0.85rem;
    }
    .btn {
        padding: 0.35rem 0.9rem;
    }
    .btn-danger {
        background-color: #e30613;
        border: none;
    }
    .btn-danger:hover {
        background-color: #b6050e;
    }
</style>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Create New Shipment</h2>

    <?php if ($success): ?>
        <div class="alert alert-success small">Shipment created successfully.</div>
    <?php elseif ($errors): ?>
        <div class="alert alert-danger small">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="small">
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Booking Reference</label>
                <select name="booking_id" class="form-select">
                    <option value="">-- Select --</option>
                    <?php foreach ($bookings as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['booking_ref']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Container No</label>
                <select name="container_id" class="form-select">
                    <option value="">-- Select --</option>
                    <?php foreach ($containers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['container_no']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Fleet</label>
                <select name="fleet_id" class="form-select">
                    <option value="">-- Select --</option>
                    <?php foreach ($fleets as $f): ?>
                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['fleet_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Origin</label>
                <input type="text" name="origin" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Destination</label>
                <input type="text" name="destination" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Departure Date</label>
                <input type="date" name="departure_date" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Arrival Date</label>
                <input type="date" name="arrival_date" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Pending">Pending</option>
                    <option value="In Transit">In Transit</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Notes (Optional)</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-danger">Create Shipment</button>
        </div>
    </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
