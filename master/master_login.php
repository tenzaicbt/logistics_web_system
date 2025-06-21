<?php
session_start();
require_once '../includes/db.php';

if (isset($_SESSION['master_admin_logged_in']) && $_SESSION['master_admin_logged_in'] === true) {
    header('Location: master.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM master_admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['master_admin_logged_in'] = true;
            $_SESSION['master_admin_email'] = $email;
            header('Location: master.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Please enter email and password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Master Login - Tradefording Shipping Line</title>
<style>
    body { font-family: Arial, sans-serif; background:#f0f0f0; }
    .login-container {
        max-width: 400px; margin: 100px auto; background:#fff; padding: 20px;
        border: 1px solid #ccc; border-radius: 6px;
    }
    input[type=email], input[type=password] {
        width: 100%; padding: 10px; margin: 10px 0;
        border: 1px solid #ccc; border-radius: 4px;
        box-sizing: border-box;
    }
    button {
        width: 100%; padding: 10px; background: #333; color: #fff;
        border: none; border-radius: 4px; cursor: pointer;
    }
    button:hover { background: #555; }
    .error { color: red; margin-bottom: 10px; }
</style>
</head>
<body>

<div class="login-container">
    <h2>Master Admin Login</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</div>

</body>  
</html>
