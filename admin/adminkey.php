<?php

// LOGS
function logAction($pdo, $userId, $action)
{
    $stmt = $pdo->prepare("INSERT INTO logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $action,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

echo password_hash('admin123', PASSWORD_BCRYPT);
?>

INSERT INTO `users` 
  (`username`, `email`, `password_hash`, `role`, `is_active`, `created_at`, `updated_at`) 
VALUES
  ('admin', 'admin@northport.com', '$2y$10$x2mFCP.UpSdTOctE3r9SRuD5tfWPw6A3UaP7frAspSiQ5egaQVtsm', 'admin', 1, NOW(), NOW());
