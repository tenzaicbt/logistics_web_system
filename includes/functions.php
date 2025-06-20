<?php
// includes/functions.php
function getSetting(PDO $pdo, string $key): ?string {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetchColumn();
    return $result === false ? null : $result;
}

function updateSetting(PDO $pdo, string $key, string $value): bool {
    // Check if exists
    $stmt = $pdo->prepare("SELECT 1 FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        return $stmt->execute([$value, $key]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        return $stmt->execute([$key, $value]);
    }
}
