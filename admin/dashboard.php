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

$currentRole = $_SESSION['role'] ?? 'user';

$actions = [
  // ['title' => 'Employee Leave', 'desc' => 'Manage employee leave requests.', 'href' => 'employee_leave.php', 'roles' => ['manager']],
  ['title' => 'Manage Bookings', 'desc' => 'Handle and approve bookings.', 'href' => 'manage_bookings.php', 'roles' => ['admin', 'manager']],
  ['title' => 'Manage Fleet', 'desc' => 'Update fleet data.', 'href' => 'manage_fleet.php', 'roles' => ['admin', 'manager']],
  ['title' => 'Manage Shipments', 'desc' => 'View, edit and shipment data.', 'href' => 'manage_shipments.php', 'roles' => ['admin', 'manager', '']],
  ['title' => 'Manage Containers', 'desc' => 'View, edit and container dat.', 'href' => 'manage_containers.php', 'roles' => ['admin', 'manager', '']],
  ['title' => 'Manage Users', 'desc' => 'Create, update or delete system users.', 'href' => 'manage_users.php', 'roles' => ['admin']],
  ['title' => 'Manage Leave', 'desc' => 'Review and manage employee leave requests.', 'href' => 'manage_leaves.php', 'roles' => ['admin', 'manager']],
  ['title' => 'Bank Account Details', 'desc' => 'View and update bank account info.', 'href' => 'bank_accounts.php', 'roles' => ['manager']],
  ['title' => 'Attendance', 'desc' => 'Manage attendance (optional integration).', 'href' => 'manage_attendance.php', 'roles' => ['manager']],
  ['title' => 'Manage Paysheets', 'desc' => 'Manage employeer paysheets.', 'href' => 'manage_paysheets.php', 'roles' => ['admin', '']],
  ['title' => 'Attendance', 'desc' => 'View employeers attendance.', 'href' => 'view_attendance.php', 'roles' => ['admin']],
  ['title' => 'Manage Notifications', 'desc' => 'Manage admin notifications.', 'href' => 'admin_message.php', 'roles' => ['admin', 'manager']],
  ['title' => 'Employee Leave ', 'desc' => 'Create and manage leave for employees.', 'href' => 'employee_leave.php', 'roles' => ['employer']],
  ['title' => 'Paysheets Details ', 'desc' => 'Monthly paysheets details.', 'href' => 'employer_paysheets.php', 'roles' => ['employer']],
  ['title' => 'Bank Details ', 'desc' => 'Add Bank details and more ', 'href' => 'add_bank_details.php', 'roles' => ['employer']],
];

// Stats
$totalUsers      = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalShipments  = $pdo->query("SELECT COUNT(*) FROM shipments")->fetchColumn();
$totalRevenue    = $pdo->query("SELECT SUM(amount) FROM payments WHERE status='Paid'")->fetchColumn() ?: 0;
$totalContainers = $pdo->query("SELECT COUNT(*) FROM containers")->fetchColumn();

$fleetStatusStmt = $pdo->query("SELECT status, COUNT(*) as count FROM containers GROUP BY status");
$fleetStatus     = $fleetStatusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Admin Messages: get only Pending and count
$newMessageCount = $pdo->query("SELECT COUNT(*) FROM admin_messages WHERE status = 'Pending'")->fetchColumn();

$pendingMessages = $pdo->prepare("SELECT id, name, subject, created_at FROM admin_messages WHERE status = 'Pending' ORDER BY created_at DESC LIMIT 5");
$pendingMessages->execute();
$pendingMessages = $pendingMessages->fetchAll();

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

  .notification-bell {
    position: relative;
    font-size: 1.5rem;
    cursor: pointer;
    color: #e30613;
    user-select: none;
  }

  .notification-count {
    position: absolute;
    top: -5px;
    right: -8px;
    background-color: red;
    color: white;
    font-size: 0.6rem;
    padding: 2px 6px;
    border-radius: 50%;
    font-weight: bold;
  }

  /* Popup box */
  .notification-popup {
    position: absolute;
    right: 0;
    margin-top: 10px;
    width: 320px;
    max-height: 350px;
    overflow-y: auto;
    background: white;
    border: 1px solid #ddd;
    border-radius: 6px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    z-index: 9999;
    display: none;
  }

  .notification-popup.show {
    display: block;
  }

  .notification-popup-header {
    padding: 8px 12px;
    border-bottom: 1px solid #eee;
    font-weight: 600;
    font-size: 0.95rem;
    background-color: #f8f9fa;
    color: #333;
  }

  .notification-item {
    padding: 10px 12px;
    border-bottom: 1px solid #eee;
    font-size: 0.85rem;
  }

  .notification-item:last-child {
    border-bottom: none;
  }

  .notification-item a {
    color: #e30613;
    font-weight: 600;
    text-decoration: none;
  }

  .notification-item a:hover {
    text-decoration: underline;
  }

  .notification-item .subject {
    font-weight: 500;
  }

  .notification-item small {
    color: #666;
  }

  .btn {
    font-size: 0.8rem;
    padding: 0.25rem 0.75rem;
  }

  .btn-danger {
    background-color: #e30613;
    border: none;
  }

  .btn-danger:hover {
    background-color: #b6050e;
  }

  .btn-secondary {
    background-color: #666;
    border: none;
  }

  .btn-secondary:hover {
    background-color: #444;
  }

  canvas#shipmentsChart {
    min-height: 140px;
  }

  canvas {
    min-height: 200px;
  }
</style>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4 position-relative">
    <h2 class="mb-4 fw-bold">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>

    <?php if (in_array($_SESSION['role'], ['admin', 'manager'])): ?>
      <div id="notificationBellWrapper" style="position: relative;">
        <i class="fas fa-bell notification-bell" id="notificationBell" title="Pending Messages" style="cursor:pointer;"></i>
        <?php if ($newMessageCount > 0): ?>
          <span class="notification-count" id="notificationCount"><?= $newMessageCount ?></span>
        <?php endif; ?>

        <div class="notification-popup" id="notificationPopup" aria-live="polite" aria-label="Pending messages notifications" style="display:none;">
          <div class="notification-popup-header p-2 border-bottom fw-bold">Pending Messages (<?= $newMessageCount ?>)</div>
          <?php if ($newMessageCount == 0): ?>
            <div class="notification-item p-2">No pending messages.</div>
          <?php else: ?>
            <?php foreach ($pendingMessages as $msg): ?>
              <div class="notification-item p-2 border-bottom">
                <div><strong><?= htmlspecialchars($msg['name']) ?></strong></div>
                <div class="subject text-truncate"><?= htmlspecialchars($msg['subject'] ?: 'No subject') ?></div>
                <small><?= date('d M Y, h:i A', strtotime($msg['created_at'])) ?></small><br>
                <a href="admin_message.php#msg<?= $msg['id'] ?>">View message</a>
              </div>
            <?php endforeach; ?>
            <?php if ($newMessageCount > 5): ?>
              <div class="notification-item p-2 text-center">
                <a href="admin_message.php">View all pending messages</a>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div> <!-- notificationPopup -->
      </div> <!-- notificationBellWrapper -->
    <?php endif; ?>
  </div> <!-- d-flex container -->

  <!-- Dashboard Stats -->
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

  <!-- Fleet Status -->
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

  <!-- Three‑Chart Row (Small Versions) -->
  <div class="row g-4 mb-5">
    <!-- 1) Shipments (Last 7 Days) – thin bar -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-header fw-bold">Shipments (Last 7 Days)</div>
        <div class="card-body p-2">
          <canvas
            id="shipmentsChart"
            style="width:100%; height:60px; display:block;">
          </canvas>
        </div>
      </div>
    </div>

    <!-- 2) Bookings vs Shipments – small doughnut -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-header fw-bold">Bookings vs Shipments</div>
        <div class="card-body d-flex justify-content-center align-items-center p-2">
          <canvas
            id="bookingsShipmentsChart"
            style="width:100px; height:100px;">
          </canvas>
        </div>
      </div>
    </div>

    <!-- 3) Fleet vs Containers – small doughnut -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-header fw-bold">Fleet vs Containers</div>
        <div class="card-body d-flex justify-content-center align-items-center p-2">
          <canvas
            id="fleetContainersChart"
            style="width:100px; height:100px;">
          </canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Management Cards -->
  <div class="row g-3 mb-5">
    <?php foreach ($actions as $a): ?>
      <?php if (in_array($currentRole, $a['roles'])): ?>
        <div class="col-md-3">
          <div class="card h-100 p-3 shadow-sm d-flex flex-column">
            <h6 class="fw-bold"><?= htmlspecialchars($a['title']) ?></h6>
            <p class="text-muted small flex-grow-1"><?= htmlspecialchars($a['desc']) ?></p>
            <a href="<?= htmlspecialchars($a['href']) ?>" class="btn btn-danger btn-sm mt-auto">Access</a>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <?php if ($currentRole == 'admin' || $currentRole == 'manager'): ?>
  <!-- Recent Users and Shipments -->
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
<?php endif; ?>

  <script>
    document.getElementById('notificationBell')?.addEventListener('click', function() {
      const popup = document.getElementById('notificationPopup');
      if (!popup) return;
      popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
    });

    document.addEventListener('click', function(e) {
      const popup = document.getElementById('notificationPopup');
      const bell = document.getElementById('notificationBell');
      if (!popup || !bell) return;
      if (!popup.contains(e.target) && e.target !== bell) {
        popup.style.display = 'none';
      }
    });
  </script>

<!-- Chart.js core + DataLabels plugin -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
(() => {
  // —— 1) Shipments Bar Chart (colors unchanged) ——  
  const sCtx = document.getElementById('shipmentsChart')?.getContext('2d');
  let sChart;
  function refreshShipments() {
    if (!sCtx) return;
    fetch('../api/shipments_chart_data.php')
      .then(r=>r.json())
      .then(({labels,data})=>{
        if (sChart) {
          sChart.data.labels = labels;
          sChart.data.datasets[0].data = data;
          sChart.update();
        } else {
          sChart = new Chart(sCtx, {
            type:'bar',
            data:{ labels, datasets:[{
              label:'Shipments',
              data,
              backgroundColor:'#e30613DD'
            }]},
            options:{
              responsive:true, maintainAspectRatio:false,
              animation:{ duration:800, easing:'easeOutQuart' },
              scales:{ x:{display:false}, y:{beginAtZero:true} },
              plugins:{ legend:{display:false} }
            }
          });
        }
      })
      .catch(console.error);
  }
  refreshShipments();
  setInterval(refreshShipments,30000);

  // —— 2) Doughnut builder with custom colors & labels ——  
  function makeDoughnut(ctxId, apiUrl, colors) {
    const canvas = document.getElementById(ctxId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let chart;
    function load() {
      fetch(apiUrl)
        .then(r=>r.json())
        .then(({labels,data})=>{
          const total = data.reduce((sum,v)=>sum+v,0);
          const dataset = {
            data,
            backgroundColor: colors,       // ← YOUR CUSTOM SLICE COLORS
            borderColor: colors.map(c=>c),
            borderWidth:1
          };
          if (chart) {
            chart.data.labels = labels;
            chart.data.datasets[0] = dataset;
            chart.update();
          } else {
            chart = new Chart(ctx, {
              type:'doughnut',
              data:{ labels, datasets:[dataset] },
              options:{
                responsive:true, maintainAspectRatio:false,
                animation:{ duration:800, easing:'easeOutBounce' },
                plugins:{
                  legend:{
                    position:'bottom',
                    labels:{ boxWidth:12, font:{size:10}, color:'#333' }  // legend label color
                  },
                  tooltip:{
                    callbacks:{
                      label:ctx=>{
                        const v = ctx.parsed;
                        const pct = total ? ((v/total*100).toFixed(1)+'%') : '0%';
                        return `${ctx.label}: ${v} (${pct})`;
                      }
                    }
                  },
                  datalabels:{ 
                    color:'#ffffff',        // ← IN‑SLICE TEXT COLOR
                    font:{ weight:'bold', size:11 },
                    formatter:(v, ctx)=>{
                      const pct = total ? ((v/total*100).toFixed(0)+'%') : '0%';
                      // return either value, percent, or both on separate lines:
                      return `${v}\n${pct}`;  // change to `${pct}` if you only want % 
                    },
                    anchor:'center',
                    align:'center',
                    clamp:true
                  }
                }
              },
              plugins:[ ChartDataLabels ]
            });
          }
        })
        .catch(console.error);
    }
    load();
    setInterval(load,30000);
  }

  // —— 3) Initialize your two doughnuts with new palettes ——  
  makeDoughnut(
    'bookingsShipmentsChart',
    '../api/bookings_vs_shipments.php',
    ['#ff6384', '#36a2eb']    // ← e.g. pink & blue
  );

  makeDoughnut(
    'fleetContainersChart',
    '../api/fleet_vs_containers.php',
    ['#4bc0c0', '#ff9f40']    // ← e.g. teal & orange
  );
})();
</script>



  <div class="row g-6 mt-5"></div>
  <?php require_once '../includes/admin_footer.php'; ?>