<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
include '../includes/header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username         = trim($_POST['username'] ?? '');
  $email            = trim($_POST['email'] ?? '');
  $password         = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  $role             = $_POST['role'] ?? 'user';

  $phone            = trim($_POST['phone'] ?? '');
  $street_address   = trim($_POST['street_address'] ?? '');
  $city             = trim($_POST['city'] ?? '');
  $state            = trim($_POST['state'] ?? '');
  $postal_code      = trim($_POST['postal_code'] ?? '');
  $country          = trim($_POST['country'] ?? '');
  $company_name     = trim($_POST['company_name'] ?? '');
  $profile_pic      = trim($_POST['profile_pic'] ?? '');
  $date_of_birth    = $_POST['date_of_birth'] ?? null;
  $notes            = trim($_POST['notes'] ?? '');
  $department       = trim($_POST['department'] ?? '');
  $job_title        = trim($_POST['job_title'] ?? '');
  $date_of_joining  = $_POST['date_of_joining'] ?? null;
  $nic_passport     = trim($_POST['nic_passport_number'] ?? '');
  $is_active        = 1;

  if (!$username) $errors[] = "Username is required.";
  if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
  if (!$password) $errors[] = "Password is required.";
  if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

  if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetchColumn() > 0) {
      $errors[] = "Username or email already exists.";
    }
  }

  if (empty($errors)) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (
        username, email, password_hash, role, is_active, created_at, updated_at,
        phone, street_address, city, state, postal_code, country,
        company_name, profile_pic, date_of_birth, notes,
        department, job_title, date_of_joining, nic_passport_number
    ) VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
      $notes ?: null,
      $department ?: null,
      $job_title ?: null,
      $date_of_joining ?: null,
      $nic_passport ?: null
    ]);
    $_SESSION['success'] = "Registration successful.";
    header("Location: manage_users.php");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register User - NorthPort</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #fff;
      font-family: 'Inter', sans-serif;
      color: #222;
      font-size: 0.85rem;
    }

    .form-label, .form-control, .form-select, .btn {
      font-size: 0.85rem;
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

    .container {
      font-size: 0.90rem;
      max-width: 960px;
    }

    h3 {
      color: #e30613;
      font-weight: bold;
      margin-bottom: 1.5rem;
      font-size: 1.25rem;
    }

    .section-title {
      font-weight: 600;
      font-size: 0.9rem;
      margin: 1rem 0 0.5rem;
    }

    .alert {
      font-size: 0.8rem;
    }
  </style>
</head>

<body>
<div class="container my-5">
    <div class="mb-4 fw-bold">
        <h2 class="fw-bold">User Register</h2>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="section-title">Account Information</div>
      <div class="row g-3">
        <div class="col-md-4"><input name="username" class="form-control" placeholder="Username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"></div>
        <div class="col-md-4"><input type="email" name="email" class="form-control" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
        <div class="col-md-2"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
        <div class="col-md-2"><input type="password" name="confirm_password" class="form-control" placeholder="Confirm" required></div>
      </div>

      <div class="row g-3 mt-2">
        <div class="col-md-4">
          <select name="role" class="form-select" required>
            <option value="">-- Select Role --</option>
            <?php foreach (['admin', 'manager', 'employer', 'user'] as $r): ?>
              <option value="<?= $r ?>" <?= (($_POST['role'] ?? 'user') === $r ? 'selected' : '') ?>><?= ucfirst($r) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4"><input name="phone" class="form-control" placeholder="Phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"></div>
        <div class="col-md-4"><input name="street_address" class="form-control" placeholder="Street Address" value="<?= htmlspecialchars($_POST['street_address'] ?? '') ?>"></div>
      </div>

      <div class="section-title">Location Details</div>
      <div class="row g-3">
        <div class="col-md-3"><input name="city" class="form-control" placeholder="City" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"></div>
        <div class="col-md-3"><input name="state" class="form-control" placeholder="State" value="<?= htmlspecialchars($_POST['state'] ?? '') ?>"></div>
        <div class="col-md-3"><input name="postal_code" class="form-control" placeholder="Postal Code" value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>"></div>
        <div class="col-md-3"><input name="country" class="form-control" placeholder="Country" value="<?= htmlspecialchars($_POST['country'] ?? '') ?>"></div>
      </div>

      <div class="section-title">Employment Details</div>
      <div class="row g-3">
        <div class="col-md-4"><input name="company_name" class="form-control" placeholder="Company Name" value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>"></div>
        <div class="col-md-4"><input name="department" class="form-control" placeholder="Department" value="<?= htmlspecialchars($_POST['department'] ?? '') ?>"></div>
        <div class="col-md-4"><input name="job_title" class="form-control" placeholder="Job Title" value="<?= htmlspecialchars($_POST['job_title'] ?? '') ?>"></div>
      </div>

      <div class="row g-3 mt-2">
        <div class="col-md-4"><input type="date" name="date_of_joining" class="form-control" value="<?= htmlspecialchars($_POST['date_of_joining'] ?? '') ?>"></div>
        <div class="col-md-4"><input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>"></div>
        <div class="col-md-4"><input name="nic_passport_number" class="form-control" placeholder="NIC / Passport No." value="<?= htmlspecialchars($_POST['nic_passport_number'] ?? '') ?>"></div>
      </div>

      <div class="section-title">Other Details</div>
      <div class="row g-3 mb-3">
        <div class="col-md-6"><input name="profile_pic" class="form-control" placeholder="Profile Picture URL" value="<?= htmlspecialchars($_POST['profile_pic'] ?? '') ?>"></div>
        <div class="col-md-6"><textarea name="notes" class="form-control" placeholder="Notes"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea></div>
      </div>

      <div class="mt-4 d-flex justify-content-between">
        <a href="manage_users.php" class="btn btn-secondary">Back</a>
        <button type="submit" class="btn btn-danger">Register</button>
      </div>
    </form>
  </div>

  <?php require_once '../includes/admin_footer.php'; ?>
</body>
</html>
