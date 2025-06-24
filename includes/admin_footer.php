<?php
// Assumes session started before including this

// Role Display (default to Guest)
$userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Guest';
?>

<footer class="footer-northport">
  <div class="footer-bottom mt-2" style="text-align:center; font-size:13px; color:#888; border-top:1px solid #ddd; padding-top:1.5rem;">
    &copy; <?= date('Y') ?> NorthPort Logistics Pvt Ltd. All rights reserved.
    <br>
    You are logged in as: <strong><?= htmlspecialchars($userRole) ?></strong>
  </div>
</footer>
