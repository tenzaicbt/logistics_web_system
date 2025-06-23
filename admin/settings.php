<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$success = '';
$error = '';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'company_name', 'footer_text',
        'footer_contact_email', 'footer_contact_phone',
        'footer_address_line1', 'footer_address_line2',
        'footer_social_facebook', 'footer_social_twitter',
        'footer_social_linkedin', 'footer_social_instagram',
        'footer_shortcut_1_name', 'footer_shortcut_1_url',
        'footer_shortcut_2_name', 'footer_shortcut_2_url',
        'footer_shortcut_3_name', 'footer_shortcut_3_url'
    ];

    foreach ($fields as $field) {
        $value = trim($_POST[$field] ?? '');
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$field, $value]);
    }

    // Handle logo upload
    if (!empty($_FILES['site_logo']['name'])) {
        $file = $_FILES['site_logo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            $error = "Invalid logo file type.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = "Logo file too large. Max 2MB.";
        } else {
            $uploadDir = '../assets/images/';
            $newName = 'site_logo_' . time() . '.' . $ext;
            $target = $uploadDir . $newName;

            if (move_uploaded_file($file['tmp_name'], $target)) {
                $stmt = $pdo->prepare("
                    INSERT INTO settings (setting_key, setting_value)
                    VALUES ('site_logo', ?)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                $stmt->execute(['assets/images/' . $newName]);

                $success = "Settings updated including logo.";
                $_POST['site_logo'] = 'assets/images/' . $newName; // update preview immediately
            } else {
                $error = "Failed to upload logo.";
            }
        }
    } else {
        if (!$error) $success = "Settings updated successfully.";
    }
}

// Load settings without function
$settings = [];
$fields = [
    'company_name', 'footer_text', 'site_logo',
    'footer_contact_email', 'footer_contact_phone',
    'footer_address_line1', 'footer_address_line2',
    'footer_social_facebook', 'footer_social_twitter',
    'footer_social_linkedin', 'footer_social_instagram',
    'footer_shortcut_1_name', 'footer_shortcut_1_url',
    'footer_shortcut_2_name', 'footer_shortcut_2_url',
    'footer_shortcut_3_name', 'footer_shortcut_3_url'
];

$inClause = implode(',', array_fill(0, count($fields), '?'));
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($inClause)");
$stmt->execute($fields);
$result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

foreach ($fields as $field) {
    $settings[$field] = $result[$field] ?? '';
}

// If a new logo was just uploaded, use that immediately
if (!empty($_POST['site_logo'])) {
    $settings['site_logo'] = $_POST['site_logo'];
}

// Determine logo path for preview
$logo_path = (!empty($settings['site_logo']) && file_exists('../' . $settings['site_logo']))
    ? '../' . $settings['site_logo']
    : '../assets/images/default-logo.png';

$logo_url = htmlspecialchars($logo_path) . '?v=' . time(); // cache bust
?>

<div class="container my-5">
    <h2 class="mb-4 fw-bold">SETTINGS</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="row mb-4">
            <div class="col-md-8 mb-3">
                <label class="form-label">Company Name</label>
                <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($settings['company_name']) ?>" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Upload Logo</label><br>

                <img id="logoPreview" src="<?= $logo_url ?>" alt="Logo Preview" height="60" class="mb-2 d-block"><br>

                <input type="file" name="site_logo" class="form-control" id="logoInput" accept="image/*">
                <small class="text-muted">Allowed: png, jpg, jpeg, gif, webp. Max: 2MB</small>
            </div>
        </div>

        <!-- Footer info + Social + Shortcuts -->
        <div class="row">
            <div class="col-md-6">
                <h5>Footer Contact Info</h5>
                <div class="mb-3"><label>Email</label><input type="email" name="footer_contact_email" class="form-control" value="<?= htmlspecialchars($settings['footer_contact_email']) ?>"></div>
                <div class="mb-3"><label>Phone</label><input type="text" name="footer_contact_phone" class="form-control" value="<?= htmlspecialchars($settings['footer_contact_phone']) ?>"></div>
                <div class="mb-3"><label>Address Line 1</label><input type="text" name="footer_address_line1" class="form-control" value="<?= htmlspecialchars($settings['footer_address_line1']) ?>"></div>
                <div class="mb-3"><label>Address Line 2</label><input type="text" name="footer_address_line2" class="form-control" value="<?= htmlspecialchars($settings['footer_address_line2']) ?>"></div>
            </div>
            <div class="col-md-6">
                <h5>Social Links</h5>
                <div class="mb-3"><label>Facebook</label><input type="url" name="footer_social_facebook" class="form-control" value="<?= htmlspecialchars($settings['footer_social_facebook']) ?>"></div>
                <div class="mb-3"><label>Twitter</label><input type="url" name="footer_social_twitter" class="form-control" value="<?= htmlspecialchars($settings['footer_social_twitter']) ?>"></div>
                <div class="mb-3"><label>LinkedIn</label><input type="url" name="footer_social_linkedin" class="form-control" value="<?= htmlspecialchars($settings['footer_social_linkedin']) ?>"></div>
                <div class="mb-3"><label>Instagram</label><input type="url" name="footer_social_instagram" class="form-control" value="<?= htmlspecialchars($settings['footer_social_instagram']) ?>"></div>
            </div>
        </div>

        <h5 class="mt-4">Quick Links</h5>
        <div class="row">
            <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="col-md-6 mb-3">
                    <label>Name <?= $i ?></label>
                    <input type="text" name="footer_shortcut_<?= $i ?>_name" class="form-control" value="<?= htmlspecialchars($settings["footer_shortcut_{$i}_name"]) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label>URL <?= $i ?></label>
                    <input type="url" name="footer_shortcut_<?= $i ?>_url" class="form-control" value="<?= htmlspecialchars($settings["footer_shortcut_{$i}_url"]) ?>">
                </div>
            <?php endfor; ?>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="dashboard.php" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-danger">Save Settings</button>
        </div>
    </form>
</div>

<footer class="text-center py-3 mt-4">
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> NorthPort Logistics Pvt Ltd. All rights reserved.
    </div>
</footer>
