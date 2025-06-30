<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$currentRole = $_SESSION['role'] ?? 'user';

if (!in_array($currentRole, ['admin', 'manager', 'employer'])) {
    echo "<div class='alert alert-danger m-5'>Access denied.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

$sql = "
    SELECT b.*, u.username 
    FROM bank_details b
    JOIN users u ON b.user_id = u.id
    ORDER BY b.updated_at DESC
";

$stmt = $pdo->query($sql);
$bankAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
  .btn {
    font-size: 0.85rem;
    padding: 0.3rem 0.8rem;
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
  h2 {
    font-size: 1.25rem;
  }
</style>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Bank Accounts</h2>
    <?php if (in_array($currentRole, ['admin', 'manager'])): ?>
      <a href="add_bank_details.php" class="btn btn-danger">Add Bank Account</a>
    <?php endif; ?>
  </div>

  <div class="table-responsive card shadow-sm">
    <table class="table table-hover mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th>User</th>
          <th>Bank Name</th>
          <th>Branch</th>
          <th>Account Name</th>
          <th>Account No</th>
          <th>SWIFT</th>
          <th>Currency</th>
          <th>Created</th>
          <th>Updated</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($bankAccounts)): ?>
          <?php foreach ($bankAccounts as $b): ?>
            <tr>
              <td><?= htmlspecialchars($b['username']) ?></td>
              <td><?= htmlspecialchars($b['bank_name']) ?></td>
              <td><?= htmlspecialchars($b['branch_name'] ?? '-') ?></td>
              <td><?= htmlspecialchars($b['account_name']) ?></td>
              <td><?= htmlspecialchars($b['account_number']) ?></td>
              <td><?= htmlspecialchars($b['swift_code'] ?? '-') ?></td>
              <td><?= htmlspecialchars($b['currency']) ?></td>
              <td><?= date('Y-m-d', strtotime($b['created_at'])) ?></td>
              <td><?= date('Y-m-d', strtotime($b['updated_at'])) ?></td>
              <td class="text-nowrap">
                <a href="edit_bank_details.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger">Edit</a>
                <?php if ($currentRole === 'admin'): ?>
                  <a href="delete_bank_details.php?id=<?= $b['id'] ?>" onclick="return confirm('Delete this bank account?')" class="btn btn-sm btn-outline-danger">Delete</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="10" class="text-center text-muted">No bank accounts found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
