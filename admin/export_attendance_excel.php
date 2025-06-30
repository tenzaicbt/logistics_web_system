<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Only allow admin
$role = $_SESSION['role'] ?? '';
if ($role !== 'admin') {
    exit('Access denied.');
}

// Headers to force Excel download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=attendance_export_" . date("Y-m-d") . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Filters from GET
$filterUser = $_GET['user_id'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterDateFrom = $_GET['date_from'] ?? '';
$filterDateTo = $_GET['date_to'] ?? '';

// Build SQL query
$where = [];
$params = [];

if ($filterUser !== '') {
    $where[] = 'a.user_id = ?';
    $params[] = $filterUser;
}
if ($filterStatus !== '') {
    $where[] = 'a.status = ?';
    $params[] = $filterStatus;
}
if ($filterDateFrom !== '') {
    $where[] = 'a.date >= ?';
    $params[] = $filterDateFrom;
}
if ($filterDateTo !== '') {
    $where[] = 'a.date <= ?';
    $params[] = $filterDateTo;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// SQL to fetch data
$sql = "SELECT a.date, u.username, a.check_in_time, a.check_out_time, a.status, a.remarks, a.location, a.is_manual_entry
        FROM attendances a
        LEFT JOIN users u ON a.user_id = u.id
        $whereSql
        ORDER BY a.date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output HTML table (Excel can open this format)
echo "<table border='1'>";
echo "<tr>
        <th>Date</th>
        <th>User</th>
        <th>Check-in</th>
        <th>Check-out</th>
        <th>Status</th>
        <th>Remarks</th>
        <th>Location</th>
        <th>Manual Entry</th>
      </tr>";

foreach ($rows as $row) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['date']) . "</td>";
    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
    echo "<td>" . htmlspecialchars($row['check_in_time'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['check_out_time'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
    echo "<td>" . htmlspecialchars($row['remarks'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['location'] ?? '-') . "</td>";
    echo "<td>" . ($row['is_manual_entry'] ? 'Yes' : 'No') . "</td>";
    echo "</tr>";
}
echo "</table>";
