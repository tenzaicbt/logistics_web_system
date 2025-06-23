

CREATE TABLE `containers` (
  `id` int(11) NOT NULL,
  `container_no` varchar(50) NOT NULL,
  `type` enum('20ft','40ft','Reefer','Open Top','Tank') NOT NULL,
  `status` enum('Available','In Use','Under Maintenance','Damaged') DEFAULT 'Available',
  `location` varchar(100) DEFAULT NULL,
  `assigned_fleet_id` int(11) DEFAULT NULL,
  `last_inspected_at` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `payment_method` enum('Bank Transfer','Credit Card','Cash','PayPal') DEFAULT 'Bank Transfer',
  `status` enum('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
  `transaction_reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-21 07:41:08'),
(2, 'logo_path', 'assets/images/logo.png', '2025-06-20 16:29:08'),
(3, 'footer_text', '', '2025-06-21 07:41:08'),
(4, 'site_logo', 'assets/images/site_logo_1750489895.png', '2025-06-21 07:11:35'),
(5, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-21 07:41:08'),
(6, 'footer_contact_phone', '+94 11 2517445', '2025-06-21 07:41:08'),
(7, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-21 07:41:08'),
(8, 'footer_address_line2', 'Sri Lanka', '2025-06-21 07:41:08'),
(9, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-21 07:41:08'),
(10, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-21 07:41:08'),
(11, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-21 07:41:08'),
(12, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-21 07:41:08'),
(13, 'footer_shortcut_1_name', '', '2025-06-21 07:41:08'),
(14, 'footer_shortcut_1_url', '', '2025-06-21 07:41:08'),
(15, 'footer_shortcut_2_name', '', '2025-06-21 07:41:08'),
(16, 'footer_shortcut_2_url', '', '2025-06-21 07:41:08'),
(17, 'footer_shortcut_3_name', '', '2025-06-21 07:41:08'),
(18, 'footer_shortcut_3_url', '', '2025-06-21 07:41:08'),
(19, 'footer_bottom_text', '&copy; 2025 NorthPort Logistics. All rights reserved.', '2025-06-21 07:29:21');


CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `shipment_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `container_id` int(11) DEFAULT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `status` enum('Pending','In Transit','Delivered','Cancelled') DEFAULT 'Pending',
  `departure_date` date DEFAULT NULL,
  `arrival_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','manager','employer','user') NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `phone` varchar(20) DEFAULT NULL,
  `street_address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `notes` text DEFAULT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

