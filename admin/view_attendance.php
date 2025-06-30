<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$role = $_SESSION['role'] ?? 'user';
if ($role !== 'admin') {
    echo "<div class='alert alert-danger m-5'>Access denied.</div>";
    require_once '../includes/footer.php';
    exit;
}

$filterUser = $_GET['user_id'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterDateFrom = $_GET['date_from'] ?? '';
$filterDateTo = $_GET['date_to'] ?? '';

$where = [];
$params = [];

if ($filterUser !== '') {
    $where[] = 'a.user_id = ?';
    $params[] = $filterUser;
}
if ($filterStatus !== '') {
    $where[] = 'a.status = ?';
    $params[] = $filterStatus;
}
if ($filterDateFrom !== '') {
    $where[] = 'a.date >= ?';
    $params[] = $filterDateFrom;
}
if ($filterDateTo !== '') {
    $where[] = 'a.date <= ?';
    $params[] = $filterDateTo;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT a.*, u.username
        FROM attendances a
        LEFT JOIN users u ON a.user_id = u.id
        $whereSql
        ORDER BY a.date DESC, a.check_in_time DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$attendances = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch users with roles admin, manager, employer only for filter dropdown
$usersStmt = $pdo->prepare("SELECT id, username FROM users WHERE is_active = 1 AND role IN ('admin','manager','employer') ORDER BY username");
$usersStmt->execute();
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">All Attendance Records</h2>

    <form method="GET" class="row g-2 mb-4 align-items-end">
        <div class="col-md-3">
            <label for="user_id" class="form-label">User</label>
            <select id="user_id" name="user_id" class="form-select">
                <option value="">All Users</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= $filterUser == $user['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label for="date_from" class="form-label">Date From</label>
            <input type="date" id="date_from" name="date_from" class="form-control" value="<?= htmlspecialchars($filterDateFrom) ?>">
        </div>

        <div class="col-md-2">
            <label for="date_to" class="form-label">Date To</label>
            <input type="date" id="date_to" name="date_to" class="form-control" value="<?= htmlspecialchars($filterDateTo) ?>">
        </div>

        <div class="col-md-2">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="">All Status</option>
                <?php $statuses = ['Present', 'Absent', 'Late', 'Leave', 'Remote']; ?>
                <?php foreach ($statuses as $statusOption): ?>
                    <option value="<?= $statusOption ?>" <?= $filterStatus === $statusOption ? 'selected' : '' ?>>
                        <?= $statusOption ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-1">
            <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
        </div>

        <div class="col-md-2 text-end">
            <a href="export_attendance_excel.php?<?= http_build_query($_GET) ?>" class="btn btn-success">Export to Excel</a>
        </div>
    </form>

    <div class="table-responsive card shadow-sm">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Date</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Location</th>
                    <th>Manual Entry</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$attendances): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No attendance records found.</td></tr>
                <?php else: foreach ($attendances as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['username']) ?></td>
                        <td><?= htmlspecialchars($a['date']) ?></td>
                        <td><?= htmlspecialchars($a['check_in_time'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($a['check_out_time'] ?? '-') ?></td>
                        <td>
                            <span class="badge
                                <?= $a['status'] === 'Present' ? 'bg-success' :
                                   ($a['status'] === 'Absent' ? 'bg-danger' :
                                   ($a['status'] === 'Late' ? 'bg-warning text-dark' :
                                   ($a['status'] === 'Leave' ? 'bg-info text-dark' : 'bg-secondary'))) ?>">
                                <?= htmlspecialchars($a['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($a['remarks'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($a['location'] ?? '-') ?></td>
                        <td><?= $a['is_manual_entry'] ? 'Yes' : 'No' ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
