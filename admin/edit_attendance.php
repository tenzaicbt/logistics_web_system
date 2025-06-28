<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$role = $_SESSION['role'] ?? 'user';
$userId = $_SESSION['user_id'] ?? null;

if (!in_array($role, ['admin', 'manager'])) {
    echo "<div class='alert alert-danger m-5'>Access denied. Only admin or manager can edit attendance records.</div>";
    require_once '../includes/footer.php';
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    echo "<div class='alert alert-danger m-5'>Invalid attendance record ID.</div>";
    require_once '../includes/footer.php';
    exit;
}

// Fetch attendance record
$stmt = $pdo->prepare("SELECT * FROM attendances WHERE id = ?");
$stmt->execute([$id]);
$attendance = $stmt->fetch();

if (!$attendance) {
    echo "<div class='alert alert-danger m-5'>Attendance record not found.</div>";
    require_once '../includes/footer.php';
    exit;
}

// Fetch users for dropdown
$users = $pdo->query("SELECT id, username FROM users WHERE is_active = 1 ORDER BY username")->fetchAll();

// Initialize errors and success
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $date = $_POST['date'] ?? '';
    $check_in_time = $_POST['check_in_time'] ?? '';
    $check_out_time = $_POST['check_out_time'] ?? '';
    $status = $_POST['status'] ?? '';
    $remarks = trim($_POST['remarks'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $ip_address = trim($_POST['ip_address'] ?? '');
    $device_info = trim($_POST['device_info'] ?? '');
    $is_manual_entry = isset($_POST['is_manual_entry']) ? 1 : 0;

    // Validate
    if (!$user_id || !is_numeric($user_id)) {
        $errors[] = "Please select a valid user.";
    }
    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors[] = "Please enter a valid date (YYYY-MM-DD).";
    }
    $validStatuses = ['Present', 'Absent', 'Late', 'Leave', 'Remote'];
    if (!in_array($status, $validStatuses)) {
        $errors[] = "Please select a valid status.";
    }
    if ($check_in_time && !preg_match('/^\d{2}:\d{2}$/', $check_in_time)) {
        $errors[] = "Check-in time format must be HH:MM.";
    }
    if ($check_out_time && !preg_match('/^\d{2}:\d{2}$/', $check_out_time)) {
        $errors[] = "Check-out time format must be HH:MM.";
    }

    if (empty($errors)) {
        // Update
        $updateStmt = $pdo->prepare("UPDATE attendances SET 
            user_id = ?, 
            date = ?, 
            check_in_time = NULLIF(?, ''),
            check_out_time = NULLIF(?, ''),
            status = ?, 
            remarks = ?, 
            location = ?, 
            ip_address = ?, 
            device_info = ?, 
            is_manual_entry = ?
            WHERE id = ?");
        
        $updateStmt->execute([
            $user_id,
            $date,
            $check_in_time,
            $check_out_time,
            $status,
            $remarks ?: null,
            $location ?: null,
            $ip_address ?: null,
            $device_info ?: null,
            $is_manual_entry,
            $id
        ]);

        $success = true;

        // Reload updated record
        $stmt = $pdo->prepare("SELECT * FROM attendances WHERE id = ?");
        $stmt->execute([$id]);
        $attendance = $stmt->fetch();
    }
}

?>

<style>
    .form-control, .form-select { font-size: 0.9rem; }
    label { font-weight: 600; }
    .btn { font-size: 0.9rem; padding: 0.3rem 1rem; }
</style>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Edit Attendance Record</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">Attendance record updated successfully.</div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3">
            <label for="user_id" class="form-label">User</label>
            <select id="user_id" name="user_id" class="form-select" required>
                <option value="">Select user</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= ($attendance['user_id'] == $user['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($attendance['date']) ?>" required>
        </div>

        <div class="mb-3 row">
            <div class="col-md-6">
                <label for="check_in_time" class="form-label">Check-in Time (HH:MM)</label>
                <input type="time" id="check_in_time" name="check_in_time" class="form-control" value="<?= htmlspecialchars($attendance['check_in_time']) ?>">
            </div>
            <div class="col-md-6">
                <label for="check_out_time" class="form-label">Check-out Time (HH:MM)</label>
                <input type="time" id="check_out_time" name="check_out_time" class="form-control" value="<?= htmlspecialchars($attendance['check_out_time']) ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select" required>
                <?php
                foreach (['Present', 'Absent', 'Late', 'Leave', 'Remote'] as $option): ?>
                    <option value="<?= $option ?>" <?= ($attendance['status'] === $option) ? 'selected' : '' ?>>
                        <?= $option ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea id="remarks" name="remarks" class="form-control" rows="3"><?= htmlspecialchars($attendance['remarks']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input id="location" name="location" type="text" class="form-control" value="<?= htmlspecialchars($attendance['location']) ?>">
        </div>

        <div class="mb-3">
            <label for="ip_address" class="form-label">IP Address</label>
            <input id="ip_address" name="ip_address" type="text" class="form-control" value="<?= htmlspecialchars($attendance['ip_address']) ?>">
        </div>

        <div class="mb-3">
            <label for="device_info" class="form-label">Device Info</label>
            <textarea id="device_info" name="device_info" class="form-control" rows="2"><?= htmlspecialchars($attendance['device_info']) ?></textarea>
        </div>

        <div class="form-check mb-3">
            <input id="is_manual_entry" name="is_manual_entry" class="form-check-input" type="checkbox" value="1" <?= $attendance['is_manual_entry'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_manual_entry">Manual Entry</label>
        </div>

        <button type="submit" class="btn btn-danger">Update Attendance</button>
        <a href="manage_attendance.php" class="btn btn-secondary ms-2">Back to List</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
