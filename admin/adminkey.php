<?php
echo password_hash('admin123', PASSWORD_BCRYPT);
?>

INSERT INTO `users` 
  (`username`, `email`, `password_hash`, `role`, `is_active`, `created_at`, `updated_at`, `user_role`) 
VALUES
  ('admin', 'admin@northport.com', '$2y$10$e0NRB3XGxJGmAom6H.8lMeD2l3u8OdTqz6A2A5LGkn0Ul2F6Tqpr2', 'admin', 1, NOW(), NOW(), 'admin');