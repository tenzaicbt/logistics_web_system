<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$role = $_SESSION['role'] ?? '';

if ($role !== 'admin') {
    echo "<div class='alert alert-danger m-5'>Access denied.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

// Handle status update
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_status'], $_POST['shipment_id'], $_POST['new_status'])
) {
    $shipmentId = (int) $_POST['shipment_id'];
    $newStatus = $_POST['new_status'];
    $allowedStatuses = ['Pending', 'In Transit', 'Delivered', 'Cancelled'];

    if (in_array($newStatus, $allowedStatuses)) {
        $stmt = $pdo->prepare("UPDATE shipments SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $shipmentId]);

        header("Location: manage_shipments.php");
        exit;
    }
}

// Filters
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

$where = [];
$params = [];

if ($search) {
    $where[] = "(s.shipment_id LIKE ? OR s.origin LIKE ? OR s.destination LIKE ?)";
    $params = array_fill(0, 3, "%$search%");
}
if ($status) {
    $where[] = "s.status = ?";
    $params[] = $status;
}

$sql = "SELECT s.*, u.username, c.container_no 
        FROM shipments s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN containers c ON s.container_id = c.id";

if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .btn,
    .form-control,
    .form-select {
        font-size: 0.85rem;
    }

    h2 {
        font-size: 1.5rem;
        font-weight: bold;
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">Manage Shipments</h2>
    </div>

    <form method="get" action="manage_shipments.php" role="search" aria-label="Filter shipments" style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 20px;">
        <input
            type="search"
            name="search"
            placeholder="Search by ID, origin, destination"
            value="<?= htmlspecialchars($search) ?>"
            style="border: 1px solid #b6050e; border-radius: 4px; padding: 6px 12px; font-size: 0.9rem; width: 240px; outline: none;"
            onfocus="this.style.borderColor='#8b0000';"
            onblur="this.style.borderColor='#b6050e';" />

        <select name="status" style="border: 1px solid #b6050e; border-radius: 4px; padding: 6px 10px; font-size: 0.9rem; width: 180px;">
            <option value="">All Statuses</option>
            <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="In Transit" <?= $status === 'In Transit' ? 'selected' : '' ?>>In Transit</option>
            <option value="Delivered" <?= $status === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
            <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>

        <button type="submit" class="btn btn-outline-secondary" style="padding: 6px 14px;">Filter</button>
        <?php if ($role === 'admin'): ?>
            <a href="create_shipment.php" class="btn btn-danger" style="margin-left: auto; padding: 6px 14px;">Create Shipment</a>
        <?php endif; ?>
    </form>



    <div class="table-responsive card shadow-sm">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Shipment ID</th>
                    <th>User</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Status</th>
                    <th>Container</th>
                    <th>Delivery</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($shipments): ?>
                    <?php foreach ($shipments as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['shipment_id']) ?></td>
                            <td><?= htmlspecialchars($s['username']) ?></td>
                            <td><?= htmlspecialchars($s['origin']) ?></td>
                            <td><?= htmlspecialchars($s['destination']) ?></td>
                            <td>
                                <form method="POST" class="m-0">
                                    <input type="hidden" name="shipment_id" value="<?= $s['id'] ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <?php
                                        $statuses = ['Pending', 'In Transit', 'Delivered', 'Cancelled'];
                                        foreach ($statuses as $option): ?>
                                            <option value="<?= $option ?>" <?= ($s['status'] === $option ? 'selected' : '') ?>>
                                                <?= $option ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td><?= htmlspecialchars($s['container_no']) ?></td>
                            <td><?= htmlspecialchars($s['delivery_type']) ?></td>
                            <td><?= date('Y-m-d', strtotime($s['created_at'])) ?></td>
                            <td class="text-nowrap">
                                <a href="view_shipment_details.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary">Details</a>
                                <a href="shipment_edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger">Edit</a>
                                <a href="shipment_delete.php?id=<?= $s['id'] ?>" onclick="return confirm('Delete this shipment?');" class="btn btn-sm btn-outline-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted">No shipments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>