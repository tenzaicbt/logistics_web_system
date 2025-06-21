<?php
require_once '../includes/auth.php';
authorize('admin');

require_once '../includes/db.php';         // $pdo connection
require_once '../includes/functions.php';  // getSetting(), updateSetting()
include '../includes/header.php';

$success = '';
$error = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $company_name = trim($_POST['company_name'] ?? '');
  $footer_text = trim($_POST['footer_text'] ?? '');

  // Footer contact info
  $footer_contact_email = trim($_POST['footer_contact_email'] ?? '');
  $footer_contact_phone = trim($_POST['footer_contact_phone'] ?? '');
  $footer_address_line1 = trim($_POST['footer_address_line1'] ?? '');
  $footer_address_line2 = trim($_POST['footer_address_line2'] ?? '');

  // Footer social links
  $footer_social_facebook = trim($_POST['footer_social_facebook'] ?? '');
  $footer_social_twitter = trim($_POST['footer_social_twitter'] ?? '');
  $footer_social_linkedin = trim($_POST['footer_social_linkedin'] ?? '');
  $footer_social_instagram = trim($_POST['footer_social_instagram'] ?? '');

  // Footer shortcuts (3 sets)
  $footer_shortcut_1_name = trim($_POST['footer_shortcut_1_name'] ?? '');
  $footer_shortcut_1_url = trim($_POST['footer_shortcut_1_url'] ?? '');
  $footer_shortcut_2_name = trim($_POST['footer_shortcut_2_name'] ?? '');
  $footer_shortcut_2_url = trim($_POST['footer_shortcut_2_url'] ?? '');
  $footer_shortcut_3_name = trim($_POST['footer_shortcut_3_name'] ?? '');
  $footer_shortcut_3_url = trim($_POST['footer_shortcut_3_url'] ?? '');

  if (!$company_name) {
    $error = "Company name cannot be empty.";
  } else {
    // Update basic settings
    updateSetting($pdo, 'company_name', $company_name);
    updateSetting($pdo, 'footer_text', $footer_text);

    // Update footer contact info
    updateSetting($pdo, 'footer_contact_email', $footer_contact_email);
    updateSetting($pdo, 'footer_contact_phone', $footer_contact_phone);
    updateSetting($pdo, 'footer_address_line1', $footer_address_line1);
    updateSetting($pdo, 'footer_address_line2', $footer_address_line2);

    // Update social links
    updateSetting($pdo, 'footer_social_facebook', $footer_social_facebook);
    updateSetting($pdo, 'footer_social_twitter', $footer_social_twitter);
    updateSetting($pdo, 'footer_social_linkedin', $footer_social_linkedin);
    updateSetting($pdo, 'footer_social_instagram', $footer_social_instagram);

    // Update footer shortcuts
    updateSetting($pdo, 'footer_shortcut_1_name', $footer_shortcut_1_name);
    updateSetting($pdo, 'footer_shortcut_1_url', $footer_shortcut_1_url);
    updateSetting($pdo, 'footer_shortcut_2_name', $footer_shortcut_2_name);
    updateSetting($pdo, 'footer_shortcut_2_url', $footer_shortcut_2_url);
    updateSetting($pdo, 'footer_shortcut_3_name', $footer_shortcut_3_name);
    updateSetting($pdo, 'footer_shortcut_3_url', $footer_shortcut_3_url);

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
          updateSetting($pdo, 'site_logo', 'assets/images/' . $newName);
          $success = "Settings updated including logo.";
        } else {
          $error = "Failed to upload logo.";
        }
      }
    } else {
      if (!$error) $success = "Settings updated successfully.";
    }
  }
}

// Load current settings for form values
$current_company = getSetting($pdo, 'company_name') ?? '';
$current_footer = getSetting($pdo, 'footer_text') ?? '';
$current_logo = getSetting($pdo, 'site_logo') ?? '';

$current_footer_contact_email = getSetting($pdo, 'footer_contact_email') ?? '';
$current_footer_contact_phone = getSetting($pdo, 'footer_contact_phone') ?? '';
$current_footer_address_line1 = getSetting($pdo, 'footer_address_line1') ?? '';
$current_footer_address_line2 = getSetting($pdo, 'footer_address_line2') ?? '';

$current_footer_social_facebook = getSetting($pdo, 'footer_social_facebook') ?? '';
$current_footer_social_twitter = getSetting($pdo, 'footer_social_twitter') ?? '';
$current_footer_social_linkedin = getSetting($pdo, 'footer_social_linkedin') ?? '';
$current_footer_social_instagram = getSetting($pdo, 'footer_social_instagram') ?? '';

$current_footer_shortcut_1_name = getSetting($pdo, 'footer_shortcut_1_name') ?? '';
$current_footer_shortcut_1_url = getSetting($pdo, 'footer_shortcut_1_url') ?? '';
$current_footer_shortcut_2_name = getSetting($pdo, 'footer_shortcut_2_name') ?? '';
$current_footer_shortcut_2_url = getSetting($pdo, 'footer_shortcut_2_url') ?? '';
$current_footer_shortcut_3_name = getSetting($pdo, 'footer_shortcut_3_name') ?? '';
$current_footer_shortcut_3_url = getSetting($pdo, 'footer_shortcut_3_url') ?? '';
?>

<style>
  .btn-northport {
    background-color: #e30613;
    border: none;
    color: #fff;
    font-weight: 600;
  }

  .btn-northport:hover {
    background-color: #b6050e;
  }
</style>

<div class="container my-5">
    <h2 class="mb-4 fw-bold">SETTINGS</h2>
        <div class="row g-2 mb-4"></div>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <!-- Company name & logo full width -->
    <div class="row mb-4">
      <div class="col-12 col-md-8 mb-3">
        <label for="company_name" class="form-label">Company Name</label>
        <input type="text" name="company_name" id="company_name" class="form-control" value="<?= htmlspecialchars($current_company) ?>" required>
      </div>
      <div class="col-12 col-md-8">
        <label for="site_logo" class="form-label">Upload Logo</label><br>
        <?php if ($current_logo): ?>
          <img src="../<?= htmlspecialchars($current_logo) ?>" alt="Current Logo" height="60" class="mb-2"><br>
        <?php endif; ?>
        <input type="file" name="site_logo" id="site_logo" class="form-control">
        <small class="form-text text-muted">Allowed: png, jpg, jpeg, gif, webp. Max: 2MB</small>
      </div>
    </div>


    <!-- Footer Contact Info & Social Links side-by-side -->
    <div class="row">
      <div class="col-12 col-md-6">
        <h5>Footer Contact Info</h5>
        <div class="mb-3">
          <label for="footer_contact_email" class="form-label">Contact Email</label>
          <input type="email" name="footer_contact_email" id="footer_contact_email" class="form-control" value="<?= htmlspecialchars($current_footer_contact_email) ?>">
        </div>
        <div class="mb-3">
          <label for="footer_contact_phone" class="form-label">Contact Phone</label>
          <input type="text" name="footer_contact_phone" id="footer_contact_phone" class="form-control" value="<?= htmlspecialchars($current_footer_contact_phone) ?>">
        </div>
        <div class="mb-3">
          <label for="footer_address_line1" class="form-label">Address Line 1</label>
          <input type="text" name="footer_address_line1" id="footer_address_line1" class="form-control" value="<?= htmlspecialchars($current_footer_address_line1) ?>">
        </div>
        <div class="mb-3">
          <label for="footer_address_line2" class="form-label">Address Line 2</label>
          <input type="text" name="footer_address_line2" id="footer_address_line2" class="form-control" value="<?= htmlspecialchars($current_footer_address_line2) ?>">
        </div>
      </div>

      <div class="col-12 col-md-6">
        <h5>Footer Social Links</h5>
        <div class="mb-3">
          <label for="footer_social_facebook" class="form-label">Facebook URL</label>
          <input type="url" name="footer_social_facebook" id="footer_social_facebook" class="form-control" value="<?= htmlspecialchars($current_footer_social_facebook) ?>">
        </div>
        <div class="mb-3">
          <label for="footer_social_twitter" class="form-label">Twitter URL</label>
          <input type="url" name="footer_social_twitter" id="footer_social_twitter" class="form-control" value="<?= htmlspecialchars($current_footer_social_twitter) ?>">
        </div>
        <div class="mb-3">
          <label for="footer_social_linkedin" class="form-label">LinkedIn URL</label>
          <input type="url" name="footer_social_linkedin" id="footer_social_linkedin" class="form-control" value="<?= htmlspecialchars($current_footer_social_linkedin) ?>">
        </div>
        <div class="mb-3">
          <label for="footer_social_instagram" class="form-label">Instagram URL</label>
          <input type="url" name="footer_social_instagram" id="footer_social_instagram" class="form-control" value="<?= htmlspecialchars($current_footer_social_instagram) ?>">
        </div>
      </div>
    </div>

    <!-- Footer shortcuts - optional, uncomment to use -->
    <!--
    <h5 class="mt-4">Footer Quick Links (Shortcuts)</h5>
    <div class="row">
      <?php for ($i = 1; $i <= 3; $i++): ?>
        <div class="col-12 col-md-6 mb-3">
          <label for="footer_shortcut_<?= $i ?>_name" class="form-label">Shortcut <?= $i ?> Name</label>
          <input type="text" name="footer_shortcut_<?= $i ?>_name" id="footer_shortcut_<?= $i ?>_name" class="form-control" value="<?= htmlspecialchars(${'current_footer_shortcut_' . $i . '_name'}) ?>">
        </div>
        <div class="col-12 col-md-6 mb-3">
          <label for="footer_shortcut_<?= $i ?>_url" class="form-label">Shortcut <?= $i ?> URL</label>
          <input type="url" name="footer_shortcut_<?= $i ?>_url" id="footer_shortcut_<?= $i ?>_url" class="form-control" value="<?= htmlspecialchars(${'current_footer_shortcut_' . $i . '_url'}) ?>">
        </div>
      <?php endfor; ?>
    </div>
    -->

    <div class="mb-3 d-flex justify-content-between mt-4">
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