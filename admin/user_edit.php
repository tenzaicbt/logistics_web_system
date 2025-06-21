<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
include '../includes/header.php';

authorize(['admin', 'sub-admin']);

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
    die('Invalid user ID.');
}

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    die('User not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? ''); // Hidden input, still used
    $phone      = trim($_POST['phone'] ?? '');
    $role       = $_POST['role'] ?? 'user';
    $user_role  = $_POST['user_role'] ?? 'user';
    $is_active  = isset($_POST['is_active']) ? 1 : 0;
    $notes      = trim($_POST['notes'] ?? '');

    $errors = [];
    if (!$username) $errors[] = "Username is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE users 
            SET username = ?, phone = ?, role = ?, user_role = ?, is_active = ?, notes = ?, updated_at = NOW()
            WHERE id = ?");
        $stmt->execute([$username, $phone, $role, $user_role, $is_active, $notes, $user_id]);

        // Sync roles
        $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$user_id]);
        $roles = $_POST['roles'] ?? [];
        foreach ($roles as $role_id) {
            assignRole($pdo, $user_id, (int)$role_id);
        }

        header("Location: manage_users.php?success=1");
        exit;
    }
}

$all_roles = getAllRoles($pdo);
$assigned_roles = array_column(getUserRoles($pdo, $user_id), 'id');
?>

<div class="container my-5">
    <h2 class="mb-4 fw-bold">EDIT USER</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="col">
                <label class="form-label fw-semibold text-uppercase text-muted small">Email Address</label>
                <div class="border rounded bg-light px-3 py-2 d-flex justify-content-between align-items-center" style="min-height: 45px;">
                    <span class="fw-semibold text-dark"><?= htmlspecialchars($user['email']) ?></span>
                    <i class="bi bi-lock-fill text-secondary" title="Email is locked"></i>
                </div>
                <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                <small class="form-text text-muted fst-italic mt-1">
                    This email address is locked and cannot be modified for security purposes.
                </small>
            </div>

        </div>

        <!-- <div class="row mb-3">
            <div class="col">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
            </div>
            <div class="col">
                <label class="form-label">Main Role</label>
                <select name="role" class="form-select">
                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="sub-admin" <?= $user['role'] === 'sub-admin' ? 'selected' : '' ?>>Sub-admin</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">System Role</label>
            <select name="user_role" class="form-select">
                <option value="user" <?= $user['user_role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $user['user_role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="manager" <?= $user['user_role'] === 'manager' ? 'selected' : '' ?>>Manager</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Assigned Roles</label>
            <div class="form-check">
                <?php foreach ($all_roles as $role): ?>
                    <div>
                        <input type="checkbox" class="form-check-input" name="roles[]" value="<?= $role['id'] ?>" id="role_<?= $role['id'] ?>"
                            <?= in_array($role['id'], $assigned_roles) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="role_<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-check mb-3"> -->
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="activeCheck" <?= $user['is_active'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="activeCheck">Account Active</label>
        </div>

        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($user['notes']) ?></textarea>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-danger">Update User</button>
            <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<footer class="text-center py-4 mt-5">
    <div class="text-muted small">
        &copy; <?= date('Y') ?> NorthPort Logistics Pvt Ltd. All rights reserved.
    </div>
</footer>