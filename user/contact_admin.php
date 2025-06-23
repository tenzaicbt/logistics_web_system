<?php
require_once '../includes/db.php'; // Adjust if needed

$default_logo = '../assets/images/default-logo.png'; // FIXED PATH
$logo_path = $default_logo;

try {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_logo', 'logo_path')");
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    if (!empty($settings['site_logo']) && file_exists(dirname(__DIR__) . '/' . $settings['site_logo'])) {
        $logo_path = '../' . $settings['site_logo'];
    } elseif (!empty($settings['logo_path']) && file_exists(dirname(__DIR__) . '/' . $settings['logo_path'])) {
        $logo_path = '../' . $settings['logo_path'];
    }
} catch (PDOException $e) {
    $logo_path = $default_logo; // fallback silently
}

$logo = $logo_path . '?v=' . time();

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name) $errors[] = "Name is required.";
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (!$message) $errors[] = "Message is required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO admin_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);

        $success = "Your message has been sent to the administrator.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Contact Admin - NorthPort</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        body {
            background-color: #fff;
            font-family: 'Inter', sans-serif;
            color: #222;
            font-size: 0.85rem;
        }

        .contact-container {
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
        }

        .form-label {
            font-weight: 600;
            font-size: 0.75rem;
            margin-bottom: 0.2rem;
        }

        input.form-control,
        textarea.form-control {
            font-size: 0.8rem;
            padding: 0.3rem 0.5rem;
            border-radius: 4px;
            margin-bottom: 0.75rem;
        }

        .btn-primary {
            background-color: transparent;
            border: 2px solid #e30613;
            color: #e30613;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.4rem;
            transition: all 0.25s ease-in-out;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: #b6050e;
            border-color: #b6050e;
            color: white;
        }

        .alert {
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
            max-width: 720px;
            margin: 0 auto 1rem auto;
        }

        small {
            font-size: 0.75rem;
        }
    </style>
</head>

<body>
    <div class="contact-container">
        <?php if ($logo): ?>
            <img src="<?= htmlspecialchars($logo) ?>" alt="NorthPort Logo" class="logo" />
        <?php endif; ?>

        <h3>CONTACT ADMIN</h3>

        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="container my-2" style="max-width: 720px;">
            <form method="post" novalidate>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Your Name *</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">

                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">

                        <label class="form-label">Message *</label>
                        <textarea name="message" rows="5" class="form-control" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-3">Send Message</button>
            </form>
            <div class="mt-3 text-center">
                <a href="../login.php" style="color: #e30613; font-size: 0.75rem; text-decoration: none;">
                    Back to Login
                </a>
            </div>
        </div>
        <footer class="text-center py-3 mt-2">
            <div class="footer-bottom">
                &copy; <?= date('Y') ?> NorthPort Logistics Pvt Ltd. All rights reserved.
            </div>
        </footer>
</body>

</html>