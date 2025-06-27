<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$currentRole = $_SESSION['role'] ?? 'user';
$userId = $_SESSION['user_id'] ?? 0;

// Access control
if (!in_array($currentRole, ['admin', 'employer'])) {
    echo "<div class='alert alert-danger m-5'>Access denied.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

// Upload success message
$uploadMessage = '';

// Handle Upload (Admin only)
if ($currentRole === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $employerId = (int)$_POST['employer_id'];
    $month = $_POST['month'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if (isset($_FILES['paysheet']) && $_FILES['paysheet']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/paysheets/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['paysheet']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['paysheet']['tmp_name'], $targetPath)) {
            $stmt = $pdo->prepare("INSERT INTO paysheets (employer_id, uploaded_by, file_path, month, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$employerId, $userId, $targetPath, $month, $notes]);
            $uploadMessage = "<div class='alert alert-success'>Paysheet uploaded successfully.</div>";
        } else {
            $uploadMessage = "<div class='alert alert-danger'>Failed to upload file.</div>";
        }
    }
}

// Fetch paysheets
if ($currentRole === 'admin') {
    $stmt = $pdo->query("SELECT p.*, u.username AS employer_name FROM paysheets p JOIN users u ON p.employer_id = u.id ORDER BY p.created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM paysheets WHERE employer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
}
$paysheets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch employers and managers for dropdown (admin only)
$employers = [];
if ($currentRole === 'admin') {
    $stmtUsers = $pdo->prepare("SELECT id, username FROM users WHERE role IN ('employer', 'manager') ORDER BY username ASC");
    $stmtUsers->execute();
    $employers = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
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

    /* .container {
        font-size: 0.85rem;
    } */

    h2 {
        font-size: 1.25rem;
    }
</style>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Manage Paysheets</h2>
        <?php if ($currentRole === 'admin'): ?>
            <!-- Optional button or link -->
        <?php endif; ?>
    </div>

    <?= $uploadMessage ?>

    <?php if ($currentRole === 'admin'): ?>
        <form method="post" enctype="multipart/form-data" class="row g-2 mb-4">
            <div class="col-md-3">
                <label class="form-label">Select Employer/Manager</label>
                <select name="employer_id" class="form-select" required>
                    <option value="">-- Select Employer/Manager --</option>
                    <?php foreach ($employers as $employer): ?>
                        <option value="<?= $employer['id'] ?>"><?= htmlspecialchars($employer['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Month</label>
                <input type="text" name="month" class="form-control" placeholder="e.g., June 2025" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Upload PDF</label>
                <input type="file" name="paysheet" class="form-control" accept="application/pdf" required>
            </div>
            <div class="col-md-12">
                <label class="form-label">Notes (optional)</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-md-2">
                <button class="btn btn-danger w-100 mt-3" type="submit">Upload</button>
            </div>
        </form>
    <?php endif; ?>

    <div class="table-responsive card shadow-sm">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Employer</th>
                    <th>Month</th>
                    <th>File</th>
                    <th>Uploaded</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($paysheets)): ?>
                    <?php foreach ($paysheets as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td><?= $p['employer_name'] ?? $p['employer_id'] ?></td>
                            <td><?= htmlspecialchars($p['month']) ?></td>
                            <td><a href="<?= $p['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Download</a></td>
                            <td><?= date('Y-m-d', strtotime($p['created_at'])) ?></td>
                            <td><?= htmlspecialchars($p['notes']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No paysheets found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
