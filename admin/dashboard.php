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

// Get current logged in user role
$currentRole = $_SESSION['role'] ?? 'user';

// Define management cards visible only to manager role
$actions = [
  ['title' => 'Manage Users', 'desc' => 'Create, update or delete system users.', 'href' => 'manage_users.php', 'roles' => ['admin']], // Admin only
  ['title' => 'Manage Bookings', 'desc' => 'Handle and approve bookings.', 'href' => 'manage_bookings.php', 'roles' => ['admin', 'manager', 'employer', 'user']],
  ['title' => 'Manage Fleet', 'desc' => 'Update fleet and container data.', 'href' => 'manage_fleet.php', 'roles' => ['admin', 'manager', 'employer', 'user']],
  ['title' => 'Settings', 'desc' => 'System and branding options.', 'href' => 'settings.php', 'roles' => ['admin']],
  
  // These three only for manager
  ['title' => 'Employee Leave', 'desc' => 'Manage employee leave requests.', 'href' => 'employee_leave.php', 'roles' => ['manager']],
  ['title' => 'Bank Account Details', 'desc' => 'View and update bank account info.', 'href' => 'bank_accounts.php', 'roles' => ['manager']],
  ['title' => 'Attendance', 'desc' => 'Manage attendance (optional integration).', 'href' => 'attendance.php', 'roles' => ['manager']],
];
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
          <div class="text-muted small"><?= htmlspecialchars($s['label']) ?></div>
          <div class="h4"><?= htmlspecialchars($s['value']) ?></div>
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
          <div class="text-muted small"><?= htmlspecialchars($status) ?></div>
          <div class="h4"><?= htmlspecialchars($fleetStatus[$status] ?? 0) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Management cards -->
  <div class="row g-3 mb-5">
    <?php foreach ($actions as $a): ?>
      <?php if (in_array($currentRole, $a['roles'])): ?>
        <div class="col-md-3">
          <div class="card h-100 p-3 shadow-sm">
            <h6 class="fw-bold"><?= htmlspecialchars($a['title']) ?></h6>
            <p class="text-muted small flex-grow-1"><?= htmlspecialchars($a['desc']) ?></p>
            <a href="<?= htmlspecialchars($a['href']) ?>" class="btn btn-outline-secondary btn-sm mt-auto">Access</a>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <!-- Recent Users -->
  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Recent Users</div>
        <ul class="list-group list-group-flush">
          <?php foreach ($recentUsers as $user): ?>
            <li class="list-group-item d-flex justify-content-between">
              <div><strong><?= htmlspecialchars($user['username']) ?></strong><br><small><?= htmlspecialchars($user['email']) ?></small></div>
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
              <div><strong>#<?= htmlspecialchars($ship['shipment_id']) ?></strong><br><small>Status: <?= htmlspecialchars($ship['status']) ?></small></div>
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
              <div><strong><?= htmlspecialchars($pay['username'] ?? 'Unknown') ?></strong><br>
                <small><?= htmlspecialchars($pay['payment_method']) ?> - <?= htmlspecialchars($pay['status']) ?></small>
              </div>
              <div class="text-end">
                <div><?= number_format($pay['amount'], 2) . ' ' . htmlspecialchars($pay['currency']) ?></div>
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
              <div><strong><?= htmlspecialchars($log['username'] ?? 'Unknown') ?></strong><br><small><?= htmlspecialchars($log['action']) ?></small></div>
              <small><?= date('M d, Y', strtotime($log['created_at'])) ?></small>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <!-- Recent Docs (if available) -->
  <?php if (!empty($recentDocs)): ?>
    <div class="row g-4 mt-3">
      <div class="col-lg-12">
        <div class="card shadow-sm">
          <div class="card-header fw-bold">Recent Uploaded Documents</div>
          <ul class="list-group list-group-flush">
            <?php foreach ($recentDocs as $doc): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div><strong><?= htmlspecialchars($doc['document_type']) ?></strong> by <?= htmlspecialchars($doc['username'] ?? 'Unknown') ?></div>
                <div class="text-end">
                  <a href="../uploads/<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="me-2">View</a>
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

<?php require_once '../includes/admin_footer.php'; ?>
