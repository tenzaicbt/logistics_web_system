<?php
require_once '../includes/auth.php';
authorize(['admin', 'sub-admin']);

require_once '../includes/header.php'; // includes full HTML header + <body>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - NorthPort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">NorthPort Admin</a>
    <div class="d-flex">
      <span class="navbar-text me-3">Hello, <?=htmlspecialchars($_SESSION['username'])?></span>
      <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container my-4">
  <h1 class="mb-4">Admin Dashboard</h1>

  <div class="row">
    <!-- Manage Users Card -->
    <div class="col-md-4">
      <div class="card text-white bg-primary mb-3 shadow-sm">
        <div class="card-header fw-semibold">Users</div>
        <div class="card-body">
          <h5 class="card-title">Manage Users</h5>
          <p class="card-text">Add, edit, or remove system users.</p>
          <a href="manage_users.php" class="btn btn-light btn-sm">Go</a>
        </div>
      </div>
    </div>

    <!-- Manage Bookings Card -->
    <div class="col-md-4">
      <div class="card text-white bg-success mb-3 shadow-sm">
        <div class="card-header fw-semibold">Bookings</div>
        <div class="card-body">
          <h5 class="card-title">Manage Bookings</h5>
          <p class="card-text">Approve, track, or cancel shipments.</p>
          <a href="manage_bookings.php" class="btn btn-light btn-sm">Go</a>
        </div>
      </div>
    </div>

    <!-- Manage Fleet Card -->
    <div class="col-md-4">
      <div class="card text-white bg-info mb-3 shadow-sm">
        <div class="card-header fw-semibold">Fleet</div>
        <div class="card-body">
          <h5 class="card-title">Manage Fleet & Containers</h5>
          <p class="card-text">Add trucks, vessels, containers.</p>
          <a href="manage_fleet.php" class="btn btn-light btn-sm">Go</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>