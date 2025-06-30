<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/db.php';

$base_url = '/northport/';
$default_logo = 'assets/images/default-logo.png';

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

function safeOutput($str)
{
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= safeOutput($company_name) ?><?= $username ? ' | ' . ucfirst($role) : '' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
    html {
      font-size: 14px;
    }

    .navbar-brand img {
      height: 36px;
      object-fit: contain;
    }

    .nav-link {
      font-weight: 600;
      font-size: 0.85rem;
    }

    .nav-link.active {
      font-weight: bold;
      color: #e30613 !important;
    }

    .nav-link:hover {
      color: #c80010 !important;
    }

    .theme-toggle-btn {
      border: none;
      background: transparent;
      font-size: 1.2rem;
      cursor: pointer;
      margin-left: 10px;
    }

    .navbar-light {
      background-color: #ffffff !important;
    }

    .navbar-dark {
      background-color: #212529 !important;
    }

    .navbar-dark .nav-link,
    .navbar-dark .navbar-brand,
    .navbar-dark .dropdown-toggle {
      color: #f8f9fa !important;
    }

    .notification-bell {
      position: relative;
      font-size: 1.2rem;
      margin-left: 10px;
      color: #dc3545;
    }

    .notification-bell .badge {
      position: absolute;
      top: -4px;
      right: -8px;
      font-size: 0.65rem;
    }

    .dropdown-menu-notifications {
      min-width: 250px;
      max-height: 300px;
      overflow-y: auto;
      font-size: 0.85rem;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg shadow-sm py-2 transition">
    <div class="container">
      <a class="navbar-brand" href="<?= safeOutput($base_url) ?>">
        <img src="<?= safeOutput($logo_url) ?>" alt="Logo" />
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
        aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <?php if ($username): ?>
            <?php if ($role === 'admin'): ?>
              <li class="nav-item"><a class="nav-link <?= $current_file === 'dashboard.php' ? 'active' : '' ?>" href="<?= $base_url ?>admin/dashboard.php">Dashboard</a></li>
              <li class="nav-item"><a class="nav-link <?= $current_file === 'manage_users.php' ? 'active' : '' ?>" href="<?= $base_url ?>admin/manage_users.php">Users</a></li>
              <li class="nav-item"><a class="nav-link <?= $current_file === 'manage_bookings.php' ? 'active' : '' ?>" href="<?= $base_url ?>admin/manage_bookings.php">Bookings</a></li>
              <li class="nav-item"><a class="nav-link <?= $current_file === 'settings.php' ? 'active' : '' ?>" href="<?= $base_url ?>admin/settings.php">Settings</a></li>
            <?php elseif ($role === 'sub-admin'): ?>
              <li class="nav-item"><a class="nav-link <?= $current_file === 'manage_bookings.php' ? 'active' : '' ?>" href="<?= $base_url ?>admin/manage_bookings.php">Bookings</a></li>
              <li class="nav-item"><a class="nav-link <?= $current_file === 'role_permissions.php' ? 'active' : '' ?>" href="<?= $base_url ?>admin/role_permissions.php">Permissions</a></li>
            <?php elseif ($role === 'user'): ?>
              <li class="nav-item"><a class="nav-link <?= $current_file === 'book_shipment.php' ? 'active' : '' ?>" href="<?= $base_url ?>user/book_shipment.php">Book</a></li>
              <li class="nav-item"><a class="nav-link <?= $current_file === 'track_shipment.php' ? 'active' : '' ?>" href="<?= $base_url ?>user/track_shipment.php">Track</a></li>
            <?php endif; ?>


            <li class="nav-item">
              <a class="nav-link" href="<?= $base_url ?>logout.php">Logout (<?= safeOutput($username) ?>)</a>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link <?= $current_file === 'login.php' ? 'active' : '' ?>" href="<?= $base_url ?>login.php">Login</a></li>
            <li class="nav-item"><a class="nav-link <?= $current_file === 'register.php' ? 'active' : '' ?>" href="<?= $base_url ?>register.php">Register</a></li>
          <?php endif; ?>
        </ul>

        <!-- Dark Mode Toggle -->
        <?php if ($role !== 'user'): ?>
          <!-- Dark Mode Toggle -->
          <button id="toggleTheme" class="theme-toggle-btn" title="Toggle Light/Dark Mode">🌓</button>
        <?php endif; ?>

      </div>
    </div>
  </nav>

  <main class="container my-4">

    <script>
      const htmlTag = document.documentElement;
      const navbar = document.querySelector('nav.navbar');
      const toggleBtn = document.getElementById('toggleTheme');

      function applyTheme(theme) {
        htmlTag.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);
        navbar.classList.toggle('navbar-dark', theme === 'dark');
        navbar.classList.toggle('navbar-light', theme === 'light');
      }

      toggleBtn.addEventListener('click', () => {
        const current = htmlTag.getAttribute('data-bs-theme');
        const newTheme = current === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
      });

      document.addEventListener('DOMContentLoaded', () => {
        const saved = localStorage.getItem('theme') || 'light';
        applyTheme(saved);
      });
    </script>