<?php
// includes/functions.php

/**
 * Check if the currently logged-in user has a given permission key.
 * Checks direct user permissions and role permissions (including multiple roles).
 *
 * @param PDO $pdo
 * @param string $permissionKey
 * @param int|null $userId Optional user ID; defaults to logged-in user in session.
 * @return bool
 */
if (!function_exists('can')) {
    function can(PDO $pdo, string $permissionKey, ?int $userId = null): bool {
        if ($userId === null) {
            if (!isset($_SESSION['user_id'])) {
                return false;
            }
            $userId = intval($_SESSION['user_id']);
        }

        $sql = "
            SELECT 1 FROM permissions p
            WHERE p.permission_key = :permKey
              AND (
                EXISTS (
                    SELECT 1 FROM user_permissions up
                    WHERE up.user_id = :uid1 AND up.permission_id = p.id AND up.is_granted = 1
                )
                OR EXISTS (
                    SELECT 1 FROM user_roles ur
                    JOIN role_permissions rp ON ur.role_id = rp.role_id
                    WHERE ur.user_id = :uid2 AND rp.permission_id = p.id AND rp.granted = 1
                )
              )
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':permKey' => $permissionKey,
            ':uid1' => $userId,
            ':uid2' => $userId,
        ]);

        return (bool) $stmt->fetchColumn();
    }
}

function assignRole(PDO $pdo, int $userId, int $roleId): bool {
    $check = $pdo->prepare("SELECT 1 FROM user_roles WHERE user_id = ? AND role_id = ?");
    $check->execute([$userId, $roleId]);
    if ($check->fetchColumn()) {
        return true;
    }
    $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, assigned_at) VALUES (?, ?, NOW())");
    return $stmt->execute([$userId, $roleId]);
}

function removeRole(PDO $pdo, int $userId, int $roleId): bool {
    $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ? AND role_id = ?");
    return $stmt->execute([$userId, $roleId]);
}

function grantUserPermission(PDO $pdo, int $userId, int $permissionId): bool {
    $check = $pdo->prepare("SELECT 1 FROM user_permissions WHERE user_id = ? AND permission_id = ?");
    $check->execute([$userId, $permissionId]);
    if ($check->fetchColumn()) {
        $update = $pdo->prepare("UPDATE user_permissions SET is_granted = 1 WHERE user_id = ? AND permission_id = ?");
        return $update->execute([$userId, $permissionId]);
    }
    $stmt = $pdo->prepare("INSERT INTO user_permissions (user_id, permission_id, is_granted, created_at) VALUES (?, ?, 1, NOW())");
    return $stmt->execute([$userId, $permissionId]);
}

function revokeUserPermission(PDO $pdo, int $userId, int $permissionId): bool {
    $check = $pdo->prepare("SELECT 1 FROM user_permissions WHERE user_id = ? AND permission_id = ?");
    $check->execute([$userId, $permissionId]);
    if ($check->fetchColumn()) {
        $update = $pdo->prepare("UPDATE user_permissions SET is_granted = 0 WHERE user_id = ? AND permission_id = ?");
        return $update->execute([$userId, $permissionId]);
    }
    $stmt = $pdo->prepare("INSERT INTO user_permissions (user_id, permission_id, is_granted, created_at) VALUES (?, ?, 0, NOW())");
    return $stmt->execute([$userId, $permissionId]);
}

function getUserPermissions(PDO $pdo, int $userId): array {
    $sql = "
        SELECT p.permission_key, p.description,
          COALESCE(up.is_granted, rp.granted, 0) as granted
        FROM permissions p
        LEFT JOIN user_permissions up ON up.permission_id = p.id AND up.user_id = :uid
        LEFT JOIN (
            SELECT rp.permission_id, MAX(rp.granted) AS granted
            FROM user_roles ur
            JOIN role_permissions rp ON ur.role_id = rp.role_id
            WHERE ur.user_id = :uid
            GROUP BY rp.permission_id
        ) rp ON rp.permission_id = p.id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserRoles(PDO $pdo, int $userId): array {
    $sql = "
        SELECT r.*
        FROM user_roles ur
        JOIN roles r ON ur.role_id = r.id
        WHERE ur.user_id = ?
        ORDER BY r.role_name
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllRoles(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM roles ORDER BY role_name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPermissionsGrouped(PDO $pdo): array {
    $sql = "
        SELECT pg.id AS group_id, pg.name AS group_name, p.id, p.permission_key, p.description
        FROM permission_groups pg
        LEFT JOIN permissions p ON p.group_id = pg.id
        ORDER BY pg.name, p.permission_key
    ";
    $stmt = $pdo->query($sql);
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $groups = [];
    foreach ($all as $row) {
        $gid = $row['group_id'] ?? 0;
        if (!isset($groups[$gid])) {
            $groups[$gid] = [
                'group_name' => $row['group_name'] ?? 'Ungrouped',
                'permissions' => [],
            ];
        }
        if ($row['id']) {
            $groups[$gid]['permissions'][] = [
                'id' => $row['id'],
                'permission_key' => $row['permission_key'],
                'description' => $row['description'],
            ];
        }
    }
    return array_values($groups);
}

function getSetting(PDO $pdo, string $key, $default = null) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    return $val !== false ? $val : $default;
}

function isAdmin(PDO $pdo): bool {
    // Example: user role stored in session or database
    // Adjust this to your actual role check logic.
    // For instance, check if user has 'admin' role:
    global $pdo;
    $userId = $_SESSION['user_id'] ?? 0;
    if (!$userId) return false;

    $stmt = $pdo->prepare("SELECT 1 FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ? AND r.role_name = 'admin' LIMIT 1");
    $stmt->execute([$userId]);
    return (bool) $stmt->fetchColumn();
}
function updateSetting(PDO $pdo, string $key, $value): bool {
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, updated_at)
        VALUES (:key, :value, NOW())
        ON DUPLICATE KEY UPDATE setting_value = :value, updated_at = NOW()");
    return $stmt->execute([
        ':key' => $key,
        ':value' => $value
    ]);
}
