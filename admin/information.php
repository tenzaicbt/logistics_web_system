<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

// Count total records in major tables
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
$uploadsWritable = is_writable(__DIR__ . '/../uploads'); // adjust path if needed
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>System Info - ExploreSri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #fff;
            font-family: 'Segoe UI', sans-serif;
        }

        /* .container {
            padding-top: 0px;
            max-width: 1000px;
        } */

        .card {
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color:rgb(163, 7, 7);
            color: white;
            font-weight: bold;
        }

        .badge {
            font-size: 0.85rem;
        }

        .developer-info p {
            margin: 0;
        }

        .table-summary td {
            font-weight: 500;
        }

        .text-muted small {
            font-size: 0.85rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <h3 class="mb-4 text-center">System Information</h3>

        <!-- Developer Info -->
        <div class="card">
            <div class="card-header">Developer Info</div>
            <div class="card-body developer-info">
                <p><strong>Developer:</strong> Yohan Koshala</p>
                <p><strong>Location:</strong> Sri Lanka</p>
                <div class="mt-3">
                    <span class="badge bg-dark">ExploreSri Version 1.0</span>
                    <span class="badge bg-secondary">Last Update: June 2025</span>
                </div>
            </div>
        </div>

        <!-- Server Info -->
        <div class="card">
            <div class="card-header">Server & PHP Info</div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">PHP Version</dt>
                    <dd class="col-sm-8"><?= phpversion(); ?></dd>

                    <dt class="col-sm-4">MySQL Version</dt>
                    <dd class="col-sm-8"><?= $pdo->getAttribute(PDO::ATTR_SERVER_VERSION); ?></dd>

                    <dt class="col-sm-4">Server Software</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'N/A'); ?></dd>

                    <dt class="col-sm-4">Operating System</dt>
                    <dd class="col-sm-8"><?= php_uname(); ?></dd>
                </dl>
            </div>
        </div>

        <!-- System Status -->
        <div class="card">
            <div class="card-header">System Status & Security</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        Session Status:
                        <?= session_status() === PHP_SESSION_ACTIVE
                            ? '<span class="text-success fw-bold">Active</span>'
                            : '<span class="text-danger fw-bold">Inactive</span>' ?>
                    </li>
                    <li class="list-group-item">
                        HTTPS:
                        <span class="text-warning fw-bold">Check manually on server</span>
                    </li>
                    <li class="list-group-item">
                        Admin Auth:
                        <span class="text-success fw-bold">Secured (Session)</span>
                    </li>
                    <li class="list-group-item">
                        Database Connection:
                        <span class="text-success fw-bold">Connected</span>
                    </li>
                    <li class="list-group-item">
                        Prepared Queries:
                        <span class="text-info fw-bold">Recommended</span>
                    </li>
                    <li class="list-group-item">
                        Display Errors:
                        <?= $displayErrors
                            ? '<span class="text-danger fw-bold">ON (Should be OFF in production)</span>'
                            : '<span class="text-success fw-bold">OFF</span>' ?>
                    </li>
                    <li class="list-group-item">
                        Session Cookies (HTTPOnly):
                        <?= $sessionCookieParams['httponly']
                            ? '<span class="text-success fw-bold">Enabled</span>'
                            : '<span class="text-danger fw-bold">Disabled</span>' ?>
                    </li>
                    <li class="list-group-item">
                        Session Cookies (Secure):
                        <?= $sessionCookieParams['secure']
                            ? '<span class="text-success fw-bold">Enabled</span>'
                            : '<span class="text-warning fw-bold">Not forced</span>' ?>
                    </li>
                    <li class="list-group-item">
                        X-Powered-By Header:
                        <?= $poweredByExposed
                            ? '<span class="text-warning fw-bold">Exposed (Can leak PHP version)</span>'
                            : '<span class="text-success fw-bold">Hidden</span>' ?>
                    </li>
                    <li class="list-group-item">
                        Uploads Directory Writable:
                        <?= $uploadsWritable
                            ? '<span class="text-warning fw-bold">Writable (Restrict in production)</span>'
                            : '<span class="text-success fw-bold">Safe</span>' ?>
                    </li>
                </ul>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-dark">Back to Dashboard</a>
        </div>
    </div>
</body>

</html>
