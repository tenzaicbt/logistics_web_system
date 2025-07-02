<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$role = $_SESSION['role'] ?? 'user';
$userId = $_SESSION['user_id'] ?? null;

// Only admin, manager and user allowed
if (!in_array($role, ['admin', 'manager', 'user'])) {
    echo "<div class='alert alert-danger m-5'>Access denied.</div>";
    require_once '../includes/footer.php';
    exit;
}

// Filters
$filterUser = $_GET['user_id'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterDateFrom = $_GET['date_from'] ?? '';
$filterDateTo = $_GET['date_to'] ?? '';

// Pagination
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

// Admin and manager can filter by any user, regular users see only their own
if ($role === 'admin' || $role === 'manager') {
    if ($filterUser) {
        $where[] = 'a.user_id = ?';
        $params[] = $filterUser;
    }
} else {
    $where[] = 'a.user_id = ?';
    $params[] = $userId;
}

if ($filterStatus) {
    $where[] = 'a.status = ?';
    $params[] = $filterStatus;
}

if ($filterDateFrom) {
    $where[] = 'a.date >= ?';
    $params[] = $filterDateFrom;
}

if ($filterDateTo) {
    $where[] = 'a.date <= ?';
    $params[] = $filterDateTo;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total records count for pagination
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendances a $whereSql");
$stmt->execute($params);
$totalRecords = $stmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Fetch attendance records with user info
$sql = "SELECT a.*, u.username
        FROM attendances a
        LEFT JOIN users u ON a.user_id = u.id
        $whereSql
        ORDER BY a.date DESC, a.check_in_time DESC
        LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$attendances = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch users for filter dropdown (only for admin/manager)
$users = [];
if ($role === 'admin' || $role === 'manager') {
    $users = $pdo->query("SELECT id, username FROM users WHERE is_active = 1 ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
}

?>

<style>
    .form-control, .form-select { font-size: 0.85rem; }
    h2 { font-size: 1.5rem; font-weight: bold; }
    .btn { padding: 0.25rem 0.75rem; font-size: 0.85rem; }
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
    <h2 class="fw-bold mb-4">Manage Attendance</h2>

<form method="GET" class="row g-2 align-items-end mb-4">

  <?php if ($role === 'admin' || $role === 'manager'): ?>
    <div class="col-md-3">
      <label for="user_id" class="form-label mb-1 small">User</label>
      <select id="user_id" name="user_id" class="form-select form-select-sm">
        <option value="">All Users</option>
        <?php foreach ($users as $user): ?>
          <option value="<?= $user['id'] ?>" <?= $filterUser == $user['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($user['username']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  <?php endif; ?>

  <div class="col-md-2">
    <label for="date_from" class="form-label mb-1 small">Date From</label>
    <input type="date" id="date_from" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filterDateFrom) ?>">
  </div>

  <div class="col-md-2">
    <label for="date_to" class="form-label mb-1 small">Date To</label>
    <input type="date" id="date_to" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($filterDateTo) ?>">
  </div>

  <div class="col-md-2">
    <label for="status" class="form-label mb-1 small">Status</label>
    <select id="status" name="status" class="form-select form-select-sm">
      <option value="">All Status</option>
      <?php foreach (['Present', 'Absent', 'Late', 'Leave', 'Remote'] as $statusOption): ?>
        <option value="<?= $statusOption ?>" <?= $filterStatus === $statusOption ? 'selected' : '' ?>>
          <?= $statusOption ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-1 d-grid">
    <button type="submit" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-funnel me-1"></i> Filter
    </button>
  </div>




        <div class="col-md-2 text-end">
            <a href="add_attendance.php" class="btn btn-danger">Add Attendance</a>
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
                    <th>IP Address</th>
                    <th>Device Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$attendances): ?>
                    <tr><td colspan="11" class="text-center text-muted py-4">No attendance records found.</td></tr>
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
                        <td><?= htmlspecialchars($a['ip_address'] ?? '-') ?></td>
                        <td title="<?= htmlspecialchars($a['device_info'] ?? '') ?>">
                            <?= htmlspecialchars(strlen($a['device_info']) > 30 ? substr($a['device_info'], 0, 30) . '...' : $a['device_info']) ?>
                        </td>
                        <td class="text-nowrap">
                            <a href="view_attendance.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                            <?php if ($role === 'admin' || $role === 'manager'): ?>
                                <a href="edit_attendance.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <a href="delete_attendance.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this attendance record?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav aria-label="Attendance pagination" class="mt-3">
        <ul class="pagination justify-content-center">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
