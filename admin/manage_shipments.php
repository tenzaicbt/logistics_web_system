<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$currentRole = $_SESSION['role'] ?? 'user';

// Optional: restrict access
if (!in_array($currentRole, ['admin', 'manager', 'employer'])) {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

// Fetch all shipments with related user & booking info
$stmt = $pdo->query("
    SELECT s.*, u.username, b.booking_ref, c.container_no
    FROM shipments s
    LEFT JOIN users u ON s.user_id = u.id
    LEFT JOIN bookings b ON s.booking_id = b.id
    LEFT JOIN containers c ON s.container_id = c.id
    ORDER BY s.created_at DESC
");
$shipments = $stmt->fetchAll();
?>

<style>
    .btn {
        font-size: 0.8rem;
        padding: 0.25rem 0.6rem;
    }

    .badge {
        font-size: 0.75rem;
    }

    .table th, .table td {
        font-size: 0.85rem;
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
        <h2 class="fw-bold">Manage Shipments</h2>
        <a href="create_shipment.php" class="btn btn-danger btn-sm">Create New Shipment</a>
    </div>

    <?php if (!$shipments): ?>
        <div class="alert alert-info">No shipments found.</div>
    <?php else: ?>
        <div class="table-responsive shadow-sm">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Shipment ID</th>
                        <th>User</th>
                        <th>Booking</th>
                        <th>Container</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Status</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shipments as $s): ?>
                        <tr>
                            <td><?= $s['id'] ?></td>
                            <td><?= htmlspecialchars($s['shipment_id']) ?></td>
                            <td><?= htmlspecialchars($s['username']) ?></td>
                            <td><?= $s['booking_ref'] ?? '-' ?></td>
                            <td><?= $s['container_no'] ?? '-' ?></td>
                            <td><?= htmlspecialchars($s['origin']) ?></td>
                            <td><?= htmlspecialchars($s['destination']) ?></td>
                            <td>
                                <span class="badge 
                                    <?= match ($s['status']) {
                                        'Delivered' => 'bg-success',
                                        'In Transit' => 'bg-warning text-dark',
                                        'Cancelled' => 'bg-danger',
                                        default => 'bg-secondary'
                                    } ?>">
                                    <?= htmlspecialchars($s['status']) ?>
                                </span>
                            </td>
                            <td><?= $s['departure_date'] ?? '-' ?></td>
                            <td><?= $s['arrival_date'] ?? '-' ?></td>
                            <td><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                            <td>
                                <a href="view_shipment.php?id=<?= $s['id'] ?>" class="btn btn-secondary btn-sm">View</a>
                                <a href="edit_shipment.php?id=<?= $s['id'] ?>" class="btn btn-outline-dark btn-sm">Edit</a>
                                <a href="delete_shipment.php?id=<?= $s['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
