<?php
require_once '../includes/auth.php';
authorize('admin');

require_once '../includes/db.php';
require_once '../includes/functions.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = trim($_POST['company_name'] ?? '');
    $logo_path = trim($_POST['logo_path'] ?? '');
    $footer_text = trim($_POST['footer_text'] ?? '');

    if (!$company_name) {
        $error = "Company name cannot be empty.";
    } else {
        updateSetting($pdo, 'company_name', $company_name);
        updateSetting($pdo, 'logo_path', $logo_path);
        updateSetting($pdo, 'footer_text', $footer_text);
        $success = "Settings updated successfully.";
    }
}

$current_company = getSetting($pdo, 'company_name') ?? '';
$current_logo = getSetting($pdo, 'logo_path') ?? '';
$current_footer = getSetting($pdo, 'footer_text') ?? '';

include '../includes/header.php';
?>

<div class="container">
  <h2>Site Settings</h2>
  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" action="">
    <div class="mb-3">
      <label for="company_name" class="form-label">Company Name</label>
      <input type="text" name="company_name" id="company_name" class="form-control" value="<?= htmlspecialchars($current_company) ?>" required>
    </div>
    <div class="mb-3">
      <label for="logo_path" class="form-label">Logo Path (relative to root)</label>
      <input type="text" name="logo_path" id="logo_path" class="form-control" value="<?= htmlspecialchars($current_logo) ?>">
      <small class="form-text text-muted">Example: assets/images/logo.png</small>
    </div>
    <div class="mb-3">
      <label for="footer_text" class="form-label">Footer Text</label>
      <textarea name="footer_text" id="footer_text" class="form-control" rows="3"><?= htmlspecialchars($current_footer) ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Save Settings</button>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
