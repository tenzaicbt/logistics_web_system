<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Path where logos are stored
$logo_folder = __DIR__ . '/../assets/uploads/';
$default_logo = 'site_logo.jpg';

try {
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'site_logo' LIMIT 1");
    $stmt->execute();
    $site_logo = $stmt->fetchColumn();

    // Check file exists; fallback if not
    if (!$site_logo || !file_exists($logo_folder . $site_logo)) {
        $site_logo = $default_logo;
    }
} catch (Exception $e) {
    $site_logo = $default_logo;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Northport Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/css/custom.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="/admin/dashboard.php">
      <img src="/assets/uploads/<?= htmlspecialchars($site_logo) ?>" alt="Site Logo" height="40" style="object-fit: contain; margin-right: 8px;">
      Northport Admin
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <?php if (isset($_SESSION['user_name'])): ?>
          <li class="nav-item me-3">
            <span class="nav-link text-light">Welcome, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
          </li>
          <li class="nav-item">
            <a href="/auth/logout.php" class="btn btn-outline-light btn-sm">Logout <i class="bi bi-box-arrow-right"></i></a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a href="/auth/login.php" class="btn btn-outline-light btn-sm">Login</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4">
