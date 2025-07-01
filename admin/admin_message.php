<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

// Only admin and manager can view
if (!in_array($_SESSION['role'] ?? '', ['admin', 'manager'])) {
    echo '<div class="container my-5"><div class="alert alert-danger">Access denied.</div></div>';
    require_once '../includes/admin_footer.php';
    exit;
}

$currentRole = $_SESSION['role'] ?? 'user';

// Handle status toggle (only for admin)
if ($currentRole === 'admin' && isset($_GET['toggle_status'], $_GET['id'])) {
    $id        = (int) $_GET['id'];
    $newStatus = ($_GET['toggle_status'] === 'Solved') ? 'Solved' : 'Pending';

    $stmt = $pdo->prepare(
        "UPDATE admin_messages SET status = ? WHERE id = ?"
    );
    $stmt->execute([$newStatus, $id]);

    $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');      // /admin/admin_message.php
    $params  = $_GET;
    unset($params['id'], $params['toggle_status']);       // toss the temp stuff
    $qs = $params ? ('?' . http_build_query($params)) : '';

    header("Location: {$baseUrl}{$qs}");
    exit;
}


// Fetch all messages
$stmt = $pdo->query("SELECT * FROM admin_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();

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
    .small-text {
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

    .btn-success {
        background-color: #28a745;
        border: none;
    }

    .btn-success:hover {
        background-color: #218838;
    }

    .table th,
    .table td {
        vertical-align: middle !important;
    }
</style>

<div class="container my-5 small-text">
    <h2 class="fw-bold mb-4">Admin Contact Messages</h2>

    <?php if (empty($messages)): ?>
        <div class="alert alert-info">No messages found.</div>
    <?php else: ?>
        <div class="table-responsive shadow-sm">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Received On</th>
                        <th>Status</th>
                        <?php if ($currentRole === 'admin'): ?>
                            <th>Toggle Status</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $i => $msg): ?>
                        <tr class="<?= $msg['status'] === 'Solved' ? 'table-success' : '' ?>">
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($msg['name']) ?></td>
                            <td><?= htmlspecialchars($msg['email']) ?></td>
                            <td><?= htmlspecialchars($msg['subject'] ?: '-') ?></td>
                            <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
                            <td><?= date('d M Y, h:i A', strtotime($msg['created_at'])) ?></td>
                            <td>
                                <span class="badge <?= $msg['status'] === 'Solved' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= htmlspecialchars($msg['status']) ?>
                                </span>
                            </td>
                            <?php if ($currentRole === 'admin'): ?>
                                <td>
                                    <?php if ($msg['status'] === 'Pending'): ?>
                                        <a href="?id=<?= $msg['id'] ?>&toggle_status=Solved" class="btn btn-sm btn-success">Done</a>
                                    <?php else: ?>
                                        <a href="?id=<?= $msg['id'] ?>&toggle_status=Pending" class="btn btn-sm btn-danger">Pending</a>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>


<?php require_once '../includes/admin_footer.php'; ?>