<?php
// Assumes session started and $pdo (PDO connection) available before this include

// Role Display (default to Guest)
$userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Guest';
?>

<style>
  .footer-northport {
    background: #ffffff;
    color: #333;
    font-family: 'Inter', sans-serif;
    padding: 3rem 0 2rem;
    font-size: 14px;
    border-top: 1px solid #eee;
  }

  .footer-northport a {
    color: #cc0612;
    text-decoration: none;
  }

  .footer-northport a:hover {
    color: #b6050e;
    text-decoration: underline;
  }

  .footer-northport .footer-bottom {
    margin-top: 2rem;
    padding-top: 1.5rem;
    font-size: 13px;
    color: #888;
    border-top: 1px solid #ddd;
    text-align: center;
  }

  @media (max-width: 768px) {
    .footer-northport {
      text-align: center;
    }
  }
</style>

<footer class="footer-northport">
  <div class="footer-bottom mt-2">
    &copy; <?= date('Y') ?> NorthPort Logistics Pvt Ltd. All rights reserved.
    <br>
    You are logged in as: <strong><?= htmlspecialchars($userRole) ?></strong>
  </div>
</footer>

<!-- Font Awesome + Bootstrap -->
<script src="https://kit.fontawesome.com/a2e0c4d6c9.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
