<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

function getTableCount($pdo, $table)
{
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return 'N/A';
    }
}

$counts = [
    'Users' => getTableCount($pdo, 'users'),
    'Admins' => getTableCount($pdo, 'admins'),
    'Hotels' => getTableCount($pdo, 'hotels'),
    'Guides' => getTableCount($pdo, 'guide'),
    'Vehicles' => getTableCount($pdo, 'vehicles'),
    'Bookings' => getTableCount($pdo, 'bookings'),
    'Guide Bookings' => getTableCount($pdo, 'guide_bookings'),
    'Vehicle Bookings' => getTableCount($pdo, 'vehicle_bookings')
];

$displayErrors = ini_get('display_errors');
$sessionCookieParams = session_get_cookie_params();
$poweredByExposed = in_array('X-Powered-By', headers_list());
$uploadsWritable = is_writable(__DIR__ . '/../uploads');
?>

<style>
    .section-title {
        font-weight: 700;
        color: #b6050e;
        border-bottom: 2px solid #ccc;
        margin-bottom: 10px;
        padding-bottom: 4px;
        font-size: 1.1rem;
    }

    .info-label {
        font-weight: 600;
        color: #444;
        width: 200px;
        display: inline-block;
    }

    .info-row {
        padding: 5px 0;
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
</style>

<div class="container my-5">
    <h2 class="fw-bold mb-4">System Information</h2>

    <div class="section-title">Developer Info</div>
    <div class="info-row"><span class="info-label">Developer:</span> Yohan Koshala</div>
    <div class="info-row"><span class="info-label">Location:</span> Sri Lanka</div>
    <div class="info-row"><span class="info-label">Version:</span> NorthPort Logistics ERP 1.0</div>
    <div class="info-row"><span class="info-label">Last Update:</span> June 2025</div>

    <div class="section-title mt-5">Server & PHP Info</div>
    <div class="info-row"><span class="info-label">PHP Version:</span> <?= phpversion(); ?></div>
    <div class="info-row"><span class="info-label">MySQL Version:</span> <?= $pdo->getAttribute(PDO::ATTR_SERVER_VERSION); ?></div>
    <div class="info-row"><span class="info-label">Server Software:</span> <?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'N/A'); ?></div>
    <div class="info-row"><span class="info-label">Operating System:</span> <?= php_uname(); ?></div>

    <div class="section-title mt-5">System Status & Security</div>
    <div class="info-row"><span class="info-label">Session Status:</span>
        <?= session_status() === PHP_SESSION_ACTIVE ? '<span class="text-success fw-bold">Active</span>' : '<span class="text-danger fw-bold">Inactive</span>' ?>
    </div>
    <div class="info-row"><span class="info-label">HTTPS:</span> <span class="text-warning fw-bold">Check manually on server</span></div>
    <div class="info-row"><span class="info-label">Admin Auth:</span> <span class="text-success fw-bold">Secured (Session)</span></div>
    <div class="info-row"><span class="info-label">Database Connection:</span> <span class="text-success fw-bold">Connected</span></div>
    <div class="info-row"><span class="info-label">Prepared Queries:</span> <span class="text-info fw-bold">Recommended</span></div>
    <div class="info-row"><span class="info-label">Display Errors:</span>
        <?= $displayErrors ? '<span class="text-danger fw-bold">ON</span>' : '<span class="text-success fw-bold">OFF</span>' ?>
    </div>
    <div class="info-row"><span class="info-label">Session Cookies (HTTPOnly):</span>
        <?= $sessionCookieParams['httponly'] ? '<span class="text-success fw-bold">Enabled</span>' : '<span class="text-danger fw-bold">Disabled</span>' ?>
    </div>
    <div class="info-row"><span class="info-label">Session Cookies (Secure):</span>
        <?= $sessionCookieParams['secure'] ? '<span class="text-success fw-bold">Enabled</span>' : '<span class="text-warning fw-bold">Not forced</span>' ?>
    </div>
    <div class="info-row"><span class="info-label">X-Powered-By Header:</span>
        <?= $poweredByExposed ? '<span class="text-warning fw-bold">Exposed</span>' : '<span class="text-success fw-bold">Hidden</span>' ?>
    </div>
    <div class="info-row"><span class="info-label">Uploads Writable:</span>
        <?= $uploadsWritable ? '<span class="text-warning fw-bold">Writable</span>' : '<span class="text-success fw-bold">Safe</span>' ?>
    </div>

<div class="container my-5"></div>
    <a href="dashboard.php" class="btn btn-secondary"> Back to Dashboard</a>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
