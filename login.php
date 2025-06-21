<?php
session_start();
require_once 'includes/db.php';       // Your PDO connection
require_once 'includes/settings.php'; // contains get_setting() if used

// Load logo path or fallback default
$default_logo = 'assets/images/default-logo.png';
$logo_path = get_setting('site_logo') ?: get_setting('logo_path') ?: $default_logo;
$full_path = __DIR__ . '/' . $logo_path;
if (!file_exists($full_path)) {
    $logo_path = $default_logo;
}
$logo = $logo_path . '?v=' . time();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = "Please enter email and password.";
    } else {
        // Case-insensitive email check
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role, is_active FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Invalid credentials.";
        } elseif (!$user['is_active']) {
            $error = "Your account is inactive. Please contact support.";
        } elseif (!password_verify($password, $user['password_hash'])) {
            $error = "Invalid credentials.";
        } else {
            // Login success
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username']; // store username for session use
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();

            // Redirect based on role
            if ($user['role'] === 'admin' || $user['role'] === 'sub-admin') {
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] === 'user') {
                header("Location: user/dashboard.php");
            } else {
                header("Location: unauthorized.php");
            }
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login - NorthPort</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
    body {
      background-color: #fff;
      font-family: 'Inter', sans-serif;
      color: #222;
    }
    .login-container {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 2rem;
    }
    .logo {
      display: block;
      margin-bottom: 1rem;
      max-height: 60px;
    }
    h3 {
      text-align: center;
      color: #e30613;
      font-weight: 800;
      margin-bottom: 1.5rem;
    }
    .form-label {
      font-weight: 600;
    }
    .btn-primary {
      background-color: transparent;
      border: 2px solid #e30613;
      color: #e30613;
      font-weight: 600;
      transition: all 0.25s ease-in-out;
    }
    .btn-primary:hover,
    .btn-primary:focus {
      background-color: #b6050e;
      border-color: #b6050e;
      color: white;
      outline: none;
      box-shadow: none;
    }
    a {
      color: #cc0612;
    }
    a:hover {
      color: #b6050e;
      text-decoration: underline;
    }
    .alert {
      font-size: 0.9rem;
    }
    form {
      width: 100%;
      max-width: 400px;
    }
  </style>
</head>
<body>

<div class="login-container">

  <?php if ($logo): ?>
    <img src="<?= htmlspecialchars($logo) ?>" alt="NorthPort Logo" class="logo" />
  <?php endif; ?>

  <h3>NorthPort Login</h3>

  <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="post" novalidate>
    <div class="mb-3">
      <label for="email" class="form-label">Email address</label>
      <input type="email" id="email" name="email" class="form-control" required autofocus
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" id="password" name="password" class="form-control" required />
    </div>
    <button type="submit" class="btn btn-primary w-100">Login</button>
  </form>

  <div class="mt-3 text-center">
    <small>Don't have an account? <a href="register.php">Register here</a></small>
  </div>

</div>

</body>
</html>
