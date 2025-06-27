<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$shipmentId = $_GET['id'] ?? null;
if (!$shipmentId) {
    echo "<div class='alert alert-danger m-5'>Invalid shipment ID.</div>";
    require_once '../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT s.*, u.username, u.email, c.container_no, c.type AS container_type
    FROM shipments s
    LEFT JOIN users u ON s.user_id = u.id
    LEFT JOIN containers c ON s.container_id = c.id
    WHERE s.id = ?
");
$stmt->execute([$shipmentId]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$shipment) {
    echo "<div class='alert alert-warning m-5'>Shipment not found.</div>";
    require_once '../includes/footer.php';
    exit;
}
?>

<style>
    .info-label {
        font-weight: 600;
        color: #444;
        width: 130px;
        display: inline-block;
    }

    .section-title {
        font-weight: 700;
        color: #b6050e;
        border-bottom: 1px solid #ccc;
        margin-bottom: 10px;
        padding-bottom: 4px;
        font-size: 1rem;
    }

    .shipment-header {
        margin-bottom: 30px;
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
    }

    .shipment-tracking {
        font-size: 1.1rem;
        font-weight: bold;
        color: #e30613;
    }

    .status-text {
        font-weight: bold;
        font-size: 0.95rem;
    }

    .status-pending {
        color: #ffc107;
    }

    .status-intransit {
        color: #0d6efd;
    }

    .status-delivered {
        color: #198754;
    }

    .status-cancelled {
        color: #dc3545;
    }

    .btn-back {
        font-size: 0.85rem;
        padding: 0.4rem 1rem;
        background-color: #666;
        border: none;
        color: #fff;
    }

    .btn-back:hover {
        background-color: #444;
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
    <div class="shipment-header">
        <h2 class="fw-bold mb-2">SHIPMENT DETAILS</h2>
        <div class="shipment-tracking">Tracking #: <?= htmlspecialchars($shipment['shipment_id']) ?></div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="section-title">Sender Information</div>
            <p><span class="info-label">Name:</span> <?= htmlspecialchars($shipment['sender_name']) ?></p>
            <p><span class="info-label">Address:</span> <?= htmlspecialchars($shipment['sender_address']) ?></p>
            <p><span class="info-label">Contact:</span> <?= htmlspecialchars($shipment['sender_contact']) ?></p>
        </div>
        <div class="col-md-6">
            <div class="section-title">Recipient Information</div>
            <p><span class="info-label">Name:</span> <?= htmlspecialchars($shipment['recipient_name']) ?></p>
            <p><span class="info-label">Address:</span> <?= htmlspecialchars($shipment['recipient_address']) ?></p>
            <p><span class="info-label">Contact:</span> <?= htmlspecialchars($shipment['recipient_contact']) ?></p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="section-title">Shipment Info</div>
            <p><span class="info-label">Origin:</span> <?= htmlspecialchars($shipment['origin']) ?></p>
            <p><span class="info-label">Destination:</span> <?= htmlspecialchars($shipment['destination']) ?></p>
            <p><span class="info-label">Delivery:</span> <?= htmlspecialchars($shipment['delivery_type']) ?></p>
            <p><span class="info-label">Status:</span>
                <span class="status-text 
                    <?= 
                        $shipment['status'] === 'Delivered' ? 'status-delivered' :
                        ($shipment['status'] === 'In Transit' ? 'status-intransit' :
                        ($shipment['status'] === 'Cancelled' ? 'status-cancelled' : 'status-pending'))
                    ?>">
                    <?= htmlspecialchars($shipment['status']) ?>
                </span>
            </p>
        </div>
        <div class="col-md-6">
            <div class="section-title">Dates</div>
            <p><span class="info-label">Departure:</span> <?= date('Y-m-d', strtotime($shipment['departure_date'])) ?></p>
            <p><span class="info-label">Arrival:</span> <?= date('Y-m-d', strtotime($shipment['arrival_date'])) ?></p>
            <p><span class="info-label">Created:</span> <?= date('Y-m-d H:i', strtotime($shipment['created_at'])) ?></p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="section-title">Package Details</div>
            <p><span class="info-label">Contents:</span> <?= htmlspecialchars($shipment['package_contents']) ?></p>
            <p><span class="info-label">Weight:</span> <?= htmlspecialchars($shipment['package_weight']) ?> Kg</p>
            <p><span class="info-label">Value:</span> $<?= htmlspecialchars($shipment['package_value']) ?></p>
        </div>
        <div class="col-md-6">
            <div class="section-title">Container</div>
            <p><span class="info-label">No:</span> <?= htmlspecialchars($shipment['container_no'] ?? '-') ?></p>
            <p><span class="info-label">Type:</span> <?= htmlspecialchars($shipment['container_type'] ?? '-') ?></p>
        </div>
    </div>

    <div class="info-group">
        <div class="section-title">User Info</div>
        <p><span class="info-label">Booked By:</span> <?= htmlspecialchars($shipment['username'] ?? '-') ?></p>
        <p><span class="info-label">Email:</span> <?= htmlspecialchars($shipment['email'] ?? '-') ?></p>
    </div>

    <div class="mt-4">
        <a href="view_shipment.php" class="btn btn-back">Back to Shipments</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
