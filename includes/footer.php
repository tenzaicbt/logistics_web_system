<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Helper for safe output
function safeOutput($str) {
  return htmlspecialchars($str);
}

// Fetch footer settings
$footer_email = getSetting($pdo, 'footer_contact_email');
$footer_phone = getSetting($pdo, 'footer_contact_phone');
$footer_address1 = getSetting($pdo, 'footer_address_line1');
$footer_address2 = getSetting($pdo, 'footer_address_line2');

$social_links = [
  'facebook' => getSetting($pdo, 'footer_social_facebook'),
  'twitter' => getSetting($pdo, 'footer_social_twitter'),
  'linkedin' => getSetting($pdo, 'footer_social_linkedin'),
  'instagram' => getSetting($pdo, 'footer_social_instagram'),
];

$shortcuts = [];
for ($i = 1; $i <= 3; $i++) {
  $name = getSetting($pdo, "footer_shortcut_{$i}_name");
  $url = getSetting($pdo, "footer_shortcut_{$i}_url");
  if ($name && $url) {
    $shortcuts[] = ['name' => $name, 'url' => $url];
  }
}
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
            <a href="<?= safeOutput($social_links['facebook']) ?>" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <?php endif; ?>
          <?php if ($social_links['twitter']): ?>
            <a href="<?= safeOutput($social_links['twitter']) ?>" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <?php endif; ?>
          <?php if ($social_links['linkedin']): ?>
            <a href="<?= safeOutput($social_links['linkedin']) ?>" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
          <?php endif; ?>
          <?php if ($social_links['instagram']): ?>
            <a href="<?= safeOutput($social_links['instagram']) ?>" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Newsletter -->
      <div class="col-md-3">
        <h5 class="footer-heading">Stay Updated</h5>
        <p>Subscribe for updates & offers</p>
        <form class="d-flex" action="#" method="post">
          <input type="email" class="form-control form-control-sm me-2" placeholder="Your email" required>
          <button class="form-control form-control-sm me-2" type="submit">Go</button>
        </form>
      </div>

    </div>

    <div class="footer-bottom mt-4">
      &copy; <?= date('Y') ?> NorthPort Logistics Pvt Ltd. All rights reserved.
    </div>
  </div>
</footer>

<!-- Font Awesome for social icons -->
<script src="https://kit.fontawesome.com/a2e0c4d6c9.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
