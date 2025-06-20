<?php
function get_site_settings($pdo) {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    $rows = $stmt->fetchAll();
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function update_setting($pdo, $key, $value) {
    $stmt = $pdo->prepare("
        INSERT INTO site_settings (setting_key, setting_value)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$key, $value]);
}

function generateTrackingNumber() {
    return 'NP' . strtoupper(bin2hex(random_bytes(5))); // e.g. NP8F3C4D2A9
}

?>