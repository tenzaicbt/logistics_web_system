<?php
// Assumes session started and $pdo (PDO connection) is available

// Fetch footer settings
$footer_settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $footer_settings[$row['setting_key']] = $row['setting_value'];
}

$footer_email    = $footer_settings['footer_contact_email'] ?? '';
$footer_phone    = $footer_settings['footer_contact_phone'] ?? '';
$footer_address1 = $footer_settings['footer_address_line1'] ?? '';
$footer_address2 = $footer_settings['footer_address_line2'] ?? '';
?>

<style>
  .footer-contact {
    background-color: #ffffff;
    color: #333;
    font-family: 'Inter', sans-serif;
    padding: 2rem 0;
    border-top: 1px solid #eee;
    font-size: 14px;
  }

  .footer-contact .container {
    max-width: 960px;
    margin: 0 auto;
    text-align: center;
  }

  .footer-contact h5 {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 1rem;
    color: #b6050e;
  }

  .footer-contact p {
    margin: 0.25rem 0;
    color: #555;
  }

  .footer-contact a {
    color: #cc0612;
    text-decoration: none;
    font-weight: 500;
  }

  .footer-contact a:hover {
    text-decoration: underline;
    color: #b6050e;
  }

  .footer-contact .footer-bottom {
    margin-top: 1.5rem;
    font-size: 13px;
    color: #888;
  }
</style>

<footer class="footer-contact">
  <div class="container">
    <h5>Contact Us</h5>
    
    <?php if ($footer_address1 || $footer_address2): ?>
      <p><?= htmlspecialchars($footer_address1) ?><br><?= htmlspecialchars($footer_address2) ?></p>
    <?php endif; ?>

    <?php if ($footer_email): ?>
      <p>Email: <a href="mailto:<?= htmlspecialchars($footer_email) ?>"><?= htmlspecialchars($footer_email) ?></a></p>
    <?php endif; ?>

    <?php if ($footer_phone): ?>
      <p>Phone: <a href="tel:<?= htmlspecialchars($footer_phone) ?>"><?= htmlspecialchars($footer_phone) ?></a></p>
    <?php endif; ?>

    <div class="footer-bottom">
      &copy; <?= date('Y') ?> NorthPort Logistics Pvt Ltd. All rights reserved.
    </div>
  </div>
</footer>
