<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/db.php';

$base_url = '/northport/';
$default_logo = 'assets/images/default-logo.png';

// Load settings
$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $settings[$row['setting_key']] = $row['setting_value'];
}

$logo_path = $settings['site_logo'] ?? $settings['logo_path'] ?? $default_logo;
$logo_full_path = realpath(__DIR__ . '/../' . $logo_path);
if (!$logo_full_path || !file_exists($logo_full_path)) {
  $logo_path = $default_logo;
}

$logo_url = $base_url . $logo_path . '?v=' . time();
$company_name = $settings['company_name'] ?? 'NorthPort Logistics Pvt Ltd';

$username = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;
$current_file = basename($_SERVER['PHP_SELF']);

function safeOutput($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Dashboard URL by role
$dashboard_link = '#';
if ($role === 'admin') $dashboard_link = $base_url . 'admin/dashboard.php';
elseif ($role === 'manager') $dashboard_link = $base_url . 'admin/dashboard.php';
elseif ($role === 'employer') $dashboard_link = $base_url . 'admin/dashboard.php';

// Show back button if not on dashboard
$show_back_button = in_array($role, ['admin', 'manager', 'employer']) && $current_file !== 'dashboard.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= safeOutput($company_name) ?><?= $username ? ' | ' . ucfirst($role) : '' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    html { font-size: 14px; }
    .navbar-brand img { height: 36px; object-fit: contain; }
    .nav-link { font-weight: 600; font-size: 0.85rem; }
    .nav-link.active { font-weight: bold; color: #e30613 !important; }
    .nav-link:hover { color: #c80010 !important; }
    .navbar-light { background-color: #ffffff !important; }
    .navbar-dark { background-color: #212529 !important; }
    .navbar-dark .nav-link, .navbar-dark .navbar-brand, .navbar-dark .dropdown-toggle {
      color: #f8f9fa !important;
    }
  </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm py-2">
  <div class="container">
    <!-- Logo link to role-based dashboard -->
    <a class="navbar-brand d-flex align-items-center" href="<?= safeOutput($dashboard_link) ?>">
      <img src="<?= safeOutput($logo_url) ?>" alt="Logo" />
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
      aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

        <!-- Back Button -->
        <?php if ($show_back_button): ?>
          <li class="nav-item">
            <a class="nav-link" href="javascript:history.back()">
              Back
            </a>
          </li>
        <?php endif; ?>

        <?php if ($username): ?>
          <?php if ($role === 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link <?= $current_file === 'dashboard.php' ? 'active' : '' ?>"
                href="<?= safeOutput($base_url) ?>admin/dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_file === 'manage_users.php' ? 'active' : '' ?>"
                href="<?= safeOutput($base_url) ?>admin/manage_users.php">Users</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_file === 'manage_bookings.php' ? 'active' : '' ?>"
                href="<?= safeOutput($base_url) ?>admin/manage_bookings.php">Bookings</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_file === 'settings.php' ? 'active' : '' ?>"
                href="<?= safeOutput($base_url) ?>admin/settings.php">Settings</a>
            </li>
          <?php elseif ($role === 'manager'): ?>
            <li class="nav-item">
              <a class="nav-link <?= $current_file === 'dashboard.php' ? 'active' : '' ?>"
                href="<?= safeOutput($base_url) ?>admin/dashboard.php">Dashboard</a>
            </li>
            <!-- Add more manager links as needed -->
          <?php elseif ($role === 'employer'): ?>
            <li class="nav-item">
              <a class="nav-link <?= $current_file === 'dashboard.php' ? 'active' : '' ?>"
                href="<?= safeOutput($base_url) ?>admin/dashboard.php">Dashboard</a>
            </li>
            <!-- Add more employer links as needed -->
          <?php endif; ?>

          <li class="nav-item">
            <a class="nav-link" href="<?= safeOutput($base_url) ?>logout.php">
              Logout (<?= safeOutput($username) ?>)
            </a>
          </li>

        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link <?= $current_file === 'login.php' ? 'active' : '' ?>"
              href="<?= safeOutput($base_url) ?>login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current_file === 'register.php' ? 'active' : '' ?>"
              href="<?= safeOutput($base_url) ?>register.php">Register</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container my-4">
