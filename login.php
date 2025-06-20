<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = "Please enter username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT id, password_hash, role, is_active FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !$user['is_active']) {
            $error = "Invalid credentials or inactive account.";
        } elseif (!password_verify($password, $user['password_hash'])) {
            $error = "Invalid credentials.";
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();

            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                case 'sub-admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'user':
                    header("Location: user/dashboard.php");
                    break;
                default:
                    header("Location: unauthorized.php");
                    break;
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
  <div class="card shadow p-4" style="width: 360px;">
    <h3 class="mb-4 text-center">NorthPort Login</h3>
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" id="username" name="username" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100" type="submit">Login</button>
    </form>
    <div class="mt-3 text-center">
      <small>Don't have an account? <a href="register.php">Register here</a></small>
    </div>
  </div>
</div>

</body>
</html>