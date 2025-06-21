<?php
session_start();
require_once '../includes/db.php';

$message = '';
$error = '';
$hint = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        $stmt = $pdo->prepare("SELECT password_hint FROM master_admins WHERE email = ?");
        $stmt->execute([$email]);
        $hint = $stmt->fetchColumn();

        if ($hint) {
            $message = "Password hint for $email:";
        } else {
            $error = "No master admin found with that email.";
        }
    } else {
        $error = "Please enter your email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Master Password Hint</title>
<style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; }
    .container { max-width: 400px; margin: 100px auto; padding: 20px; background: white; border: 1px solid #ddd; border-radius: 5px; }
    input[type=email] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 3px; }
    button { width: 100%; padding: 10px; background: #222; color: white; border: none; border-radius: 3px; cursor: pointer; }
    button:hover { background: #444; }
    .message { margin-bottom: 15px; font-weight: bold; }
    .error { color: red; margin-bottom: 10px; }
    .hint { font-style: italic; color: #555; }
</style>
</head>
<body>
    <div class="container">
        <h2>Master Password Hint</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
            <div class="hint"><?= htmlspecialchars($hint) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Enter your master email" required />
            <button type="submit">Get Hint</button>
        </form>
        <a href="master_login.php" style="display:block; margin-top:10px;">Back to Login</a>
    </div>
</body>
</html>
