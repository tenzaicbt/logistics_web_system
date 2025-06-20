<?php
require_once '../includes/auth.php';
authorize('sub-admin'); // Only sub-admin allowed

require_once '../includes/header.php'; // Contains full HTML head, nav, and <body>
?>

<div class="container my-4">
  <h1 class="mb-3">Sub-Admin Dashboard</h1>
  <p class="text-muted">Manage assigned modules and monitor shipments.</p>

  <div class="row">
    <!-- Manage Bookings -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <h5 class="card-title">Manage Bookings</h5>
          <p class="card-text">View, approve, or update shipment bookings assigned to you.</p>
          <a href="../admin/manage_bookings.php" class="btn btn-warning w-100">Go to Bookings</a>
        </div>
      </div>
    </div>

    <!-- Role Permissions -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <h5 class="card-title">Role Permissions</h5>
          <p class="card-text">Edit access rights and module visibility for sub-admins.</p>
          <a href="../admin/role_permissions.php" class="btn btn-secondary w-100">Edit Permissions</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
