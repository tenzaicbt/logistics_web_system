<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/settings.php';

// === Load logo ===
$default_logo = 'assets/images/default-logo.png';
$logo_path = get_setting('site_logo') ?: get_setting('logo_path') ?: $default_logo;
$full_path = __DIR__ . '/' . $logo_path;
if (!file_exists($full_path)) {
    $logo_path = $default_logo;
}
$logo = $logo_path . '?v=' . time(); // Cache bust

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username         = trim($_POST['username'] ?? '');
  $email            = trim($_POST['email'] ?? '');
  $password         = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  // Optional fields
  $phone            = trim($_POST['phone'] ?? '');
  $street_address   = trim($_POST['street_address'] ?? '');
  $city             = trim($_POST['city'] ?? '');
  $state            = trim($_POST['state'] ?? '');
  $postal_code      = trim($_POST['postal_code'] ?? '');
  $country          = trim($_POST['country'] ?? '');
  $company_name     = trim($_POST['company_name'] ?? '');
  $profile_pic      = trim($_POST['profile_pic'] ?? '');
  $date_of_birth    = $_POST['date_of_birth'] ?? null;
  $preferences_raw  = trim($_POST['preferences'] ?? '');
  $preferences      = $preferences_raw ? json_encode($preferences_raw) : null;
  $notes            = trim($_POST['notes'] ?? '');

  $role             = 'user';
  $user_role        = 'user';
  $is_active        = 1;
  $role_id          = null;

  // Validation
  if (!$username) $errors[] = "Username is required.";
  if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
  if (!$password) $errors[] = "Password is required.";
  if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

  // Check if user exists
  if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetchColumn() > 0) {
      $errors[] = "Username or email already exists.";
    }
  }

  // Insert user
  if (empty($errors)) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (
      username, email, password_hash, role, is_active, created_at, updated_at,
      phone, street_address, city, state, postal_code, country,
      company_name, profile_pic, date_of_birth, user_role, preferences, notes, role_id
    ) VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
      $username, $email, $password_hash, $role, $is_active,
      $phone, $street_address, $city, $state, $postal_code, $country,
      $company_name, $profile_pic, $date_of_birth, $user_role,
      $preferences, $notes, $role_id
    ]);

    $_SESSION['success'] = "Registration successful. Please log in.";
    header("Location: login.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - NorthPort</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

    body {
      background-color: #ffffff;
      font-family: 'Inter', sans-serif;
      color: #222222;
    }

    .form-label {
      font-weight: 600;
      font-size: 0.9rem;
    }

    .form-control {
      font-size: 0.9rem;
      padding: 0.45rem 0.75rem;
      margin-bottom: 1rem;
    }

    .btn-danger {
      background-color: transparent;
      border: 2px solid #e30613;
      color: #e30613;
      font-weight: 600;
      transition: all 0.25s ease-in-out;
    }

    .btn-danger:hover {
      background-color: #b6050e;
      color: white;
      border-color: #b6050e;
    }

    .logo {
      display: block;
      margin: 0 auto 1rem auto;
      max-height: 60px;
    }

    .alert {
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="mx-auto" style="max-width: 900px;">

      <?php if ($logo): ?>
        <img src="<?= htmlspecialchars($logo) ?>" alt="Logo" class="logo mb-3">
      <?php endif; ?>

      <h3 class="text-center mb-4">Register Account</h3>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Username *</label>
            <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">

            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" required>

            <label class="form-label">Confirm Password *</label>
            <input type="password" name="confirm_password" class="form-control" required>

            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control">

            <label class="form-label">Street Address</label>
            <input type="text" name="street_address" class="form-control">

            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">State</label>
            <input type="text" name="state" class="form-control">

            <label class="form-label">Postal Code</label>
            <input type="text" name="postal_code" class="form-control">

            <label class="form-label">Country</label>
            <input type="text" name="country" class="form-control">

            <label class="form-label">Company Name</label>
            <input type="text" name="company_name" class="form-control">

            <label class="form-label">Profile Picture URL</label>
            <input type="text" name="profile_pic" class="form-control">

            <label class="form-label">Date of Birth</label>
            <input type="date" name="date_of_birth" class="form-control">

            <label class="form-label">Preferences</label>
            <textarea name="preferences" class="form-control" rows="2"></textarea>

            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
          </div>
        </div>

        <button type="submit" class="btn btn-danger w-100 mt-4">Register</button>
      </form>

      <div class="mt-3 text-center">
        <small>Already registered? <a href="login.php">Login here</a></small>
      </div>
    </div>
  </div>
</body>
</html>
