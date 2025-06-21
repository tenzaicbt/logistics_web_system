<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Only admin and sub-admin allowed
authorize(['admin', 'sub-admin']);

$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Toggle activation - only if not self
if (isset($_GET['toggle_id'])) {
    $toggle_id = (int)$_GET['toggle_id'];
    if ($toggle_id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET is_active = 1 - is_active WHERE id = ?");
        $stmt->execute([$toggle_id]);
    }
    header("Location: manage_users.php?page=$page&search=" . urlencode($search));
    exit;
}

// Delete user - only if not self
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    if ($delete_id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_id]);
    }
    header("Location: manage_users.php?page=$page&search=" . urlencode($search));
    exit;
}

// Count total users matching search
$params = [];
$sqlCount = "SELECT COUNT(*) FROM users";
if ($search) {
    $sqlCount .= " WHERE username LIKE ? OR email LIKE ?";
    $params = ["%$search%", "%$search%"];
}
$stmt = $pdo->prepare($sqlCount);
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Fetch users directly from users table (using 'role' or 'user_role')
$sql = "SELECT * FROM users";
if ($search) {
    $sql .= " WHERE username LIKE ? OR email LIKE ?";
}
$sql .= " ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPages = ceil($total / $perPage);

// Status icon
function redCheckIcon()
{
    return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="red" class="bi bi-check-lg" viewBox="0 0 16 16">'
        . '<path d="M13.485 1.929a.75.75 0 0 1 1.06 1.06l-8.25 8.25a.75.75 0 0 1-1.06 0L1.47 7.56a.75.75 0 1 1 1.06-1.06l3.182 3.183 7.773-7.773z"/></svg>';
}
?>

<?php require_once '../includes/header.php'; ?>

<style>
    /* Simple minimal buttons for search and add */
    .btn-simple {
        background: transparent;
        border: 1px solid #b6050e;
        color: #b6050e;
        padding: 6px 14px;
        font-size: 0.9rem;
        cursor: pointer;
        border-radius: 4px;
        text-decoration: none;
        user-select: none;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .btn-simple:hover,
    .btn-simple:focus {
        background-color: #b6050e;
        color: #fff;
        outline: none;
    }

    .btn-simple:disabled,
    .btn-simple[disabled] {
        opacity: 0.5;
        cursor: not-allowed;
    }

    form.row {
        display: flex;
        gap: 10px;
        margin-bottom: 1.25rem;
    }

    input[type="search"] {
        border: 1px solid #b6050e;
        border-radius: 4px;
        padding: 6px 10px;
        font-size: 0.9rem;
        width: 220px;
        outline-offset: 2px;
        transition: border-color 0.2s ease;
    }

    input[type="search"]:focus {
        border-color: #8b0000;
    }

    /* Table styling */
    .table-responsive {
        overflow-x: auto;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        max-width: 100%;
        margin-top: 1rem;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px 10px;
        text-align: center;
        vertical-align: middle;
    }

    th {
        background-color: #f9f9f9;
        color: #b6050e;
    }

    td {
        font-size: 0.9rem;
    }

    .btn-group-sm>.btn-simple {
        padding: 4px 8px;
        font-size: 0.85rem;
        margin: 0 2px;
    }

    /* Pagination */
    .pagination {
        margin-top: 20px;
        display: flex;
        justify-content: center;
        gap: 6px;
        list-style: none;
        padding: 0;
    }

    .pagination li {
        display: inline-block;
    }

    .pagination a {
        color: #b6050e;
        text-decoration: none;
        border: 1px solid #b6050e;
        padding: 6px 12px;
        border-radius: 3px;
        font-size: 0.9rem;
    }

    .pagination .active a {
        background-color: #b6050e;
        color: white;
    }

    footer {
        text-align: center;
        margin-top: 40px;
        font-size: 0.85rem;
        color: #888;
    }
</style>

<div class="container my-5">
    <h2 class="mb-4 fw-bold">MANAGE USER</h2>
    <div class="row g-2 mb-4"></div>
    <form method="get" action="manage_users.php" role="search" aria-label="Search users" style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 20px;">
        <input
            type="search"
            name="search"
            placeholder="Search username or email"
            value="<?= htmlspecialchars($search) ?>"
            style="border: 1px solid #b6050e; border-radius: 4px; padding: 6px 12px; font-size: 0.9rem; width: 220px; outline: none;"
            onfocus="this.style.borderColor='#8b0000';"
            onblur="this.style.borderColor='#b6050e';" />
        <button type="submit" class="btn-simple" style="padding: 6px 14px;">Search</button>
        <a href="user_create.php" class="btn-simple" style="margin-left: auto; padding: 6px 14px;">+ Add User</a>
    </form>

    <div class="table-responsive shadow-sm">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light text-center">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$users): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                    <?php else: foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role'] ?? $user['user_role'] ?? 'N/A') ?></td>
                            <td class="text-center">
                                <?= $user['is_active'] ? redCheckIcon() : '<span style="font-size:1.4em;" class="text-muted">&#x2717;</span>' ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group" aria-label="User actions">
                                    <a href="user_edit.php?id=<?= $user['id'] ?>" class="btn-simple">Edit</a>

                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <a href="user_toggle.php?id=<?= $user['id'] ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-simple" onclick="return confirm('Confirm toggle?');">
                                            <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </a>
                                        <a href="user_delete.php?id=<?= $user['id'] ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-simple" onclick="return confirm('Delete permanently?');">Delete</a>
                                    <?php else: ?>
                                        <button class="btn-simple" disabled>Deactivate</button>
                                        <button class="btn-simple" disabled>Delete</button>
                                    <?php endif; ?>

                                    <a href="role_management.php?user_id=<?= $user['id'] ?>" class="btn-simple">Roles</a>
                                    <a href="edit_permissions.php?user_id=<?= $user['id'] ?>" class="btn-simple">Permissions</a>
                                    <a href="user_reset_password.php?id=<?= $user['id'] ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-simple" onclick="return confirm('Reset password?');">Reset PW</a>
                                    <a href="user_kick.php?id=<?= $user['id'] ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-simple" onclick="return confirm('Kick user?');">Kick</a>
                                </div>
                            </td>

                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="mt-3" aria-label="User list pagination">
            <ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<footer class="text-center py-3">
    <div class="footer-bottom text-muted">
        &copy; <?= date('Y') ?> NorthPort Logistics Pvt Ltd. All rights reserved.
    </div>
</footer>