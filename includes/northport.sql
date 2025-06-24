-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2025 at 07:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `northport`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_messages`
--

CREATE TABLE `admin_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `booking_ref` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `container_id` int(11) DEFAULT NULL,
  `fleet_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Confirmed','Cancelled','Completed') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `containers`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `document_type` enum('Bill of Lading','Invoice','Packing List','Commercial Invoice','Other') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `status` enum('Draft','Final','Rejected','Archived') DEFAULT 'Draft',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_templates`
--

CREATE TABLE `document_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('Bill of Lading','Invoice','Packing List','Other') NOT NULL,
  `content_html` longtext NOT NULL,
  `last_modified_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fleets`
--

CREATE TABLE `fleets` (
  `id` int(11) NOT NULL,
  `fleet_name` varchar(100) NOT NULL,
  `type` enum('Vessel','Truck') NOT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `status` enum('Active','Inactive','Under Maintenance') DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `manufacturer` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `year_built` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fleets`
--

INSERT INTO `fleets` (`id`, `fleet_name`, `type`, `registration_no`, `capacity`, `status`, `created_at`, `updated_at`, `manufacturer`, `model`, `year_built`, `location`, `notes`) VALUES
(1, 'Pacific Voyager', 'Vessel', 'PV-2025-001', 5000, 'Active', '2025-06-24 23:11:08', '2025-06-24 23:22:42', 'Oceanic Shipyards', 'Voyager X200', 2018, 'Singapore Port', '');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `status` enum('Unpaid','Paid','Overdue','Cancelled') DEFAULT 'Unpaid',
  `issued_at` datetime DEFAULT current_timestamp(),
  `due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `roles_permissions`
--

CREATE TABLE `roles_permissions` (
  `id` int(11) NOT NULL,
  `role` enum('admin','manager','employer','user') NOT NULL,
  `module` varchar(100) NOT NULL,
  `can_view` tinyint(1) DEFAULT 0,
  `can_create` tinyint(1) DEFAULT 0,
  `can_edit` tinyint(1) DEFAULT 0,
  `can_delete` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles_permissions`
--

INSERT INTO `roles_permissions` (`id`, `role`, `module`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
(1, 'admin', 'manage_users', 1, 1, 1, 1),
(2, 'admin', 'manage_bookings', 1, 1, 1, 1),
(3, 'admin', 'manage_fleet', 1, 1, 1, 1),
(4, 'admin', 'manage_containers', 1, 1, 1, 1),
(5, 'admin', 'manage_invoices', 1, 1, 1, 1),
(6, 'admin', 'settings', 1, 1, 1, 1),
(7, 'manager', 'manage_bookings', 1, 1, 1, 0),
(8, 'manager', 'manage_invoices', 1, 1, 1, 0),
(9, 'manager', 'manage_fleet', 1, 1, 1, 0),
(10, 'manager', 'upload_documents', 1, 1, 1, 0),
(11, 'employer', 'upload_documents', 1, 1, 1, 0),
(12, 'employer', 'book_shipment', 1, 1, 0, 0),
(13, 'user', 'dashboard', 1, 0, 0, 0),
(14, 'user', 'book_shipment', 1, 1, 0, 0),
(15, 'user', 'upload_documents', 1, 1, 0, 0),
(16, 'user', 'my_invoices', 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

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
(1, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 08:35:21'),
(2, 'logo_path', 'assets/images/logo.png', '2025-06-20 05:29:08'),
(3, 'footer_text', '', '2025-06-23 08:35:21'),
(4, 'site_logo', 'assets/images/site_logo_1750489895.png', '2025-06-20 20:11:35'),
(5, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 08:35:21'),
(6, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 08:35:21'),
(7, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 08:35:21'),
(8, 'footer_address_line2', 'Sri Lanka', '2025-06-23 08:35:21'),
(9, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-24 14:21:25'),
(10, 'footer_text', '', '2025-06-24 14:21:25'),
(11, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-24 14:21:25'),
(12, 'footer_contact_phone', '+94 11 2517445', '2025-06-24 14:21:25'),
(13, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-24 14:21:25'),
(14, 'footer_address_line2', 'Sri Lanka', '2025-06-24 14:21:25'),
(15, 'footer_social_facebook', '', '2025-06-24 14:21:25'),
(16, 'footer_social_twitter', '', '2025-06-24 14:21:25'),
(17, 'footer_social_linkedin', '', '2025-06-24 14:21:25'),
(18, 'footer_social_instagram', '', '2025-06-24 14:21:25'),
(19, 'footer_shortcut_1_name', '', '2025-06-24 14:21:25'),
(20, 'footer_shortcut_1_url', '', '2025-06-24 14:21:25'),
(21, 'footer_shortcut_2_name', '', '2025-06-24 14:21:25'),
(22, 'footer_shortcut_2_url', '', '2025-06-24 14:21:25'),
(23, 'footer_shortcut_3_name', '', '2025-06-24 14:21:25'),
(24, 'footer_shortcut_3_url', '', '2025-06-24 14:21:25'),
(25, 'site_logo', 'assets/images/site_logo_1750774885.png', '2025-06-24 14:21:25'),
(26, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-24 14:21:30'),
(27, 'footer_text', '', '2025-06-24 14:21:30'),
(28, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-24 14:21:30'),
(29, 'footer_contact_phone', '+94 11 2517445', '2025-06-24 14:21:30'),
(30, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-24 14:21:30'),
(31, 'footer_address_line2', 'Sri Lanka', '2025-06-24 14:21:30'),
(32, 'footer_social_facebook', '', '2025-06-24 14:21:30'),
(33, 'footer_social_twitter', '', '2025-06-24 14:21:30'),
(34, 'footer_social_linkedin', '', '2025-06-24 14:21:30'),
(35, 'footer_social_instagram', '', '2025-06-24 14:21:30'),
(36, 'footer_shortcut_1_name', '', '2025-06-24 14:21:30'),
(37, 'footer_shortcut_1_url', '', '2025-06-24 14:21:30'),
(38, 'footer_shortcut_2_name', '', '2025-06-24 14:21:30'),
(39, 'footer_shortcut_2_url', '', '2025-06-24 14:21:30'),
(40, 'footer_shortcut_3_name', '', '2025-06-24 14:21:30'),
(41, 'footer_shortcut_3_url', '', '2025-06-24 14:21:30'),
(42, 'site_logo', 'assets/images/site_logo_1750774890.png', '2025-06-24 14:21:30');

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

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
  `department` varchar(100) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `nic_passport_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `is_active`, `created_at`, `updated_at`, `phone`, `street_address`, `city`, `state`, `postal_code`, `country`, `company_name`, `profile_pic`, `date_of_birth`, `preferences`, `notes`, `department`, `job_title`, `date_of_joining`, `nic_passport_number`) VALUES
(1, 'Yohan Koshala', 'yohankoshala@gmail.com', '$2y$10$ZEvAE7pZ4HgM5qyQ4iGPjuXqPQxGAtwjJrRztLihtRcam2c2criKO', 'user', 1, '2025-06-23 11:26:47', '2025-06-24 03:29:43', '0766446354', 'No 73, Mount Lavinia', 'colombo', 'western province', '10390', 'Sri Lanka', 'Tecro Technologies (PVT) Ltd', '', '2000-01-21', NULL, '', NULL, NULL, NULL, NULL),
(2, 'admin', 'admin@northport.com', '$2y$10$x2mFCP.UpSdTOctE3r9SRuD5tfWPw6A3UaP7frAspSiQ5egaQVtsm', 'admin', 1, '2025-06-23 12:38:27', '2025-06-24 03:21:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Vimash Kavinda', 'vimash@northport.com', '$2y$10$KYSmusBrhFjJVlnLs4GESOZQy9w6odqbaHeNPXyGl5msxnF1ND/5q', 'manager', 1, '2025-06-24 03:41:06', '2025-06-24 03:41:06', '0766446355', 'No 73, Mount Lavinia', 'colombo', 'western province', '10390', 'Sri Lanka', 'Tecro (PVT) Ltd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Christy Philip', 'philip@northport.com', '$2y$10$M.2aRUl1T/vX3FZi2OjYkOu7cs.mATiMxIvy1JMNmgR2tx67Oj5Vm', 'employer', 1, '2025-06-24 17:20:33', '2025-06-24 17:20:33', '0766446366', NULL, NULL, 'western Province', '10360', 'sri lanka', 'Aramex (PVT) Ltd', NULL, '2000-01-24', NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_ref` (`booking_ref`);

--
-- Indexes for table `containers`
--
ALTER TABLE `containers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document_templates`
--
ALTER TABLE `document_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fleets`
--
ALTER TABLE `fleets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles_permissions`
--
ALTER TABLE `roles_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_messages`
--
ALTER TABLE `admin_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `containers`
--
ALTER TABLE `containers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_templates`
--
ALTER TABLE `document_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fleets`
--
ALTER TABLE `fleets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles_permissions`
--
ALTER TABLE `roles_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
