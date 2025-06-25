<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$leaveId = $_GET['leave_id'] ?? null;
$userId = $_SESSION['user_id'] ?? 0;

if (!$leaveId || !is_numeric($leaveId)) {
    echo '<div class="container my-5"><div class="alert alert-danger">Invalid leave ID.</div></div>';
    require_once '../includes/admin_footer.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT el.leave_type, el.start_date, el.end_date, el.admin_reason, el.created_at,
           el.rejected_at, el.rejected_by, u.username AS rejected_by_name
    FROM employee_leaves el
    LEFT JOIN users u ON el.rejected_by = u.id
    WHERE el.id = ? AND el.status = 'Rejected'
");
$stmt->execute([$leaveId]);
$leave = $stmt->fetch();

if (!$leave) {
    echo '<div class="container my-5"><div class="alert alert-danger">Rejected leave not found or access denied.</div></div>';
    require_once '../includes/admin_footer.php';
    exit;
}
?>

<style>
    .label {
        font-weight: 600;
        width: 160px;
        display: inline-block;
    }
    .content-small {
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

<div class="container my-5 content-small">
    <h2 class="fw-bold mb-4">Leave Rejection Details</h2>

    <p><span class="label">Leave Type:</span> <?= htmlspecialchars($leave['leave_type']) ?></p>
    <p><span class="label">From:</span> <?= htmlspecialchars($leave['start_date']) ?></p>
    <p><span class="label">To:</span> <?= htmlspecialchars($leave['end_date']) ?></p>
    <p><span class="label">Requested On:</span> <?= date('d M Y h:i A', strtotime($leave['created_at'])) ?></p>
    <p><span class="label">Rejected By:</span> <?= $leave['rejected_by_name'] ? htmlspecialchars($leave['rejected_by_name']) : 'N/A' ?></p>
    <p><span class="label">Rejected On:</span> <?= $leave['rejected_at'] ? date('d M Y h:i A', strtotime($leave['rejected_at'])) : 'N/A' ?></p>

    <hr>
    <h5>Rejection Reason</h5>
    <p><?= nl2br(htmlspecialchars($leave['admin_reason'])) ?></p>

    <a href="manage_leaves.php" class="btn btn-secondary">Back to Leave Management</a>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
