<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

// Stats
$totalUsers      = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalShipments  = $pdo->query("SELECT COUNT(*) FROM shipments")->fetchColumn();
$totalRevenue    = $pdo->query("SELECT SUM(amount) FROM payments WHERE status='Paid'")->fetchColumn() ?: 0;
$totalContainers = $pdo->query("SELECT COUNT(*) FROM containers")->fetchColumn();

$fleetStatusStmt = $pdo->query("SELECT status, COUNT(*) as count FROM containers GROUP BY status");
$fleetStatus     = $fleetStatusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Recent Data
$recentUsers = $pdo->query("SELECT username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentShipments = $pdo->query("SELECT shipment_id, status, created_at FROM shipments ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentPayments = $pdo->query("
  SELECT p.amount, p.currency, p.payment_method, p.status, p.paid_at, u.username 
  FROM payments p 
  LEFT JOIN users u ON p.user_id = u.id 
  ORDER BY p.created_at DESC LIMIT 5
")->fetchAll();
$recentLogs = $pdo->query("
  SELECT l.action, l.ip_address, l.created_at, u.username 
  FROM logs l 
  LEFT JOIN users u ON l.user_id = u.id 
  ORDER BY l.created_at DESC LIMIT 5
")->fetchAll();
$recentDocs = [];
try {
  $recentDocs = $pdo->query("
    SELECT d.document_type, d.file_path, d.uploaded_at, u.username
    FROM user_documents d
    LEFT JOIN users u ON d.user_id = u.id
    ORDER BY d.uploaded_at DESC LIMIT 5
  ")->fetchAll();
} catch (PDOException $e) {
  // Skip documents section if table is missing
}
?>

<style>
  .card-small {
    padding: 0.75rem 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    border-radius: 6px;
  }
  .card-small .h4 {
    font-size: 1.2rem;
    font-weight: 700;
  }
</style>

<div class="container my-5">

  <h2 class="mb-4 fw-bold">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>

  <div class="row g-3 mb-4">
    <?php
    $stats = [
      ['label' => 'Total Users', 'value' => $totalUsers],
      ['label' => 'Total Shipments', 'value' => $totalShipments],
      ['label' => 'Total Revenue', 'value' => '$' . number_format($totalRevenue, 2)],
      ['label' => 'Total Containers', 'value' => $totalContainers]
    ];
    foreach ($stats as $s): ?>
      <div class="col-md-3 col-6">
        <div class="card card-small text-center">
          <div class="text-muted small"><?= $s['label'] ?></div>
          <div class="h4"><?= $s['value'] ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Fleet status -->
  <div class="row g-3 mb-4">
    <?php
    $statuses = ['Available', 'In Use', 'Under Maintenance', 'Damaged'];
    foreach ($statuses as $status): ?>
      <div class="col-md-3 col-6">
        <div class="card card-small text-center">
          <div class="text-muted small"><?= $status ?></div>
          <div class="h4"><?= $fleetStatus[$status] ?? 0 ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Management cards -->
  <div class="row g-3 mb-5">
    <?php
    $actions = [
      ['title' => 'Manage Users', 'desc' => 'Create, update or delete system users.', 'href' => 'manage_users.php'],
      ['title' => 'Manage Bookings', 'desc' => 'Handle and approve bookings.', 'href' => 'manage_bookings.php'],
      ['title' => 'Manage Fleet', 'desc' => 'Update fleet and container data.', 'href' => 'manage_fleet.php'],
      ['title' => 'Settings', 'desc' => 'System and branding options.', 'href' => 'settings.php']
    ];
    foreach ($actions as $a): ?>
      <div class="col-md-3">
        <div class="card h-100 p-3 shadow-sm">
          <h6 class="fw-bold"><?= $a['title'] ?></h6>
          <p class="text-muted small flex-grow-1"><?= $a['desc'] ?></p>
          <a href="<?= $a['href'] ?>" class="btn btn-outline-secondary btn-sm mt-auto">Access</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-4">
    <!-- Recent Users -->
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Recent Users</div>
        <ul class="list-group list-group-flush">
          <?php foreach ($recentUsers as $user): ?>
            <li class="list-group-item d-flex justify-content-between">
              <div><strong><?= $user['username'] ?></strong><br><small><?= $user['email'] ?></small></div>
              <small><?= date('M d, Y', strtotime($user['created_at'])) ?></small>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <!-- Recent Shipments -->
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Recent Shipments</div>
        <ul class="list-group list-group-flush">
          <?php foreach ($recentShipments as $ship): ?>
            <li class="list-group-item d-flex justify-content-between">
              <div><strong>#<?= $ship['shipment_id'] ?></strong><br><small>Status: <?= $ship['status'] ?></small></div>
              <small><?= date('M d, Y', strtotime($ship['created_at'])) ?></small>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <div class="row g-4 mt-3">
    <!-- Payments -->
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Recent Payments</div>
        <ul class="list-group list-group-flush">
          <?php foreach ($recentPayments as $pay): ?>
            <li class="list-group-item d-flex justify-content-between">
              <div><strong><?= $pay['username'] ?? 'Unknown' ?></strong><br>
                <small><?= $pay['payment_method'] ?> - <?= $pay['status'] ?></small>
              </div>
              <div class="text-end">
                <div><?= number_format($pay['amount'], 2) . ' ' . $pay['currency'] ?></div>
                <small><?= $pay['paid_at'] ? date('M d, Y', strtotime($pay['paid_at'])) : 'Unpaid' ?></small>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <!-- Logs -->
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Recent Logs</div>
        <ul class="list-group list-group-flush">
          <?php foreach ($recentLogs as $log): ?>
            <li class="list-group-item d-flex justify-content-between">
              <div><strong><?= $log['username'] ?? 'Unknown' ?></strong><br><small><?= $log['action'] ?></small></div>
              <small><?= date('M d, Y', strtotime($log['created_at'])) ?></small>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <!-- Recent Docs (only if available) -->
  <?php if (!empty($recentDocs)): ?>
    <div class="row g-4 mt-3">
      <div class="col-lg-12">
        <div class="card shadow-sm">
          <div class="card-header fw-bold">Recent Uploaded Documents</div>
          <ul class="list-group list-group-flush">
            <?php foreach ($recentDocs as $doc): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div><strong><?= $doc['document_type'] ?></strong> by <?= $doc['username'] ?? 'Unknown' ?></div>
                <div class="text-end">
                  <a href="../uploads/<?= $doc['file_path'] ?>" target="_blank" class="me-2">View</a>
                  <small><?= date('M d, Y', strtotime($doc['uploaded_at'])) ?></small>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>

<!-- <footer class="text-center mt-5 text-muted small">
  &copy; <?= date('Y') ?> NorthPort Logistics Pvt Ltd. All rights reserved.
</footer> -->

<?php require_once '../includes/admin_footer.php'; ?>