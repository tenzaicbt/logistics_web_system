<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$shipmentId = $_GET['id'] ?? null;
$errors = [];
$success = '';

if (!$shipmentId || !is_numeric($shipmentId)) {
    echo "<div class='alert alert-danger m-5'>Invalid shipment ID.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

// Fetch shipment
$stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
$stmt->execute([$shipmentId]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$shipment) {
    echo "<div class='alert alert-danger m-5'>Shipment not found.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

// Fetch containers
$containers = $pdo->query("SELECT id, container_no, type FROM containers")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $origin = trim($_POST['origin']);
    $destination = trim($_POST['destination']);
    $departure = $_POST['departure_date'] ?? null;
    $arrival = $_POST['arrival_date'] ?? null;
    $containerId = $_POST['container_id'] ?? null;

    $senderName = trim($_POST['sender_name']);
    $senderAddress = trim($_POST['sender_address']);
    $senderContact = trim($_POST['sender_contact']);
    $recipientName = trim($_POST['recipient_name']);
    $recipientAddress = trim($_POST['recipient_address']);
    $recipientContact = trim($_POST['recipient_contact']);
    $packageContents = trim($_POST['package_contents']);
    $packageWeight = trim($_POST['package_weight']);
    $packageValue = trim($_POST['package_value']);
    $deliveryType = trim($_POST['delivery_type']);

    if (!$origin || !$destination || !$departure || !$arrival || !$containerId || !$senderName || !$recipientName || !$deliveryType) {
        $errors[] = "Please fill in all required fields.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE shipments SET
            container_id = ?, origin = ?, destination = ?, departure_date = ?, arrival_date = ?,
            sender_name = ?, sender_address = ?, sender_contact = ?,
            recipient_name = ?, recipient_address = ?, recipient_contact = ?,
            package_contents = ?, package_weight = ?, package_value = ?, delivery_type = ?
            WHERE id = ?");

        $stmt->execute([
            $containerId,
            $origin,
            $destination,
            $departure,
            $arrival,
            $senderName,
            $senderAddress,
            $senderContact,
            $recipientName,
            $recipientAddress,
            $recipientContact,
            $packageContents,
            $packageWeight,
            $packageValue,
            $deliveryType,
            $shipmentId
        ]);

        $success = "Shipment updated successfully.";
        // Refresh shipment data
        $stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
        $stmt->execute([$shipmentId]);
        $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<style>
    .form-label,
    .form-control,
    .form-select,
    .btn {
        font-size: 0.85rem;
    }

    h4 {
        font-size: 1.1rem;
    }

    h6 {
        font-size: 0.8rem;
        font-weight: 600;
        color: #555;
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
        font-size: 0.90rem;
    }
</style>

<div class="container my-5">
    <div class="mb-4 fw-bold">
        <h2 class="fw-bold">Edit Shipments</h2>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <h6 class="mb-2">Sender’s Information</h6>
        <div class="row g-3 mb-3">
            <div class="col-md-4"><input type="text" name="sender_name" class="form-control" value="<?= htmlspecialchars($shipment['sender_name']) ?>" required></div>
            <div class="col-md-5"><input type="text" name="sender_address" class="form-control" value="<?= htmlspecialchars($shipment['sender_address']) ?>"></div>
            <div class="col-md-3"><input type="text" name="sender_contact" class="form-control" value="<?= htmlspecialchars($shipment['sender_contact']) ?>"></div>
        </div>

        <h6 class="mb-2">Recipient Information</h6>
        <div class="row g-3 mb-3">
            <div class="col-md-4"><input type="text" name="recipient_name" class="form-control" value="<?= htmlspecialchars($shipment['recipient_name']) ?>" required></div>
            <div class="col-md-5"><input type="text" name="recipient_address" class="form-control" value="<?= htmlspecialchars($shipment['recipient_address']) ?>"></div>
            <div class="col-md-3"><input type="text" name="recipient_contact" class="form-control" value="<?= htmlspecialchars($shipment['recipient_contact']) ?>"></div>
        </div>

        <h6 class="mb-2">Package Description</h6>
        <div class="row g-3 mb-3">
            <div class="col-md-4"><input type="text" name="package_contents" class="form-control" value="<?= htmlspecialchars($shipment['package_contents']) ?>"></div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" name="package_weight" class="form-control" value="<?= htmlspecialchars($shipment['package_weight']) ?>">
                    <span class="input-group-text">Kg</span>
                </div>
            </div>
            <div class="col-md-4"><input type="text" name="package_value" class="form-control" value="<?= htmlspecialchars($shipment['package_value']) ?>"></div>
        </div>

        <h6 class="mb-2">Shipping Info</h6>
        <div class="row g-3 mb-3">
            <div class="col-md-4"><input type="text" name="origin" class="form-control" value="<?= htmlspecialchars($shipment['origin']) ?>" required></div>
            <div class="col-md-4"><input type="text" name="destination" class="form-control" value="<?= htmlspecialchars($shipment['destination']) ?>" required></div>
            <div class="col-md-4">
                <select name="delivery_type" class="form-select" required>
                    <option value="">-- Select Delivery Type --</option>
                    <?php foreach (['Standard', 'Express', 'Overnight'] as $type): ?>
                        <option value="<?= $type ?>" <?= ($shipment['delivery_type'] === $type ? 'selected' : '') ?>><?= $type ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Departure Date</label>
                <input type="date" name="departure_date" class="form-control" value="<?= htmlspecialchars($shipment['departure_date']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Arrival Date</label>
                <input type="date" name="arrival_date" class="form-control" value="<?= htmlspecialchars($shipment['arrival_date']) ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Container</label>
            <select name="container_id" class="form-select" required>
                <option value="">-- Select Container --</option>
                <?php foreach ($containers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($shipment['container_id'] == $c['id'] ? 'selected' : '') ?>>
                        <?= htmlspecialchars($c['container_no']) ?> (<?= $c['type'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="manage_shipments.php" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-danger">Update Shipment</button>
        </div>
    </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
