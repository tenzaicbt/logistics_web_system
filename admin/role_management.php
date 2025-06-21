<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
include '../includes/header.php';

authorize(['admin', 'sub-admin']);

$errors = [];
$success = null;

function fetchPermissionsGrouped($pdo) {
    $sql = "SELECT pg.id AS group_id, pg.name AS group_name, pg.description AS group_desc, 
                   p.id AS permission_id, p.permission_key, p.description AS perm_desc
            FROM permission_groups pg
            LEFT JOIN permissions p ON p.group_id = pg.id
            ORDER BY pg.name, p.permission_key";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $groups = [];
    foreach ($rows as $row) {
        $gid = $row['group_id'] ?? 0;
        if (!isset($groups[$gid])) {
            $groups[$gid] = [
                'group_name' => $row['group_name'] ?? 'Ungrouped',
                'group_desc' => $row['group_desc'],
                'permissions' => []
            ];
        }
        if ($row['permission_id']) {
            $groups[$gid]['permissions'][] = [
                'id' => $row['permission_id'],
                'key' => $row['permission_key'],
                'desc' => $row['perm_desc'],
            ];
        }
    }
    return $groups;
}

function fetchAllRoles($pdo) {
    return $pdo->query("SELECT * FROM roles ORDER BY role_name")->fetchAll(PDO::FETCH_ASSOC);
}

function fetchRolePermissions($pdo, $role_id) {
    $stmt = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ? AND granted = 1");
    $stmt->execute([$role_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_role') {
        $role_name = trim($_POST['role_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($role_name === '') {
            $errors[] = "Role name is required.";
        } else {
            $exists = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE role_name = ?");
            $exists->execute([$role_name]);
            if ($exists->fetchColumn() > 0) {
                $errors[] = "Role name already exists.";
            }
        }

        if (empty($errors)) {
            $insert = $pdo->prepare("INSERT INTO roles (role_name, description, created_at) VALUES (?, ?, NOW())");
            $insert->execute([$role_name, $description]);
            $success = "Role '$role_name' added.";
        }
    } elseif ($action === 'update_role') {
        $role_id = (int)($_POST['role_id'] ?? 0);
        $role_name = trim($_POST['role_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];

        if ($role_id <= 0) {
            $errors[] = "Invalid role ID.";
        }
        if ($role_name === '') {
            $errors[] = "Role name is required.";
        }

        if (empty($errors)) {
            $dup = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE role_name = ? AND id != ?");
            $dup->execute([$role_name, $role_id]);
            if ($dup->fetchColumn() > 0) {
                $errors[] = "Role name already exists.";
            }
        }

        if (empty($errors)) {
            $update = $pdo->prepare("UPDATE roles SET role_name = ?, description = ? WHERE id = ?");
            $update->execute([$role_name, $description, $role_id]);

            $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$role_id]);
            if (is_array($permissions)) {
                $insert_perm = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id, granted, created_at) VALUES (?, ?, 1, NOW())");
                foreach ($permissions as $pid) {
                    $pid = (int)$pid;
                    $insert_perm->execute([$role_id, $pid]);
                }
            }
            $success = "Role updated successfully.";
        }
    } elseif ($action === 'delete_role') {
        $role_id = (int)($_POST['role_id'] ?? 0);
        if ($role_id <= 0) {
            $errors[] = "Invalid role ID.";
        } else {
            $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$role_id]);
            $pdo->prepare("DELETE FROM user_roles WHERE role_id = ?")->execute([$role_id]);
            $pdo->prepare("DELETE FROM roles WHERE id = ?")->execute([$role_id]);
            $success = "Role deleted.";
        }
    }
}

$roles = fetchAllRoles($pdo);
$permission_groups = fetchPermissionsGrouped($pdo);

$selected_role_id = isset($_POST['role_id']) ? (int)$_POST['role_id'] : ($roles[0]['id'] ?? 0);
$role_to_edit = null;
$role_permissions = [];

if ($selected_role_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
    $stmt->execute([$selected_role_id]);
    $role_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($role_to_edit) {
        $role_permissions = fetchRolePermissions($pdo, $selected_role_id);
    }
}
?>

<div class="container my-5">
    <h2 class="mb-4 fw-bold">Role Management</h2>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Select Role -->
    <form method="post" class="mb-4" aria-label="Select Role to Manage">
        <div class="mb-3 row align-items-center">
            <label for="role_id" class="col-sm-2 col-form-label fw-semibold">Select Role to Edit:</label>
            <div class="col-sm-6">
                <select id="role_id" name="role_id" class="form-select" onchange="this.form.submit()" aria-required="true">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>" <?= $role['id'] == $selected_role_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['role_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <noscript>
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-primary">Load Role</button>
                </div>
            </noscript>
        </div>
    </form>

    <?php if ($role_to_edit): ?>
        <!-- Edit Role Form -->
        <form method="post" aria-label="Edit Role Form">
            <input type="hidden" name="action" value="update_role">
            <input type="hidden" name="role_id" value="<?= $role_to_edit['id'] ?>">

            <div class="mb-3 row">
                <label for="role_name" class="col-sm-2 col-form-label fw-semibold">Role Name</label>
                <div class="col-sm-6">
                    <input type="text" id="role_name" name="role_name" class="form-control" value="<?= htmlspecialchars($role_to_edit['role_name']) ?>" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="description" class="col-sm-2 col-form-label fw-semibold">Description</label>
                <div class="col-sm-6">
                    <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($role_to_edit['description']) ?></textarea>
                </div>
            </div>

            <fieldset class="mb-4">
                <legend class="fw-semibold">Permissions</legend>

                <?php foreach ($permission_groups as $group): ?>
                    <div class="mb-3 border rounded p-3">
                        <h6 class="mb-1"><?= htmlspecialchars($group['group_name']) ?></h6>
                        <?php if ($group['group_desc']): ?>
                            <small class="text-muted d-block mb-2"><?= htmlspecialchars($group['group_desc']) ?></small>
                        <?php endif; ?>

                        <?php if (count($group['permissions']) === 0): ?>
                            <p><em>No permissions in this group.</em></p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($group['permissions'] as $perm):
                                    $checked = in_array($perm['id'], $role_permissions) ? 'checked' : '';
                                ?>
                                    <div class="col-md-4 col-sm-6 permission-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="perm_<?= $perm['id'] ?>" name="permissions[]" value="<?= $perm['id'] ?>" <?= $checked ?>>
                                            <label class="form-check-label" for="perm_<?= $perm['id'] ?>" title="<?= htmlspecialchars($perm['desc']) ?>">
                                                <?= htmlspecialchars($perm['key']) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </fieldset>

            <div class="mb-3 row">
                <div class="col-sm-8 d-flex justify-content-between">
                    <button type="submit" class="btn btn-danger">Save Changes</button>
                    <a href="manage_users.php" class="btn btn-secondary">Back to Users</a>
                </div>
            </div>
        </form>

        <!-- Delete Role Form -->
        <form method="post" onsubmit="return confirm('Are you sure you want to delete this role? This action cannot be undone.');" class="mb-5">
            <input type="hidden" name="action" value="delete_role">
            <input type="hidden" name="role_id" value="<?= $role_to_edit['id'] ?>">
            <button type="submit" class="btn btn-outline-danger">Delete Role</button>
        </form>
    <?php else: ?>
        <p>No roles found.</p>
    <?php endif; ?>

    <!-- Add New Role -->
    <hr>
    <h3 class="fw-bold mb-4">Add New Role</h3>
    <form method="post" aria-label="Add New Role Form">
        <input type="hidden" name="action" value="add_role">
        <div class="mb-3 row">
            <label for="new_role_name" class="col-sm-2 col-form-label fw-semibold">Role Name</label>
            <div class="col-sm-6">
                <input type="text" id="new_role_name" name="role_name" class="form-control" required>
            </div>
        </div>
        <div class="mb-3 row">
            <label for="new_role_desc" class="col-sm-2 col-form-label fw-semibold">Description</label>
            <div class="col-sm-6">
                <textarea id="new_role_desc" name="description" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="mb-3 row">
            <div class="col-sm-6 offset-sm-2">
                <button type="submit" class="btn btn-primary">Add Role</button>
            </div>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
