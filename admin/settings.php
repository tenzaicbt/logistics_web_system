<?php
require_once '../includes/auth_check.php';
requireRole([ROLE_ADMIN]);

require_once '../config/db.php';
require_once '../includes/functions.php';

$settings = get_site_settings($pdo);
$site_name = $settings['site_name'] ?? 'Northport Shipping';
$footer_text = $settings['footer_text'] ?? '© ' . date('Y') . ' Northport Logistics. All rights reserved.';
$site_logo = $settings['site_logo'] ?? 'northport-logo.png';
$success = false;
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['site_name'] ?? '');
    $new_footer = trim($_POST['footer_text'] ?? '');

    // Update settings
    update_setting($pdo, 'site_name', $new_name);
    update_setting($pdo, 'footer_text', $new_footer);
    $site_name = $new_name;
    $footer_text = $new_footer;

    // Handle logo upload
    if (!empty($_FILES['site_logo']['name'])) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed_ext)) {
            $filename = 'logo_' . time() . '.' . $ext;
            $target_path = "../assets/uploads/" . $filename;

            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $target_path)) {
                update_setting($pdo, 'site_logo', $filename);
                $site_logo = $filename;
                $success = true;
            } else {
                $error = "⚠️ Failed to upload logo.";
            }
        } else {
            $error = "❌ Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    } else {
        $success = true;
    }
}

include '../includes/header.php';
?>

<h2 class="mb-4">⚙ Site Settings</h2>

<?php if ($success): ?>
    <div class="alert alert-success">✅ Settings updated successfully!</div>
<?php elseif ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="site_name" class="form-label">Site Name</label>
        <input type="text" name="site_name" id="site_name" value="<?= htmlspecialchars($site_name) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="site_logo" class="form-label">Site Logo</label><br>
        <img src="/northport/assets/uploads/<?= htmlspecialchars($site_logo) ?>" alt="Logo" height="50" class="mb-2 border rounded"><br>
        <input type="file" name="site_logo" id="site_logo" class="form-control">
        <small class="text-muted">Allowed: JPG, PNG, GIF</small>
    </div>

    <div class="mb-3">
        <label for="footer_text" class="form-label">Footer Text</label>
        <input type="text" name="footer_text" id="footer_text" value="<?= htmlspecialchars($footer_text) ?>" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">💾 Save Changes</button>
</form>

<?php include '../includes/footer.php'; ?>
