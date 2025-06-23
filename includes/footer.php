<?php
// Assumes session started and $pdo (PDO connection) available before this include

// Fetch all footer related settings once
$footer_settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $footer_settings[$row['setting_key']] = $row['setting_value'];
}

// Assign variables from array with fallback empty string
$footer_email    = $footer_settings['footer_contact_email'] ?? '';
$footer_phone    = $footer_settings['footer_contact_phone'] ?? '';
$footer_address1 = $footer_settings['footer_address_line1'] ?? '';
$footer_address2 = $footer_settings['footer_address_line2'] ?? '';

$social_links = [
    'facebook'  => $footer_settings['footer_social_facebook'] ?? '',
    'twitter'   => $footer_settings['footer_social_twitter'] ?? '',
    'linkedin'  => $footer_settings['footer_social_linkedin'] ?? '',
    'instagram' => $footer_settings['footer_social_instagram'] ?? '',
];

$shortcuts = [];
for ($i = 1; $i <= 3; $i++) {
    $name = $footer_settings["footer_shortcut_{$i}_name"] ?? '';
    $url  = $footer_settings["footer_shortcut_{$i}_url"] ?? '';
    if ($name && $url) {
        $shortcuts[] = ['name' => $name, 'url' => $url];
    }
}

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
  .footer-northport .footer-heading {
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 15px;
  }
  .footer-northport ul {
    list-style: none;
    padding-left: 0;
    margin: 0;
  }
  .footer-northport ul li {
    margin-bottom: 0.5rem;
  }
  .footer-northport .social-icons a {
    font-size: 18px;
    margin-right: 10px;
    color: #cc0612;
    transition: color 0.3s ease;
  }
  .footer-northport .social-icons a:hover {
    color: #b6050e;
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
    .footer-northport .col-md-3 {
      margin-bottom: 2rem;
    }
  }
</style>

<footer class="footer-northport">
  <div class="container">
    <div class="row">

      <!-- Contact Info -->
      <div class="col-md-3">
        <h5 class="footer-heading">Contact Us</h5>
        <?php if ($footer_address1 || $footer_address2): ?>
          <p><?= safeOutput($footer_address1) ?><br><?= safeOutput($footer_address2) ?></p>
        <?php endif; ?>
        <?php if ($footer_email): ?>
          <p>Email: <a href="mailto:<?= safeOutput($footer_email) ?>"><?= safeOutput($footer_email) ?></a></p>
        <?php endif; ?>
        <?php if ($footer_phone): ?>
          <p>Phone: <a href="tel:<?= safeOutput($footer_phone) ?>"><?= safeOutput($footer_phone) ?></a></p>
        <?php endif; ?>
      </div>

      <!-- Quick Links -->
      <div class="col-md-3">
        <h5 class="footer-heading">Quick Links</h5>
        <ul>
          <?php foreach ($shortcuts as $sc): ?>
            <li><a href="<?= safeOutput($sc['url']) ?>"><?= safeOutput($sc['name']) ?></a></li>
          <?php endforeach; ?>
          <li><a href="/track_shipment.php">Track Shipment</a></li>
          <li><a href="/my_invoices.php">Invoices</a></li>
        </ul>
      </div>

      <!-- Social Media -->
      <div class="col-md-3">
        <h5 class="footer-heading">Follow Us</h5>
        <div class="social-icons">
          <?php if ($social_links['facebook']): ?>
            <a href="<?= safeOutput($social_links['facebook']) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
          <?php endif; ?>
          <?php if ($social_links['twitter']): ?>
            <a href="<?= safeOutput($social_links['twitter']) ?>" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>
          <?php endif; ?>
          <?php if ($social_links['linkedin']): ?>
            <a href="<?= safeOutput($social_links['linkedin']) ?>" target="_blank" rel="noopener"><i class="fab fa-linkedin-in"></i></a>
          <?php endif; ?>
          <?php if ($social_links['instagram']): ?>
            <a href="<?= safeOutput($social_links['instagram']) ?>" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Newsletter -->
      <div class="col-md-3">
        <h5 class="footer-heading">Stay Updated</h5>
        <p>Subscribe for updates & offers</p>
        <form class="d-flex" action="#" method="post">
          <input type="email" class="form-control form-control-sm me-2" placeholder="Your email" required>
          <button class="form-control form-control-sm" type="submit">Go</button>
        </form>
      </div>

    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom mt-4">
      &copy; <?= date('Y') ?> NorthPort Logistics Pvt Ltd. All rights reserved.<br>
      You are logged in as: <strong><?= safeOutput($userRole) ?></strong>
    </div>
  </div>
</footer>

<!-- Font Awesome + Bootstrap JS -->
<script src="https://kit.fontawesome.com/a2e0c4d6c9.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
