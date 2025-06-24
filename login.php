<?php
session_start();
require_once 'includes/db.php'; // PDO connection

$default_logo = 'assets/images/default-logo.png';
$logo_path = $default_logo;

try {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_logo', 'logo_path')");
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    if (!empty($settings['site_logo']) && file_exists(__DIR__ . '/' . $settings['site_logo'])) {
        $logo_path = $settings['site_logo'];
    } elseif (!empty($settings['logo_path']) && file_exists(__DIR__ . '/' . $settings['logo_path'])) {
        $logo_path = $settings['logo_path'];
    }
} catch (PDOException $e) {
    $logo_path = $default_logo; // fallback silently
}

$logo = $logo_path . '?v=' . time();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = "Please enter email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role, is_active FROM users WHERE email COLLATE utf8mb4_general_ci = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Invalid credentials.";
        } elseif (!$user['is_active']) {
            $error = "Your account is inactive. Please contact support.";
        } elseif (!password_verify($password, $user['password_hash'])) {
            $error = "Invalid credentials.";
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();

            switch ($user['role']) {
                case 'admin':
                case 'manager':
                    header("Location: admin/dashboard.php");
                    break;
                case 'employer':
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        body {
            background-color: #fff;
            font-family: 'Inter', sans-serif;
            color: #222;
            font-size: 0.85rem;
            /* Make all text smaller */
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
            font-weight: 700;
            margin-bottom: 1.2rem;
            font-size: 1.1rem;
            /* Smaller heading */
        }

        .form-label {
            font-weight: 600;
            font-size: 0.75rem;
            margin-bottom: 0.2rem;
        }

        .btn-primary {
            background-color: transparent;
            border: 2px solid #e30613;
            color: #e30613;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.35rem;
            transition: all 0.25s ease-in-out;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: #b6050e;
            border-color: #b6050e;
            color: white;
        }

        a {
            color: #cc0612;
            font-size: 0.75rem;
        }

        a:hover {
            color: #b6050e;
            text-decoration: underline;
        }

        .alert {
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
        }

        form {
            width: 100%;
            max-width: 360px;
        }

        input.form-control {
            font-size: 0.8rem;
            padding: 0.3rem 0.5rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        small {
            font-size: 0.75rem;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <?php if ($logo): ?>
            <img src="<?= htmlspecialchars($logo) ?>" alt="NorthPort Logo" class="logo" />
        <?php endif; ?>

        <h3>LOGIN</h3>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']);
                                                unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" id="email" name="email" class="form-control" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required />
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <!-- <div class="mt-3 text-center">
        <small>Don't have an account? <a href="register.php">Register here</a></small>
    </div> -->

        <div class="mt-3 text-center">
            <small class="text-muted">
                Only administrators can create user accounts.
                <br>
                Need help? <a href="mailto:admin@northport.com">Email</a> or
                <a href="user/contact_admin.php">Contact the admin</a>.
            </small>
        </div>


    </div>

</body>

</html>