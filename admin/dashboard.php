<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth_check.php';
requireRole([ROLE_ADMIN, ROLE_SUB_ADMIN]);

require_once '../config/db.php';
require_once '../includes/functions.php';

// Load site settings
$settings = get_site_settings($pdo);
$site_name = $settings['site_name'] ?? 'Northport';
$site_logo = $settings['site_logo'] ?? 'northport-logo.png';

// Fetch dashboard stats from DB
try {
    // Total users (all roles)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();

    // Active shipments (delivery_status = 'in_transit' or 'pending')
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM shipments WHERE delivery_status IN ('pending','in_transit')");
    $stmt->execute();
    $active_shipments = $stmt->fetchColumn();

    // Unpaid invoices
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE status = 'unpaid'");
    $stmt->execute();
    $unpaid_invoices = $stmt->fetchColumn();

} catch (PDOException $e) {
    die("DB error: " . $e->getMessage());
}

include '../includes/header.php';
?>

<h1 class="mb-4">Welcome to <?= htmlspecialchars($site_name) ?> Admin Dashboard</h1>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <p class="display-4"><?= $total_users ?></p>
                <a href="/northport/admin/manage_users.php" class="btn btn-light btn-sm">Manage Users</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title">Active Shipments</h5>
                <p class="display-4"><?= $active_shipments ?></p>
                <a href="/northport/admin/manage_shipments.php" class="btn btn-light btn-sm">View Shipments</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <h5 class="card-title">Unpaid Invoices</h5>
                <p class="display-4"><?= $unpaid_invoices ?></p>
                <a href="/northport/admin/invoices.php" class="btn btn-light btn-sm">View Invoices</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
