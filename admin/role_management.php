<?php
session_start();
require_once '../includes/db.php';
include '../includes/header.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'], $_POST['permissions'])) {
    $role = $_POST['role'];
    $permissions = $_POST['permissions'];

    $pdo->prepare("DELETE FROM roles_permissions WHERE role = ?")->execute([$role]);

    $insert = $pdo->prepare("INSERT INTO roles_permissions (role, module, can_view, can_create, can_edit, can_delete) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($permissions as $module => $perms) {
        $insert->execute([
            $role,
            $module,
            isset($perms['view']) ? 1 : 0,
            isset($perms['create']) ? 1 : 0,
            isset($perms['edit']) ? 1 : 0,
            isset($perms['delete']) ? 1 : 0
        ]);
    }

    $success = "Permissions updated for role '$role'.";
}

$roles = ['admin', 'manager', 'employer', 'user'];

$existing_permissions = [];
$stmt = $pdo->query("SELECT * FROM roles_permissions");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $existing_permissions[$row['role']][$row['module']] = [
        'view' => $row['can_view'],
        'create' => $row['can_create'],
        'edit' => $row['can_edit'],
        'delete' => $row['can_delete']
    ];
}

$modules = ['users', 'bookings', 'shipments', 'invoices', 'documents', 'containers', 'fleets', 'payments', 'settings', 'notifications', 'logs'];

$selected_role = $_POST['role'] ?? 'admin';
?>

<div class="container my-4">
    <h4 class="mb-3 fw-bold text-uppercase">Role Permissions</h4>

    <?php if ($success): ?>
        <div class="alert alert-success py-1 px-2 small"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto fw-semibold small">Select Role:</div>
            <div class="col-sm-4">
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role ?>" <?= $role === $selected_role ? 'selected' : '' ?>><?= ucfirst($role) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <form method="post">
        <input type="hidden" name="role" value="<?= $selected_role ?>">

        <table class="table table-sm table-bordered align-middle text-center fs-6">
            <thead class="table-light">
                <tr class="small text-uppercase">
                    <th class="text-start">Module</th>
                    <th>View</th>
                    <th>Create</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modules as $module): 
                    $perm = $existing_permissions[$selected_role][$module] ?? ['view' => 0, 'create' => 0, 'edit' => 0, 'delete' => 0];
                ?>
                    <tr>
                        <td class="text-start"><?= ucfirst($module) ?></td>
                        <td><input type="checkbox" name="permissions[<?= $module ?>][view]" <?= $perm['view'] ? 'checked' : '' ?>></td>
                        <td><input type="checkbox" name="permissions[<?= $module ?>][create]" <?= $perm['create'] ? 'checked' : '' ?>></td>
                        <td><input type="checkbox" name="permissions[<?= $module ?>][edit]" <?= $perm['edit'] ? 'checked' : '' ?>></td>
                        <td><input type="checkbox" name="permissions[<?= $module ?>][delete]" <?= $perm['delete'] ? 'checked' : '' ?>></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-between mt-3">
            <button type="submit" class="btn btn-danger btn-sm px-4">Save</button>
            <a href="manage_users.php" class="btn btn-secondary btn-sm px-4">Back</a>
        </div>
    </form>
</div>

<?php include '../includes/admin_footer.php'; ?>
