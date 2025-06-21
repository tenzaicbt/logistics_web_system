<?php
// includes/header.php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$base_url = '/northport/';
$default_logo = 'assets/images/default-logo.png';

$logo_path = getSetting($pdo, 'site_logo') ?: getSetting($pdo, 'logo_path') ?: $default_logo;
$full_logo_path = __DIR__ . '/../' . $logo_path;
if (!file_exists($full_logo_path)) {
  $logo_path = $default_logo;
}
$logo_url = $base_url . $logo_path . '?v=' . time();

$company_name = getSetting($pdo, 'company_name') ?? 'NorthPort Logistics Pvt Ltd';
$username = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;
$current_file = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($company_name) ?><?= $username ? ' | ' . ucfirst($role) : '' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .navbar-brand img {
      height: 40px;
      object-fit: contain;
    }
    .nav-link.active {
      font-weight: bold;
      color: #e30613 !important;
    }
    footer {
      font-size: 0.9rem;
    }
    .nav-link {
      color: #cc0612;
      font-weight: 600;
      transition: color 0.3s ease;
    }
    .nav-link:hover,
    .nav-link:focus {
      color: #e30613;
      text-decoration: none !important;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="<?= $base_url ?>">
        <img src="<?= htmlspecialchars($logo_url) ?>" alt="Logo" />
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <?php if ($username): ?>
            <li class="nav-item">
              <a class="nav-link <?= $current_file === 'dashboard.php' ? 'active' : '' ?>" href="<?= $base_url . $role ?>/dashboard.php">Dashboard</a>
            </li>

            <?php if ($role === 'admin'): ?>
              <li class="nav-item">
                <a class="nav-link <?= $current_file === 'manage_users.php' ? 'active' : '' ?>" href="<?= $base_url ?>admin/manage_users.php">Users</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $current_file === 'manage_bookings.php' ? 'active' : '' ?>" href="<?= $base_url ?>admin/manage_bookings.php">Bookings</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $current_file === 'settings.php' ? 'active' : '' ?>" href="<?= $base_url ?>admin/settings.php">Settings</a>
              </li>
            <?php elseif ($role === 'sub-admin'): ?>
              <li class="nav-item">
                <a class="nav-link <?= $current_file === 'manage_bookings.php' ? 'active' : '' ?>" href="<?= $base_url ?>admin/manage_bookings.php">Bookings</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $current_file === 'role_permissions.php' ? 'active' : '' ?>" href="<?= $base_url ?>admin/role_permissions.php">Permissions</a>
              </li>
            <?php elseif ($role === 'user'): ?>
              <li class="nav-item">
                <a class="nav-link <?= $current_file === 'book_shipment.php' ? 'active' : '' ?>" href="<?= $base_url ?>user/book_shipment.php">Book</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $current_file === 'track_shipment.php' ? 'active' : '' ?>" href="<?= $base_url ?>user/track_shipment.php">Track</a>
              </li>
            <?php endif; ?>

            <li class="nav-item">
              <a class="nav-link <?= $current_file === 'logout.php' ? 'active' : '' ?>" href="<?= $base_url ?>logout.php">Logout (<?= htmlspecialchars($username) ?>)</a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link <?= $current_file === 'login.php' ? 'active' : '' ?>" href="<?= $base_url ?>login.php">Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_file === 'register.php' ? 'active' : '' ?>" href="<?= $base_url ?>register.php">Register</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container my-4">
