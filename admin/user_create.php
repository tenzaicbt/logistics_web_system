<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
// require_once '../includes/header.php';

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
  $notes            = trim($_POST['notes'] ?? '');

  $role             = 'user';
  $user_role        = 'user';
  $is_active        = 1;
  $role_id          = null;

  // Validate preferences as JSON if provided
  $preferences = null;
  if ($preferences_raw !== '') {
    $json = json_decode($preferences_raw, true);
    if (json_last_error() === JSON_ERROR_NONE) {
      $preferences = json_encode($json);
    } else {
      $errors[] = "Preferences field must be valid JSON.";
    }
  }

  // Basic validation
  if (!$username) $errors[] = "Username is required.";
  if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
  if (!$password) $errors[] = "Password is required.";
  if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

  // Duplicate check
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
      $username,
      $email,
      $password_hash,
      $role,
      $is_active,
      $phone ?: null,
      $street_address ?: null,
      $city ?: null,
      $state ?: null,
      $postal_code ?: null,
      $country ?: null,
      $company_name ?: null,
      $profile_pic ?: null,
      $date_of_birth ?: null,
      $user_role,
      $preferences,
      $notes ?: null,
      $role_id
    ]);

    $_SESSION['success'] = "Registration successful. Please log in.";
    header("Location: login.php");
    exit;
  }
}
?>

<link rel="stylesheet" href="/northport/assets/css/animations.css">
<script src="/northport/assets/js/animations.js" defer></script>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Register - NorthPort</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

    body {
      background-color: #fff;
      font-family: 'Inter', sans-serif;
      color: #222;
      font-size: 0.85rem;
      /* Base small text */
    }

    .login-container {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 2rem;
    }

    .logo {
      display: block;
      margin-bottom: 1rem;
      max-height: 60px;
    }

    h3 {
      text-align: center;
      color: #e30613;
      font-weight: 700;
      margin-bottom: 1.2rem;
      font-size: 1.1rem;
      /* Smaller heading */
    }

    .form-label {
      font-weight: 600;
      font-size: 0.75rem;
      margin-bottom: 0.2rem;
    }

    input.form-control,
    textarea.form-control,
    select.form-control {
      font-size: 0.8rem;
      padding: 0.3rem 0.5rem;
      border-radius: 4px;
      margin-bottom: 0.75rem;
    }

    textarea.form-control {
      font-size: 0.8rem;
    }

    .btn-primary {
      background-color: transparent;
      border: 2px solid #e30613;
      color: #e30613;
      font-weight: 600;
      font-size: 0.8rem;
      padding: 0.4rem;
      transition: all 0.25s ease-in-out;
    }

    .btn-primary:hover,
    .btn-primary:focus {
      background-color: #b6050e;
      border-color: #b6050e;
      color: white;
    }

    a {
      color: #cc0612;
      font-size: 0.75rem;
    }

    a:hover {
      color: #b6050e;
      text-decoration: underline;
    }

    .alert {
      font-size: 0.8rem;
      padding: 0.5rem 0.75rem;
      max-width: 720px;
      margin: 0 auto 1rem auto;
    }

    small {
      font-size: 0.75rem;
    }
  </style>
</head>

<body>

  <div class="login-container">

    <h3>REGISTER NEW USER</h3>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="container my-2">
      <form method="post" novalidate>
        <div class="row g-4">

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
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">

            <label class="form-label">Street Address</label>
            <input type="text" name="street_address" class="form-control" value="<?= htmlspecialchars($_POST['street_address'] ?? '') ?>">

            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">State</label>
            <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($_POST['state'] ?? '') ?>">

            <label class="form-label">Postal Code</label>
            <input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>">

            <label class="form-label">Country</label>
            <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($_POST['country'] ?? '') ?>">

            <label class="form-label">Company Name</label>
            <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>">

            <label class="form-label">Profile Picture URL</label>
            <input type="text" name="profile_pic" class="form-control" value="<?= htmlspecialchars($_POST['profile_pic'] ?? '') ?>">

            <label class="form-label">Date of Birth</label>
            <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>">

            <label class="form-label">Preferences </label>
            <textarea name="preferences" rows="3" class="form-control"><?= htmlspecialchars($_POST['preferences'] ?? '') ?></textarea>

            <!-- <label class="form-label">Notes</label>
          <textarea name="notes" rows="3" class="form-control"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea> -->
          </div>

        </div>

        <button type="submit" class="btn btn-primary w-100 mt-3">Register</button>
      </form>

      <div class="mt-3 text-center">
        <small><a href="manage_users.php">Back</a></small>
      </div>
    </div>
  </div>
</body>
</html>

<footer class="text-center py-3 mt-2">
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> NorthPort Logistics Pvt Ltd. All rights reserved.
    </div>
</footer>
