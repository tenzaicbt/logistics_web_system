<?php
require_once '../includes/auth_check.php';
requireRole([ROLE_ADMIN]);

require_once '../config/db.php';
require_once '../includes/functions.php';

$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? 0;

    if ($action === 'toggle_status') {
        $stmt = $pdo->prepare("UPDATE users SET status = IF(status='active','inactive','active') WHERE id = ?");
        $stmt->execute([$user_id]);
        $success = 'User status updated.';
    }

    if ($action === 'delete_user') {
        if ($user_id != $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = 'User deleted.';
        } else {
            $error = "⚠️ You cannot delete yourself.";
        }
    }

    if ($action === 'add_user') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "Email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute([$name, $email, $hash, $role]);
            $success = "User added successfully.";
        }
    }
}

// Fetch all users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4 text-primary fw-bold">👤 Manage Users</h2>

    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php elseif ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Add User Form -->
    <div class="card shadow-sm mb-5">
        <div class="card-header bg-primary text-white fw-semibold">
            Add New User
        </div>
        <div class="card-body">
            <form method="post" class="row g-3 align-items-center">
                <input type="hidden" name="action" value="add_user">

                <div class="col-md-3">
                    <input type="text" name="name" class="form-control form-control-lg" placeholder="Full Name" required>
                </div>
                <div class="col-md-3">
                    <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" required>
                </div>
                <div class="col-md-3">
                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select form-select-lg" required>
                        <option value="user">User</option>
                        <option value="sub_admin">Sub-Admin</option>
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-success btn-lg" title="Add New User">➕</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white fw-semibold">
            User List
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name / Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($user['name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                        </td>
                        <td>
                            <span class="badge bg-info text-dark text-uppercase fw-semibold" style="font-size: 0.85rem;">
                                <?= htmlspecialchars($user['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $user['status'] === 'active' ? 'bg-success' : 'bg-danger' ?> text-uppercase fw-semibold" style="font-size: 0.85rem;">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </td>
                        <td><small class="text-muted"><?= date('Y-m-d', strtotime($user['created_at'])) ?></small></td>
                        <td class="text-center">
                            <form method="post" class="d-inline me-1">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="action" value="toggle_status">
                                <button type="submit" class="btn btn-sm <?= $user['status'] === 'active' ? 'btn-warning' : 'btn-success' ?>" 
                                  title="<?= $user['status'] === 'active' ? 'Deactivate User' : 'Activate User' ?>">
                                    <?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>

                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this user?');">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="action" value="delete_user">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete User">Delete</button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted small">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No users found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
