<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    echo "<div class='alert alert-danger m-5'>Invalid leave request ID.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

// Fetch the leave record
$stmt = $pdo->prepare("
    SELECT el.*, u.username 
    FROM employee_leaves el
    JOIN users u ON el.employee_id = u.id
    WHERE el.id = :id
");
$stmt->execute([':id' => $id]);
$leave = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$leave) {
    echo "<div class='alert alert-warning m-5'>Leave request not found.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}
?>

<div class="container my-5">
    <h3 class="mb-4">Leave Reason for <?= htmlspecialchars($leave['username']) ?></h3>
    <p><strong>Leave Type:</strong> <?= htmlspecialchars($leave['leave_type']) ?></p>
    <p><strong>Date Range:</strong> <?= htmlspecialchars($leave['start_date']) ?> to <?= htmlspecialchars($leave['end_date']) ?></p>
    <hr>
    <h5>Reason:</h5>
    <div style="white-space: pre-line; border: 1px solid #ccc; padding: 1rem; border-radius: 5px;">
        <?= nl2br(htmlspecialchars($leave['reason'])) ?>
    </div>
    <a href="manage_leaves.php" class="btn btn-secondary btn-sm mt-4">Back to Leave Requests</a>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
