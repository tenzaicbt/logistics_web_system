<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
  header("Location: /login.php");
  exit;
}

// Fetch user details
$stmt = $pdo->prepare("
    SELECT 
      username, email, phone, company_name, street_address, city, state, postal_code, country,
      profile_pic, date_of_birth, role, created_at, updated_at, preferences, notes
    FROM users WHERE id = ? LIMIT 1
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  exit('User not found.');
}

// Fetch user's uploaded documents
$docStmt = $pdo->prepare("SELECT document_type, file_path, uploaded_at FROM documents WHERE uploaded_by = ? ORDER BY uploaded_at DESC");
$docStmt->execute([$user_id]);
$documents = $docStmt->fetchAll(PDO::FETCH_ASSOC);


?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

  * {
    box-sizing: border-box;
  }

  body,
  html {
    margin: 0;
    padding: 0;
    font-family: 'Inter', sans-serif;
    background-color: #ffffff;
    color: #222222;
    height: 100vh;
    overflow-y: auto;
    font-size: 14px;
  }

  a {
    color: #cc0612;
    /* Deep Red */
    text-decoration: none;
    /* remove underline */
  }

  a:hover,
  a:focus {
    color: #b6050e;
    /* Darker Red */
    text-decoration: none;
    /* remove underline on hover/focus */
    outline: none;
  }

  .app-container {
    display: flex;
    justify-content: flex-start;
    /* align left */
    align-items: flex-start;
    /* align top */
    min-height: 100vh;
    padding: 3rem 2rem;
    background: #ffffff;
  }

  .main-content {
    max-width: 700px;
    width: 100%;
    padding: 2rem 2.5rem;
    box-sizing: border-box;
    background: transparent;
  }

  h1 {
    font-weight: 900;
    font-size: 2.5rem;
    color: #e30613;
    /* Primary Red */
    margin-bottom: 1rem;
    letter-spacing: 1.2px;
  }

  .profile-pic {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #e30613;
    margin-bottom: 1rem;
  }

  .user-info {
    margin-bottom: 2rem;
    border-bottom: 1px solid #e3e7e8;
    padding-bottom: 1rem;
  }

  .user-info h2 {
    margin: 0 0 0.5rem 0;
    font-weight: 700;
    font-size: 1.5rem;
    color: #b6050e;
    /* Darker Red */
  }

  .user-info p {
    margin: 0.3rem 0;
    color: #555;
    font-size: 0.9rem;
    font-weight: 500;
    letter-spacing: 0.02em;
  }

  .user-info strong {
    color: #333;
  }

  .description {
    font-weight: 400;
    font-size: 0.85rem;
    color: #555;
    margin-bottom: 2.5rem;
    line-height: 1.5;
  }

  .btn {
    font-weight: 600;
    font-size: 0.85rem;
    background-color: transparent;
    border: 1.5px solid #e30613;
    color: #e30613;
    border-radius: 6px;
    padding: 0.45rem 1.3rem;
    cursor: pointer;
    transition: all 0.25s ease;
    margin-bottom: 1rem;
    display: inline-block;
    min-width: 140px;
    text-align: center;
    box-shadow: inset 0 0 0 0 #e30613;
    text-decoration: none;
    /* remove underline */
  }

  .btn:hover,
  .btn:focus {
    background-color: #b6050e;
    color: #fff;
    outline: none !important;
    box-shadow: none !important;
    text-decoration: none !important;
    /* remove underline on hover/focus */
  }

  /* Additional to remove outline completely on keyboard focus */
  .btn:focus,
  .btn:focus-visible {
    outline: none !important;
    box-shadow: none !important;
  }

  .documents-section {
    margin-top: 2rem;
  }

  .documents-section h3 {
    font-weight: 700;
    font-size: 1.25rem;
    color: #e30613;
    margin-bottom: 1rem;
  }

  .documents-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .documents-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #e3e7e8;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    color: #444;
  }

  .documents-list li:last-child {
    border-bottom: none;
  }

  .doc-link {
    color: #cc0612;
    text-decoration: none;
    /* remove underline */
    font-weight: 600;
  }

  .doc-link:hover,
  .doc-link:focus {
    text-decoration: none;
    /* remove underline on hover/focus */
    color: #b6050e;
    outline: none;
  }

  @media (max-width: 700px) {
    .main-content {
      padding: 1.5rem 1rem;
      max-width: 100%;
      height: 100vh;
      overflow-y: auto;
    }

    .btn {
      min-width: auto;
      padding: 0.4rem 1rem;
      font-size: 0.8rem;
    }
  }
</style>

<div class="container my-5">
  <h2 class="mb-4 fw-bold">USER DASHBOARD</h2>

  <?php if (!empty($user['profile_pic'])): ?>
    <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture of <?= htmlspecialchars($user['username']) ?>" class="profile-pic" />
  <?php endif; ?>

  <div class="user-info" aria-label="User Profile Information">
    <h2><?= htmlspecialchars($user['username']) ?></h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <?php if (!empty($user['phone'])): ?>
      <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
    <?php endif; ?>
    <?php if (!empty($user['company_name'])): ?>
      <p><strong>Company:</strong> <?= htmlspecialchars($user['company_name']) ?></p>
    <?php endif; ?>
    <?php if (!empty($user['street_address'])): ?>
      <p><strong>Address:</strong> <?= htmlspecialchars($user['street_address']) ?></p>
    <?php endif; ?>
    <?php if (!empty($user['city']) || !empty($user['state']) || !empty($user['postal_code']) || !empty($user['country'])): ?>
      <p><strong>Location:</strong>
        <?= htmlspecialchars(trim(implode(', ', array_filter([$user['city'], $user['state'], $user['postal_code'], $user['country']])), ', ')) ?>
      </p>
    <?php endif; ?>
    <?php if (!empty($user['date_of_birth'])): ?>
      <p><strong>Date of Birth:</strong> <?= htmlspecialchars($user['date_of_birth']) ?></p>
    <?php endif; ?>
    <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
    <?php if (!empty($user['notes'])): ?>
      <p><strong>Notes:</strong> <?= nl2br(htmlspecialchars($user['notes'])) ?></p>
    <?php endif; ?>
  </div>
</div>

<div class="container my-5">
  <p class="description">Welcome to NorthPort Logistics. Use the options below to book shipments or track your orders.</p>

  <a href="book_shipment.php" class="btn" role="button">Book a Shipment</a>
  <a href="track_shipment.php" class="btn" role="button">Track Shipment</a>
  <a href="my_invoices.php" class="btn" role="button">View Invoices</a>
  <a href="upload_documents.php" class="btn" role="button">Upload Documents</a>

  <section class="documents-section" aria-label="Uploaded Documents">
    <h3>Your Uploaded Documents</h3>
    <?php if (count($documents) > 0): ?>
      <ul class="documents-list">
        <?php foreach ($documents as $doc): ?>
          <li>
            <span><?= htmlspecialchars($doc['document_type']) ?></span>
            <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" rel="noopener" class="doc-link" download>Download</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No documents uploaded yet.</p>
    <?php endif; ?>
  </section>
  </section>
</div>
</div>
<?php require_once '../includes/footer.php'; ?>