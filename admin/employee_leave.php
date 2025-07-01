<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$userId = $_SESSION['user_id'] ?? 0;
$errors = [];
$success = false;

// Handle Leave Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = $_POST['leave_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date   = $_POST['end_date'] ?? '';
    $reason     = trim($_POST['reason'] ?? '');

    if (!$leave_type || !in_array($leave_type, ['Sick', 'Casual', 'Annual', 'Emergency'])) {
        $errors[] = "Invalid leave type.";
    }

    if (!$start_date || !$end_date) {
        $errors[] = "Start and End dates are required.";
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $errors[] = "End date must be after start date.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO employee_leaves (employee_id, leave_type, start_date, end_date, reason, status, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
        if ($stmt->execute([$userId, $leave_type, $start_date, $end_date, $reason])) {
            $success = true;
        } else {
            $errors[] = "Failed to submit leave.";
        }
    }
}

// Fetch Past Leaves
$stmt = $pdo->prepare("SELECT * FROM employee_leaves WHERE employee_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$leaves = $stmt->fetchAll();
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
    /* Make content text smaller */
    .content-small {
        font-size: 0.85rem;
    }
    .content-small table,
    .content-small .form-control,
    .content-small .form-select,
    .content-small .btn {
        font-size: 0.95rem;
    }
</style>

<div class="content-small container my-5">
    <h2 class="fw-bold mb-4">LEAVE SHEET</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">Leave request submitted successfully.</div>
    <?php elseif (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="mb-5">
        <h5 class="mb-3">Request New Leave</h5>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Leave Type</label>
                <select name="leave_type" class="form-select" required>
                    <option value="">Select</option>
                    <option value="Sick">Sick Leave</option>
                    <option value="Casual">Casual Leave</option>
                    <option value="Annual">Annual Leave</option>
                    <option value="Emergency">Emergency Leave</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Reason</label>
            <textarea name="reason" class="form-control" rows="3" placeholder="Optional"></textarea>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="dashboard.php" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-danger">Submit Leave</button>
        </div>
    </form>
    <div class="card shadow-sm">
        <div class="card-header bg-light fw-bold">Your Leave History</div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Leave Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Reason / View</th>
                        <th>Requested On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($leaves) === 0): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No leave requests found.</td>
                        </tr>
                    <?php else: foreach ($leaves as $leave): ?>
                        <tr>
                            <td><?= htmlspecialchars($leave['leave_type']) ?></td>
                            <td><?= htmlspecialchars($leave['start_date']) ?></td>
                            <td><?= htmlspecialchars($leave['end_date']) ?></td>
                            <td><?= (new DateTime($leave['start_date']))->diff(new DateTime($leave['end_date']))->days + 1 ?></td>
                            <td>
                                <span class="badge 
                                    <?= $leave['status'] === 'Approved' ? 'bg-success' : ($leave['status'] === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                    <?= htmlspecialchars($leave['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($leave['status'] === 'Rejected'): ?>
                                    <a href="view_leave_reason.php?id=<?= $leave['id'] ?>" target="_blank" class="btn btn-secondary">View Reason</a>
                                <?php else: ?>
                                    <?= htmlspecialchars($leave['reason']) ?: '-' ?>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y', strtotime($leave['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
