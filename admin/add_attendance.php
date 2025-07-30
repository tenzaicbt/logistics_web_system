<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$role = $_SESSION['role'] ?? 'user';
$userId = $_SESSION['user_id'] ?? null;

// access
if (!in_array($role, ['admin', 'manager', 'employer'])) {
    echo "<div class='alert alert-danger m-5'>Access denied. You do not have permission to add attendance.</div>";
    require_once '../includes/footer.php';
    exit;
}

$errors = [];
$success = '';

// dropdown
$users = [];
$stmt = $pdo->query("SELECT id, username, role FROM users WHERE is_active = 1 AND role IN ('admin','manager','employer') ORDER BY username");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formUserId = (int) ($_POST['user_id'] ?? 0);
    $date = $_POST['date'] ?? '';
    $checkIn = $_POST['check_in_time'] ?? '';
    $checkOut = $_POST['check_out_time'] ?? '';
    $status = $_POST['status'] ?? 'Present';
    $remarks = trim($_POST['remarks'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $deviceInfo = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $isManual = isset($_POST['is_manual_entry']) ? 1 : 0;

    // Validation
    if (!$formUserId) {
        $errors[] = "User is required.";
    }
    if (!$date) {
        $errors[] = "Date is required.";
    }
    if ($checkIn && $checkOut && strtotime($checkOut) <= strtotime($checkIn)) {
        $errors[] = "Check-out time must be after check-in time.";
    }
    if (!in_array($status, ['Present', 'Absent', 'Late', 'Leave', 'Remote'])) {
        $errors[] = "Invalid status selected.";
    }

    // Check if attendance
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendances WHERE user_id = ? AND date = ?");
    $stmt->execute([$formUserId, $date]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Attendance record for this user on this date already exists.";
    }

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO attendances
            (user_id, date, check_in_time, check_out_time, status, remarks, location, ip_address, device_info, is_manual_entry)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $formUserId,
            $date,
            $checkIn ?: null,
            $checkOut ?: null,
            $status,
            $remarks ?: null,
            $location ?: null,
            $ipAddress,
            $deviceInfo,
            $isManual
        ]);

        $success = "Attendance added successfully.";
    }
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
</style>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Add Attendance</h2>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="user_id" class="form-label">User (Admin/Manager/Employer)</label>
                <select name="user_id" id="user_id" class="form-select" required>
                    <option value="">Select User</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= (isset($_POST['user_id']) && $_POST['user_id'] == $user['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['role']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="date" class="form-label">Date</label>
                <input type="date" id="date" name="date" class="form-control" required
                       value="<?= htmlspecialchars($_POST['date'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label for="check_in_time" class="form-label">Check-in Time</label>
                <input type="time" id="check_in_time" name="check_in_time" class="form-control"
                       value="<?= htmlspecialchars($_POST['check_in_time'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label for="check_out_time" class="form-label">Check-out Time</label>
                <input type="time" id="check_out_time" name="check_out_time" class="form-control"
                       value="<?= htmlspecialchars($_POST['check_out_time'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select" required>
                    <?php
                    $statuses = ['Present', 'Absent', 'Late', 'Leave', 'Remote'];
                    $selectedStatus = $_POST['status'] ?? 'Present';
                    foreach ($statuses as $statusOption): ?>
                        <option value="<?= $statusOption ?>" <?= $selectedStatus === $statusOption ? 'selected' : '' ?>>
                            <?= $statusOption ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6 d-flex align-items-center">
                <div class="form-check mt-4">
                    <input type="checkbox" class="form-check-input" id="is_manual_entry" name="is_manual_entry" <?= isset($_POST['is_manual_entry']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_manual_entry">Manual Entry</label>
                </div>
            </div>

            <div class="col-md-6">
                <label for="location" class="form-label">Location (optional)</label>
                <input type="text" id="location" name="location" class="form-control"
                       value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label for="remarks" class="form-label">Remarks</label>
                <textarea id="remarks" name="remarks" class="form-control" rows="3"><?= htmlspecialchars($_POST['remarks'] ?? '') ?></textarea>
            </div>

            <div class="col-12 mt-3">
                <a href="manage_attendance.php" class="btn btn-secondary">Back to Attendance</a>
                <button type="submit" class="btn btn-danger">Add Attendance</button>
                
            </div>
        </div>
    </form>
</div>


<?php require_once '../includes/admin_footer.php'; ?>
