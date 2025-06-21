<?php
require_once '../includes/auth.php';
authorize(['admin', 'sub-admin']);
require_once '../includes/db.php';
require_once '../includes/header.php';

// Fetch Dashboard Stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalShipments = $pdo->query("SELECT COUNT(*) FROM shipments")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE status='Paid'")->fetchColumn() ?: 0;
$totalContainers = $pdo->query("SELECT COUNT(*) FROM containers")->fetchColumn();

// Fleet Status Summary (container status counts)
$stmt = $pdo->query("SELECT status, COUNT(*) AS count FROM containers GROUP BY status");
$fleetStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // ['Available' => x, 'In Use' => y, ...]

// Recent Users
$recentUsers = $pdo->query("SELECT username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent Shipments
$recentShipments = $pdo->query("SELECT shipment_id, status, created_at FROM shipments ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent Payments
$recentPayments = $pdo->query("
  SELECT p.amount, p.currency, p.payment_method, p.status, p.paid_at, u.username 
  FROM payments p 
  LEFT JOIN users u ON p.user_id = u.id 
  ORDER BY p.created_at DESC LIMIT 5
")->fetchAll();

// Recent Logs
$recentLogs = $pdo->query("
  SELECT l.action, l.ip_address, l.created_at, u.username 
  FROM logs l 
  LEFT JOIN users u ON l.user_id = u.id 
  ORDER BY l.created_at DESC LIMIT 5
")->fetchAll();

// Recent User Documents
$recentDocs = $pdo->query("
  SELECT d.document_type, d.file_path, d.uploaded_at, u.username
  FROM user_documents d
  LEFT JOIN users u ON d.user_id = u.id
  ORDER BY d.uploaded_at DESC LIMIT 5
")->fetchAll();
?>
<style>
  /* Smaller card style */
  .card-small {
    padding: 0.5rem 1rem !important;
    /* less padding */
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
    /* lighter shadow */
    border-radius: 4px;
  }

  .card-small .text-muted {
    font-size: 0.7rem;
  }

  .card-small .h3 {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
  }
</style>

<div class="container my-5">

  <h2 class="mb-4 fw-bold">WELCOME, <?= htmlspecialchars($_SESSION['username']) ?></h2>

  <div class="row g-2 mb-4">
    <?php
    $stats = [
      ['label' => 'Total Users', 'value' => $totalUsers],
      ['label' => 'Total Shipments', 'value' => $totalShipments],
      ['label' => 'Total Revenue (Paid)', 'value' => '$' . number_format($totalRevenue, 2)],
      ['label' => 'Total Containers', 'value' => $totalContainers]
    ];
    foreach ($stats as $stat): ?>
      <div class="col-md-3 col-6">
        <div class="card border-0 card-small text-center">
          <div class="text-muted small"><?= htmlspecialchars($stat['label']) ?></div>
          <div class="h3 fw-bold"><?= htmlspecialchars($stat['value']) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Fleet Status Breakdown -->
  <div class="row g-2 mb-4">
    <?php
    $allStatuses = ['Available', 'In Use', 'Under Maintenance', 'Damaged'];
    foreach ($allStatuses as $status):
      $count = $fleetStatus[$status] ?? 0;
    ?>
      <div class="col-md-3 col-6">
        <div class="card border-0 card-small text-center">
          <div class="text-muted small"><?= htmlspecialchars($status) ?></div>
          <div class="h3 fw-bold"><?= htmlspecialchars($count) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Management Links -->
  <div class="row g-3 mb-5">
    <?php
    $links = [
      ['title' => 'Manage Users', 'desc' => 'Create, update or delete system users.', 'href' => 'manage_users.php'],
      ['title' => 'Manage Bookings', 'desc' => 'Handle and approve bookings.', 'href' => 'manage_bookings.php'],
      ['title' => 'Manage Fleet & Containers', 'desc' => 'Update fleet & container status.', 'href' => 'manage_fleet.php'],
      ['title' => 'System Settings', 'desc' => 'Logo, footer, email settings.', 'href' => 'settings.php']
    ];
    foreach ($links as $link): ?>
      <div class="col-md-3">
        <div class="card h-100 shadow-sm p-3 d-flex flex-column">
          <h6 class="fw-bold"><?= $link['title'] ?></h6>
          <p class="text-muted small flex-grow-1"><?= $link['desc'] ?></p>
          <a href="<?= $link['href'] ?>" class="btn btn-outline-secondary btn-sm mt-auto">Access</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Recent Activity Rows -->
  <div class="row g-4">

    <!-- Recent Users -->
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Recent Users</div>
        <ul class="list-group list-group-flush">
          <?php if ($recentUsers): ?>
            <?php foreach ($recentUsers as $user): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong><?= htmlspecialchars($user['username']) ?></strong> <br>
                  <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                </div>
                <small class="text-muted"><?= date('M d, Y H:i', strtotime($user['created_at'])) ?></small>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="list-group-item text-muted">No recent users found.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <!-- Recent Shipments -->
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Recent Shipments</div>
        <ul class="list-group list-group-flush">
          <?php if ($recentShipments): ?>
            <?php foreach ($recentShipments as $ship): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong>#<?= htmlspecialchars($ship['shipment_id']) ?></strong><br>
                  <small class="text-muted">Status: <?= htmlspecialchars($ship['status']) ?></small>
                </div>
                <small class="text-muted"><?= date('M d, Y H:i', strtotime($ship['created_at'])) ?></small>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="list-group-item text-muted">No recent shipments found.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

  </div>

  <div class="row g-4 mt-3">

    <!-- Recent Payments -->
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Recent Payments</div>
        <ul class="list-group list-group-flush">
          <?php if ($recentPayments): ?>
            <?php foreach ($recentPayments as $pay): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong><?= htmlspecialchars($pay['username'] ?? 'Unknown') ?></strong> <br>
                  <small class="text-muted"><?= htmlspecialchars($pay['payment_method']) ?> — <?= htmlspecialchars($pay['status']) ?></small>
                </div>
                <div class="text-end">
                  <div><?= number_format($pay['amount'], 2) . ' ' . htmlspecialchars($pay['currency']) ?></div>
                  <small class="text-muted"><?= $pay['paid_at'] ? date('M d, Y H:i', strtotime($pay['paid_at'])) : 'Not paid yet' ?></small>
                </div>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="list-group-item text-muted">No recent payments.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <!-- Recent Logs -->
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Recent Logs</div>
        <ul class="list-group list-group-flush">
          <?php if ($recentLogs): ?>
            <?php foreach ($recentLogs as $log): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong><?= htmlspecialchars($log['username'] ?? 'Unknown') ?></strong> <br>
                  <small class="text-muted"><?= htmlspecialchars($log['action']) ?></small>
                </div>
                <small class="text-muted"><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></small>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="list-group-item text-muted">No recent logs.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

  </div>

  <!-- Recent Uploaded Documents -->
  <div class="row g-4 mt-3">
    <div class="col-lg-12">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Recent Uploaded Documents</div>
        <ul class="list-group list-group-flush">
          <?php if ($recentDocs): ?>
            <?php foreach ($recentDocs as $doc): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong><?= htmlspecialchars($doc['document_type']) ?></strong> by <em><?= htmlspecialchars($doc['username'] ?? 'Unknown') ?></em>
                </div>
                <div class="text-end">
                  <a href="../uploads/<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="me-3">View</a>
                  <small class="text-muted"><?= date('M d, Y H:i', strtotime($doc['uploaded_at'])) ?></small>
                </div>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="list-group-item text-muted">No recent uploaded documents.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>

</div>

<footer class="text-center py-3">
  <div class="footer-bottom">
    &copy; <?= date('Y') ?> NorthPort Logistics Pvt Ltd. All rights reserved.
  </div>
</footer>
