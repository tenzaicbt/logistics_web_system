<?php
// includes/footer.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$footer_text = getSetting($pdo, 'footer_text') ?? '© 2025 NorthPort Logistics Pvt Ltd. All rights reserved.';
?>
</main>

<footer class="bg-light border-top mt-auto py-3">
  <div class="container text-center text-muted small">
    <?= htmlspecialchars($footer_text) ?>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
