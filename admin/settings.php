<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$success = '';
$error = '';

// Define fields (footer shortcut fields removed)
$fields = [
    'company_name',
    'footer_text',
    'footer_contact_email',
    'footer_contact_phone',
    'footer_address_line1',
    'footer_address_line2'
];

// Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        INSERT INTO settings (setting_key, setting_value)
        VALUES (:key, :value)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
    ");
    foreach ($fields as $field) {
        $value = trim($_POST[$field] ?? '');
        $stmt->execute([':key' => $field, ':value' => $value]);
    }

    // Logo upload
    if (!empty($_FILES['site_logo']['name'])) {
        $file = $_FILES['site_logo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) {
            $error = "Invalid logo file type.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = "Logo too large. Max 2MB.";
        } else {
            $uploadDir = '../assets/images/';
            $newName = 'site_logo_' . time() . '.' . $ext;
            $target = $uploadDir . $newName;
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $stmt->execute([
                    ':key' => 'site_logo',
                    ':value' => 'assets/images/' . $newName
                ]);
                $success = "Settings saved with logo.";
                $_POST['site_logo'] = 'assets/images/' . $newName;
            } else {
                $error = "Logo upload failed.";
            }
        }
    } else {
        if (!$error) $success = "Settings saved successfully.";
    }
}

// Load settings from DB
$fields[] = 'site_logo';
$placeholders = implode(',', array_fill(0, count($fields), '?'));
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($placeholders)");
$stmt->execute($fields);
$result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$settings = [];
foreach ($fields as $f) {
    $settings[$f] = $result[$f] ?? '';
}
if (!empty($_POST['site_logo'])) {
    $settings['site_logo'] = $_POST['site_logo'];
}

$logo_path = (!empty($settings['site_logo']) && file_exists('../' . $settings['site_logo']))
    ? '../' . $settings['site_logo']
    : '../assets/images/default-logo.png';
$logo_url = htmlspecialchars($logo_path) . '?v=' . time();

// LOGS
function logAction($pdo, $userId, $action)
{
    $stmt = $pdo->prepare("INSERT INTO logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $action,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}
?>

<!-- Minimalist and Neat Form Style -->
<style>
    body, label, input, textarea, small, .form-label, .form-control, h2, h5, button, .btn {
        font-size: 0.85rem !important;
    }

h2 {
    font-size: 2rem !important;
    font-weight: 600;
    margin-bottom: 1rem;
}

    .btn {
        padding: 0.35rem 0.85rem;
    }

    .form-control {
        padding: 0.4rem 0.65rem;
    }

    .fw-bold {
        font-weight: 600 !important;
    }

    .alert {
        font-size: 0.85rem;
    }

    footer .footer-bottom {
        font-size: 0.75rem;
    }

        .btn {
        font-size: 0.8rem;
        padding: 0.25rem 0.75rem;
    }

    .btn-danger {
        background-color: #e30613;
        border: none;
    }

    .btn-danger:hover {
        background-color: #b6050e;
    }

    .btn-secondary {
        background-color: #666;
        border: none;
    }

    .btn-secondary:hover {
        background-color: #444;
    }
    .page-title {
    font-size: 2.5rem;
    font-weight: 700;
    text-transform: uppercase;
    color:rgb(37, 36, 36);
    margin-bottom: 1rem;
}

</style>

<div class="container my-5" style="position: relative;">
    <a href="information.php" title="System & Developer Info" 
       style="position: absolute; top: 0; right: 0; font-size: 1.8rem; color:rgb(236, 0, 0); text-decoration: none; cursor: pointer;">
       &#9410;
    </a>
    <h2 class="page-title">SETTIGNS</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Company Name</label>
                <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($settings['company_name']) ?>" required>
            </div>
            <div>
                <label class="form-label">Upload Logo</label>
                <img id="logoPreview" src="<?= $logo_url ?>" alt="Logo Preview" height="60" class="mb-2 d-block">
                <input type="file" name="site_logo" class="form-control" accept="image/*">
                <small class="text-muted">Allowed: png, jpg, jpeg, gif, webp. Max 2MB</small>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label">Contact Email</label>
                <input type="email" name="footer_contact_email" class="form-control" value="<?= htmlspecialchars($settings['footer_contact_email']) ?>">
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Contact Phone</label>
                <input type="text" name="footer_contact_phone" class="form-control" value="<?= htmlspecialchars($settings['footer_contact_phone']) ?>">
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Address Line 1</label>
                <input type="text" name="footer_address_line1" class="form-control" value="<?= htmlspecialchars($settings['footer_address_line1']) ?>">
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Address Line 2</label>
                <input type="text" name="footer_address_line2" class="form-control" value="<?= htmlspecialchars($settings['footer_address_line2']) ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Footer Text</label>
            <textarea name="footer_text" class="form-control" rows="3"><?= htmlspecialchars($settings['footer_text']) ?></textarea>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="dashboard.php" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-danger">Save Settings</button>
        </div>
    </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>