<?php
session_start();
require_once '../includes/db.php';   // PDO connection $pdo

// Simple master admin login check
if (!isset($_SESSION['master_admin_logged_in']) || !$_SESSION['master_admin_logged_in']) {
    header('Location: master_login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: master_login.php');
    exit;
}

if (isset($_GET['toggle_id'])) {
    $toggle_id = (int)$_GET['toggle_id'];

    // Protect yourself from disabling yourself or master admins here if needed
    if ($toggle_id !== 0) { // Adjust this condition as per your master admin ID(s)
        $stmt = $pdo->prepare("UPDATE users SET is_active = 1 - is_active WHERE id = ?");
        $stmt->execute([$toggle_id]);
        header('Location: master.php');
        exit;
    }
}

// Fetch all users with roles and permissions
$sql = "
SELECT u.*, 
    GROUP_CONCAT(DISTINCT r.role_name ORDER BY r.role_name SEPARATOR ', ') AS roles,
    GROUP_CONCAT(DISTINCT p.permission_key ORDER BY p.permission_key SEPARATOR ', ') AS permissions
FROM users u
LEFT JOIN user_roles ur ON u.id = ur.user_id
LEFT JOIN roles r ON ur.role_id = r.id
LEFT JOIN user_permissions up ON u.id = up.user_id AND up.is_granted = 1
LEFT JOIN permissions p ON up.permission_id = p.id
GROUP BY u.id
ORDER BY u.created_at DESC
";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

function statusIcon($active) {
    if ($active) {
        return '<span style="color:#2a7a2a;font-weight:bold;">&#10003;</span>'; // green tick
    }
    return '<span style="color:#a00;font-weight:bold;">&#10007;</span>'; // red cross
}
?>

<!-- Include your header.php for the navbar -->
<?php include '../includes/header.php'; ?>

<style>
    /* Clean minimal table styles */
    table {
        border-collapse: collapse;
        width: 100%;
        font-family: Arial, sans-serif;
        font-size: 0.9rem;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px 10px;
        text-align: left;
        vertical-align: middle;
    }
    th {
        background: #f5f5f5;
        color: #333;
        font-weight: 600;
    }
    tr:nth-child(even) {
        background: #fafafa;
    }
    tr:hover {
        background: #f0f0f0;
    }
    .btn-simple {
        background: none;
        border: 1px solid #666;
        color: #333;
        padding: 4px 10px;
        margin: 0 2px;
        font-size: 0.85rem;
        cursor: pointer;
        border-radius: 3px;
        text-decoration: none;
        user-select: none;
    }
    .btn-simple:hover {
        background: #eaeaea;
        border-color: #333;
        color: #000;
    }
    .btn-simple:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .actions-group {
        white-space: nowrap;
    }
    /* Container padding adjustment */
    .master-container {
        max-width: 1200px;
        margin: 40px auto 80px auto;
        padding: 0 15px;
    }
</style>

<div class="master-container">
    <h1 style="font-weight:700; color:#222; margin-bottom: 20px;">MASTER ADMIN PANEL</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Roles</th>
                <th>Permissions</th>
                <th>Status</th>
                <th>Created</th>
                <th>Updated</th>
                <th style="text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$users): ?>
                <tr>
                    <td colspan="8" style="text-align:center; color:#666; padding: 20px;">No users found.</td>
                </tr>
            <?php else: foreach ($users as $user): ?>
                <tr>
                    <td><?= (int)$user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['roles'] ?: '-') ?></td>
                    <td style="font-size: 0.85rem;"><?= htmlspecialchars($user['permissions'] ?: '-') ?></td>
                    <td style="text-align:center;"><?= statusIcon((int)$user['is_active']) ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($user['updated_at'])) ?></td>
                    <td class="actions-group" style="text-align:center;">
                        <a href="user_edit.php?id=<?= $user['id'] ?>" class="btn-simple" title="Edit User">Edit</a>
                        <a href="assign_roles.php?user_id=<?= $user['id'] ?>" class="btn-simple" title="Assign Roles">Roles</a>
                        <a href="assign_permissions.php?user_id=<?= $user['id'] ?>" class="btn-simple" title="Assign Permissions">Permissions</a>
                        <a href="reset_password.php?user_id=<?= $user['id'] ?>" class="btn-simple" title="Reset Password" onclick="return confirm('Reset password for user <?= htmlspecialchars(addslashes($user['username'])) ?>?')">Reset PW</a>
                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                            <a href="?toggle_id=<?= $user['id'] ?>" class="btn-simple" title="<?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>" onclick="return confirm('Are you sure you want to <?= $user['is_active'] ? 'deactivate' : 'activate' ?> this user?')">
                                <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                            </a>
                        <?php else: ?>
                            <button class="btn-simple" disabled title="Cannot deactivate yourself">Deactivate</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<footer style="text-align:center; color:#999; padding:30px 0; font-size:0.85rem;">
    &copy; <?= date('Y') ?> Tradefording Shipping Line. All rights reserved.
</footer>

<!-- Bootstrap JS Bundle (for navbar toggler) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
