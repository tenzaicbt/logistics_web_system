<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // Redirect admin/sub-admin or user accordingly
        if (in_array($user['role'], ['admin', 'sub_admin'])) {
            header('Location: /northport/admin/dashboard.php');
        } else {
            header('Location: /northport/user/dashboard.php');
        }
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<form method="post">
    <input type="email" name="email" required placeholder="Email"><br>
    <input type="password" name="password" required placeholder="Password"><br>
    <button type="submit">Login</button>
</form>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
