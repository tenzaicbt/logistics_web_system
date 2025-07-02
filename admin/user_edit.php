<?php
session_start();
require_once '../includes/db.php';
include '../includes/header.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'sub-admin'])) {
    header("Location: ../unauthorized.php");
    exit;
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) die('Invalid user ID.');

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die('User not found.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $role       = $_POST['role'] ?? 'user';
    $is_active  = isset($_POST['is_active']) ? 1 : 0;
    $notes      = trim($_POST['notes'] ?? '');

    $street     = trim($_POST['street_address'] ?? '');
    $city       = trim($_POST['city'] ?? '');
    $state      = trim($_POST['state'] ?? '');
    $postal     = trim($_POST['postal_code'] ?? '');
    $country    = trim($_POST['country'] ?? '');
    $company    = trim($_POST['company_name'] ?? '');
    $dob        = $_POST['date_of_birth'] ?? null;

    $department       = trim($_POST['department'] ?? '');
    $job_title        = trim($_POST['job_title'] ?? '');
    $date_of_joining  = $_POST['date_of_joining'] ?? null;
    $nic_passport     = trim($_POST['nic_passport_number'] ?? '');

    $errors = [];
    if (!$username) $errors[] = "Username is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE users SET 
                username = ?, phone = ?, role = ?, is_active = ?, notes = ?,
                street_address = ?, city = ?, state = ?, postal_code = ?, country = ?, 
                company_name = ?, date_of_birth = ?, 
                department = ?, job_title = ?, date_of_joining = ?, nic_passport_number = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $username, $phone, $role, $is_active, $notes,
            $street, $city, $state, $postal, $country,
            $company, $dob ?: null,
            $department, $job_title, $date_of_joining ?: null, $nic_passport,
            $user_id
        ]);

        header("Location: manage_users.php?success=1");
        exit;
    }
}
?>

<style>
  body {
    background-color: #fff;
    font-family: 'Inter', sans-serif;
    color: #222;
    font-size: 0.85rem;
  }


  label.form-label {
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
  }

  input.form-control,
  select.form-control,
  select.form-select,
  textarea.form-control {
    font-size: 0.8rem;
    border-radius: 4px;
    margin-bottom: 0.75rem;
  }

  .btn {
    font-size: 0.8rem;
    padding: 0.25rem 0.75rem;
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

  .form-check-label {
    font-size: 0.75rem;
  }

  textarea.form-control {
    font-size: 0.8rem;
  }

</style>


<div class="container my-5">
    <h2 class="mb-4 fw-bold">Edit User</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="col">
                <label class="form-label">Email Address</label>
                <div class="form-control bg-light"><?= htmlspecialchars($user['email']) ?></div>
                <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <?php foreach (['user', 'admin', 'manager', 'employer'] as $role): ?>
                        <option value="<?= $role ?>" <?= $user['role'] === $role ? 'selected' : '' ?>><?= ucfirst($role) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6"><label class="form-label">Street Address</label>
                <input type="text" name="street_address" class="form-control" value="<?= htmlspecialchars($user['street_address']) ?>">
            </div>
            <div class="col-md-6"><label class="form-label">City</label>
                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($user['city']) ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4"><label class="form-label">State</label>
                <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($user['state']) ?>">
            </div>
            <div class="col-md-4"><label class="form-label">Postal Code</label>
                <input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($user['postal_code']) ?>">
            </div>
            <div class="col-md-4"><label class="form-label">Country</label>
                <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($user['country']) ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6"><label class="form-label">Company Name</label>
                <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($user['company_name']) ?>">
            </div>
            <div class="col-md-6"><label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($user['date_of_birth']) ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6"><label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($user['department'] ?? '') ?>">
            </div>
            <div class="col-md-6"><label class="form-label">Job Title</label>
                <input type="text" name="job_title" class="form-control" value="<?= htmlspecialchars($user['job_title'] ?? '') ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6"><label class="form-label">Date of Joining</label>
                <input type="date" name="date_of_joining" class="form-control" value="<?= htmlspecialchars($user['date_of_joining'] ?? '') ?>">
            </div>
            <div class="col-md-6"><label class="form-label">NIC / Passport Number</label>
                <input type="text" name="nic_passport_number" class="form-control" value="<?= htmlspecialchars($user['nic_passport_number'] ?? '') ?>">
            </div>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="activeCheck" <?= $user['is_active'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="activeCheck">Account Active</label>
        </div>

        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($user['notes']) ?></textarea>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-danger">Update User</button>
            <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

  <div class="row g-6 mt-5"></div>
  <?php require_once '../includes/admin_footer.php'; ?>