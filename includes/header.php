<?php
// includes/header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Base URL for your project folder in XAMPP
$base_url = '/northport/';

// Default logo path (relative to project root)
$default_logo = 'assets/images/default-logo.png';

// Get logo path from DB or use default
$logo_path = getSetting($pdo, 'logo_path') ?? $default_logo;

// Full server path to check if file exists
$full_logo_path = __DIR__ . '/../' . $logo_path;

// If file does not exist, fallback to default logo
if (!file_exists($full_logo_path)) {
    $logo_path = $default_logo;
}

// Build full URL for img src
$logo_url = $base_url . $logo_path;

$company_name = getSetting($pdo, 'company_name') ?? 'NorthPort Logistics Pvt Ltd';

$username = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($company_name) ?><?= $username ? ' | ' . ucfirst($role) : '' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
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
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="<?= $base_url ?>">
      <img src="<?= htmlspecialchars($logo_url) ?>" alt="Logo" />
      <!-- <span class="ms-2 fw-bold text-primary"><?= htmlspecialchars($company_name) ?></span> -->
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <?php if ($username): ?>
          <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" href="<?= $base_url . $role ?>/dashboard.php">Dashboard</a>
          </li>

          <?php if ($role === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>admin/manage_users.php">Users</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>admin/manage_bookings.php">Bookings</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>admin/settings.php">Settings</a></li>
          <?php elseif ($role === 'sub-admin'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>admin/manage_bookings.php">Bookings</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>admin/role_permissions.php">Permissions</a></li>
          <?php elseif ($role === 'user'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>user/book_shipment.php">Book</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>user/track_shipment.php">Track</a></li>
          <?php endif; ?>

          <li class="nav-item">
            <a class="nav-link" href="<?= $base_url ?>logout.php">Logout (<?= htmlspecialchars($username) ?>)</a>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container my-4">
