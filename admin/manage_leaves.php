<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$currentUserId = $_SESSION['user_id'];
$currentRole = $_SESSION['role'] ?? 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['leave_id'])) {
    $action = $_POST['action'];
    $leaveId = (int)$_POST['leave_id'];

    $stmt = $pdo->prepare("SELECT * FROM employee_leaves WHERE id = ?");
    $stmt->execute([$leaveId]);
    $leave = $stmt->fetch();

    if (!$leave) {
        echo "<div class='alert alert-danger'>Leave not found.</div>";
        require_once '../includes/admin_footer.php';
        exit;
    }

    if (!in_array($currentRole, ['admin', 'manager'])) {
        echo "<div class='alert alert-danger'>Permission denied.</div>";
        require_once '../includes/admin_footer.php';
        exit;
    }

    if ($action === 'approve' && $leave['status'] === 'Pending') {
        $update = $pdo->prepare("UPDATE employee_leaves SET status = 'Approved', admin_reason = NULL, updated_at = NOW() WHERE id = ?");
        $update->execute([$leaveId]);
        header('Location: manage_leaves.php');
        exit;
    }

    if ($action === 'cancel' && in_array($leave['status'], ['Pending', 'Approved'])) {
        $update = $pdo->prepare("UPDATE employee_leaves SET status = 'Cancelled', updated_at = NOW() WHERE id = ?");
        $update->execute([$leaveId]);
        header('Location: manage_leaves.php');
        exit;
    }

    if ($action === 'reject' && $leave['status'] === 'Pending') {
        $adminReason = trim($_POST['admin_reason'] ?? '');
        if (!$adminReason) {
            echo "<div class='alert alert-danger'>Rejection reason required.</div>";
        } else {
            $update = $pdo->prepare("UPDATE employee_leaves SET status = 'Rejected', admin_reason = ?, updated_at = NOW() WHERE id = ?");
            $update->execute([$adminReason, $leaveId]);
            header("Location: reject_leave_reason.php?leave_id=$leaveId");
            exit;
        }
    }
}

$leavesStmt = $pdo->prepare("
    SELECT el.*, u.username 
    FROM employee_leaves el 
    JOIN users u ON el.employee_id = u.id
    ORDER BY el.created_at DESC
");
$leavesStmt->execute();
$leaves = $leavesStmt->fetchAll();

// LOGS
function logAction($pdo, $userId, $action)
{
    $stmt = $pdo->prepare("INSERT INTO logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $action,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
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

    .btn-outline-primary {
        color: #0d6efd;
        border: 1px solid #0d6efd;
        background-color: transparent;
    }

    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: white;
    }

    .btn-outline-success {
        color: #28a745;
        border: 1px solid #28a745;
        background-color: transparent;
    }

    .btn-outline-success:hover {
        background-color: #28a745;
        color: white;
    }

    .btn-outline-danger {
        color: #dc3545;
        border: 1px solid #dc3545;
        background-color: transparent;
    }

    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }

    .btn-outline-secondary {
        color: #6c757d;
        border: 1px solid #6c757d;
        background-color: transparent;
    }

    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: white;
    }

    .btn-simple {
        font-size: 0.8rem;
        padding: 4px 10px;
        border-radius: 4px;
        color:rgb(255, 0, 0);
        background: transparent;
        border: 1px rgb(253, 0, 0);
        text-decoration: none;
        transition: all 0.2s ease-in-out;
    }
/* 
    .btn-simple:hover {
        background-color:rgb(201, 18, 18);
        color: white;
    } */

    .table td,
    .table th {
        vertical-align: middle;
    }
</style>

<div class="container my-5 small-text">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Manage Leave Requests</h2>
        <a href="employee_leave.php" class="btn btn-danger btn-sm">Add Leave Request</a>
    </div>

    <?php if (!$leaves): ?>
        <div class="alert alert-info">No leave requests found.</div>
    <?php else: ?>
        <div class="table-responsive shadow-sm">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Rejection</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaves as $leave): ?>
                        <tr>
                            <td><?= htmlspecialchars($leave['username']) ?></td>
                            <td><?= htmlspecialchars($leave['leave_type']) ?></td>
                            <td><?= htmlspecialchars($leave['start_date']) ?></td>
                            <td><?= htmlspecialchars($leave['end_date']) ?></td>
                            <td>
                                <a href="view_leave_reason.php?id=<?= (int)$leave['id'] ?>" class="btn-simple">View</a>
                            </td>
                            <td>
                                <?php
                                $status = $leave['status'];
                                $badgeClass = match ($status) {
                                    'Approved' => 'bg-success',
                                    'Rejected' => 'bg-danger',
                                    'Cancelled' => 'bg-secondary',
                                    default => 'bg-warning text-dark',
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
                            </td>
                            <td>
                                <?php if ($status === 'Rejected'): ?>
                                    <a href="/northport/admin/reject_leave_reason.php?leave_id=<?= (int)$leave['id'] ?>" class="btn-simple">View</a>
                                <?php else: ?>
                                    <em>-</em>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($leave['created_at'])) ?></td>
                            <td>
                                <?php if ($status === 'Pending'): ?>
                                    <form method="post" style="display:inline-block;">
                                        <input type="hidden" name="leave_id" value="<?= (int)$leave['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-outline-success btn-sm">Approve</button>
                                    </form>

                                    <form method="post" style="display:inline-block;" onsubmit="return confirmReject(this);">
                                        <input type="hidden" name="leave_id" value="<?= (int)$leave['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="admin_reason" value="">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
                                    </form>

                                    <form method="post" style="display:inline-block;">
                                        <input type="hidden" name="leave_id" value="<?= (int)$leave['id'] ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    <em>No actions</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    function confirmReject(form) {
        const reason = prompt("Enter reason for rejection:");
        if (!reason) {
            alert("Rejection reason is required.");
            return false;
        }
        form.admin_reason.value = reason;
        return true;
    }
</script>

<?php require_once '../includes/admin_footer.php'; ?>