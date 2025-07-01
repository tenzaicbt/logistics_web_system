<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$currentRole = $_SESSION['role'] ?? 'user';
$userId = $_SESSION['user_id'] ?? 0;

// Access control: only employers and managers can view
if (!in_array($currentRole, ['employer', 'manager'])) {
    echo "<div class='alert alert-danger m-5'>Access denied.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

// Fetch paysheets for logged-in user
$stmt = $pdo->prepare("SELECT p.*, u.username AS uploader_name 
                       FROM paysheets p 
                       LEFT JOIN users u ON p.uploaded_by = u.id 
                       WHERE p.employer_id = ? 
                       ORDER BY p.created_at DESC");
$stmt->execute([$userId]);
$paysheets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
  .btn {
    font-size: 0.8rem;
    padding: 0.25rem 0.75rem;
  }
  .btn-outline-secondary {
    border-color: #666;
    color: #666;
  }
  .btn-outline-secondary:hover {
    background-color: #666;
    color: #fff;
  }
  /* .container {
    font-size: 0.85rem;
  } */
  h2 {
    font-size: 1.25rem;
  }
</style>

<div class="container my-5">
  <h2 class="fw-bold mb-4">My Paysheets</h2>

  <div class="table-responsive card shadow-sm">
    <table class="table table-hover mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Month</th>
          <th>Uploaded By</th>
          <th>File</th>
          <th>Uploaded On</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($paysheets) > 0): ?>
          <?php foreach ($paysheets as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['id']) ?></td>
              <td><?= htmlspecialchars($p['month']) ?></td>
              <td><?= htmlspecialchars($p['uploader_name'] ?? 'Admin') ?></td>
              <td><a href="<?= htmlspecialchars($p['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Download</a></td>
              <td><?= date('Y-m-d', strtotime($p['created_at'])) ?></td>
              <td><?= nl2br(htmlspecialchars($p['notes'])) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="text-center text-muted">No paysheets found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
