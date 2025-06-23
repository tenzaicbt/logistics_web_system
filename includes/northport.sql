-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2025 at 08:40 PM
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 14:05:21'),
(2, 'logo_path', 'assets/images/logo.png', '2025-06-20 10:59:08'),
(3, 'footer_text', '', '2025-06-23 14:05:21'),
(4, 'site_logo', 'assets/images/site_logo_1750489895.png', '2025-06-21 01:41:35'),
(5, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 14:05:21'),
(6, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 14:05:21'),
(7, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 14:05:21'),
(8, 'footer_address_line2', 'Sri Lanka', '2025-06-23 14:05:21'),
(9, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 14:05:21'),
(10, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 14:05:21'),
(11, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 14:05:21'),
(12, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 14:05:21'),
(13, 'footer_shortcut_1_name', '', '2025-06-23 14:05:21'),
(14, 'footer_shortcut_1_url', '', '2025-06-23 14:05:21'),
(15, 'footer_shortcut_2_name', '', '2025-06-23 14:05:21'),
(16, 'footer_shortcut_2_url', '', '2025-06-23 14:05:21'),
(17, 'footer_shortcut_3_name', '', '2025-06-23 14:05:21'),
(18, 'footer_shortcut_3_url', '', '2025-06-23 14:05:21'),
(19, 'footer_bottom_text', '', '2025-06-23 14:05:21'),
(20, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 14:05:21'),
(21, 'footer_text', '', '2025-06-23 14:05:21'),
(22, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 14:05:21'),
(23, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 14:05:21'),
(24, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 14:05:21'),
(25, 'footer_address_line2', 'Sri Lanka', '2025-06-23 14:05:21'),
(26, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 14:05:21'),
(27, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 14:05:21'),
(28, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 14:05:21'),
(29, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 14:05:21'),
(30, 'footer_shortcut_1_name', '', '2025-06-23 14:05:21'),
(31, 'footer_shortcut_1_url', '', '2025-06-23 14:05:21'),
(32, 'footer_shortcut_2_name', '', '2025-06-23 14:05:21'),
(33, 'footer_shortcut_2_url', '', '2025-06-23 14:05:21'),
(34, 'footer_shortcut_3_name', '', '2025-06-23 14:05:21'),
(35, 'footer_shortcut_3_url', '', '2025-06-23 14:05:21'),
(36, 'site_logo', 'assets/images/site_logo_1750686524.png', '2025-06-23 13:48:44'),
(37, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 14:05:21'),
(38, 'footer_text', '', '2025-06-23 14:05:21'),
(39, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 14:05:21'),
(40, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 14:05:21'),
(41, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 14:05:21'),
(42, 'footer_address_line2', 'Sri Lanka', '2025-06-23 14:05:21'),
(43, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 14:05:21'),
(44, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 14:05:21'),
(45, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 14:05:21'),
(46, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 14:05:21'),
(47, 'footer_shortcut_1_name', '', '2025-06-23 14:05:21'),
(48, 'footer_shortcut_1_url', '', '2025-06-23 14:05:21'),
(49, 'footer_shortcut_2_name', '', '2025-06-23 14:05:21'),
(50, 'footer_shortcut_2_url', '', '2025-06-23 14:05:21'),
(51, 'footer_shortcut_3_name', '', '2025-06-23 14:05:21'),
(52, 'footer_shortcut_3_url', '', '2025-06-23 14:05:21'),
(53, 'site_logo', 'assets/images/site_logo_1750686689.png', '2025-06-23 13:51:29'),
(54, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 14:05:21'),
(55, 'footer_text', '', '2025-06-23 14:05:21'),
(56, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 14:05:21'),
(57, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 14:05:21'),
(58, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 14:05:21'),
(59, 'footer_address_line2', 'Sri Lanka', '2025-06-23 14:05:21'),
(60, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 14:05:21'),
(61, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 14:05:21'),
(62, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 14:05:21'),
(63, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 14:05:21'),
(64, 'footer_shortcut_1_name', '', '2025-06-23 14:05:21'),
(65, 'footer_shortcut_1_url', '', '2025-06-23 14:05:21'),
(66, 'footer_shortcut_2_name', '', '2025-06-23 14:05:21'),
(67, 'footer_shortcut_2_url', '', '2025-06-23 14:05:21'),
(68, 'footer_shortcut_3_name', '', '2025-06-23 14:05:21'),
(69, 'footer_shortcut_3_url', '', '2025-06-23 14:05:21'),
(70, 'site_logo', 'assets/images/site_logo_1750686862.png', '2025-06-23 13:54:22'),
(71, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 14:05:21'),
(72, 'footer_text', '', '2025-06-23 14:05:21'),
(73, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 14:05:21'),
(74, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 14:05:21'),
(75, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 14:05:21'),
(76, 'footer_address_line2', 'Sri Lanka', '2025-06-23 14:05:21'),
(77, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 14:05:21'),
(78, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 14:05:21'),
(79, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 14:05:21'),
(80, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 14:05:21'),
(81, 'footer_shortcut_1_name', '', '2025-06-23 14:05:21'),
(82, 'footer_shortcut_1_url', '', '2025-06-23 14:05:21'),
(83, 'footer_shortcut_2_name', '', '2025-06-23 14:05:21'),
(84, 'footer_shortcut_2_url', '', '2025-06-23 14:05:21'),
(85, 'footer_shortcut_3_name', '', '2025-06-23 14:05:21'),
(86, 'footer_shortcut_3_url', '', '2025-06-23 14:05:21'),
(87, 'site_logo', 'assets/images/site_logo_1750686921.png', '2025-06-23 13:55:21'),
(88, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 14:05:21'),
(89, 'footer_text', '', '2025-06-23 14:05:21'),
(90, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 14:05:21'),
(91, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 14:05:21'),
(92, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 14:05:21'),
(93, 'footer_address_line2', 'Sri Lanka', '2025-06-23 14:05:21'),
(94, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 14:05:21'),
(95, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 14:05:21'),
(96, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 14:05:21'),
(97, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 14:05:21'),
(98, 'footer_shortcut_1_name', '', '2025-06-23 14:05:21'),
(99, 'footer_shortcut_1_url', '', '2025-06-23 14:05:21'),
(100, 'footer_shortcut_2_name', '', '2025-06-23 14:05:21'),
(101, 'footer_shortcut_2_url', '', '2025-06-23 14:05:21'),
(102, 'footer_shortcut_3_name', '', '2025-06-23 14:05:21'),
(103, 'footer_shortcut_3_url', '', '2025-06-23 14:05:21'),
(104, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 14:05:21'),
(105, 'footer_text', '', '2025-06-23 14:05:21'),
(106, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 14:05:21'),
(107, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 14:05:21'),
(108, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 14:05:21'),
(109, 'footer_address_line2', 'Sri Lanka', '2025-06-23 14:05:21'),
(110, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 14:05:21'),
(111, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 14:05:21'),
(112, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 14:05:21'),
(113, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 14:05:21'),
(114, 'footer_shortcut_1_name', '', '2025-06-23 14:05:21'),
(115, 'footer_shortcut_1_url', '', '2025-06-23 14:05:21'),
(116, 'footer_shortcut_2_name', '', '2025-06-23 14:05:21'),
(117, 'footer_shortcut_2_url', '', '2025-06-23 14:05:21'),
(118, 'footer_shortcut_3_name', '', '2025-06-23 14:05:21'),
(119, 'footer_shortcut_3_url', '', '2025-06-23 14:05:21'),
(120, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 14:05:21'),
(121, 'footer_text', '', '2025-06-23 14:05:21'),
(122, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 14:05:21'),
(123, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 14:05:21'),
(124, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 14:05:21'),
(125, 'footer_address_line2', 'Sri Lanka', '2025-06-23 14:05:21'),
(126, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 14:05:21'),
(127, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 14:05:21'),
(128, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 14:05:21'),
(129, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 14:05:21'),
(130, 'footer_shortcut_1_name', '', '2025-06-23 14:05:21'),
(131, 'footer_shortcut_1_url', '', '2025-06-23 14:05:21'),
(132, 'footer_shortcut_2_name', '', '2025-06-23 14:05:21'),
(133, 'footer_shortcut_2_url', '', '2025-06-23 14:05:21'),
(134, 'footer_shortcut_3_name', '', '2025-06-23 14:05:21'),
(135, 'footer_shortcut_3_url', '', '2025-06-23 14:05:21'),
(136, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 14:05:21'),
(137, 'footer_text', '', '2025-06-23 14:05:21'),
(138, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 14:05:21'),
(139, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 14:05:21'),
(140, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 14:05:21'),
(141, 'footer_address_line2', 'Sri Lanka', '2025-06-23 14:05:21'),
(142, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 14:05:21'),
(143, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 14:05:21'),
(144, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 14:05:21'),
(145, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 14:05:21'),
(146, 'footer_shortcut_1_name', '', '2025-06-23 14:05:21'),
(147, 'footer_shortcut_1_url', '', '2025-06-23 14:05:21'),
(148, 'footer_shortcut_2_name', '', '2025-06-23 14:05:21'),
(149, 'footer_shortcut_2_url', '', '2025-06-23 14:05:21'),
(150, 'footer_shortcut_3_name', '', '2025-06-23 14:05:21'),
(151, 'footer_shortcut_3_url', '', '2025-06-23 14:05:21'),
(152, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 14:05:45'),
(153, 'footer_text', '', '2025-06-23 14:05:45'),
(154, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 14:05:45'),
(155, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 14:05:45'),
(156, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 14:05:45'),
(157, 'footer_address_line2', 'Sri Lanka', '2025-06-23 14:05:45'),
(158, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 14:05:45'),
(159, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 14:05:45'),
(160, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 14:05:45'),
(161, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 14:05:45'),
(162, 'footer_shortcut_1_name', '', '2025-06-23 14:05:45'),
(163, 'footer_shortcut_1_url', '', '2025-06-23 14:05:45'),
(164, 'footer_shortcut_2_name', '', '2025-06-23 14:05:45'),
(165, 'footer_shortcut_2_url', '', '2025-06-23 14:05:45'),
(166, 'footer_shortcut_3_name', '', '2025-06-23 14:05:45'),
(167, 'footer_shortcut_3_url', '', '2025-06-23 14:05:45'),
(168, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 14:07:26'),
(169, 'footer_text', '', '2025-06-23 14:07:26'),
(170, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 14:07:26'),
(171, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 14:07:26'),
(172, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 14:07:26'),
(173, 'footer_address_line2', 'Sri Lanka', '2025-06-23 14:07:26'),
(174, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 14:07:26'),
(175, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 14:07:26'),
(176, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 14:07:26'),
(177, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 14:07:26'),
(178, 'footer_shortcut_1_name', '', '2025-06-23 14:07:26'),
(179, 'footer_shortcut_1_url', '', '2025-06-23 14:07:26'),
(180, 'footer_shortcut_2_name', '', '2025-06-23 14:07:26'),
(181, 'footer_shortcut_2_url', '', '2025-06-23 14:07:26'),
(182, 'footer_shortcut_3_name', '', '2025-06-23 14:07:26'),
(183, 'footer_shortcut_3_url', '', '2025-06-23 14:07:26'),
(184, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:26:34'),
(185, 'footer_text', '', '2025-06-23 15:26:34'),
(186, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:26:34'),
(187, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:26:34'),
(188, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:26:34'),
(189, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:26:34'),
(190, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:26:34'),
(191, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:26:34'),
(192, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:26:34'),
(193, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:26:34'),
(194, 'footer_shortcut_1_name', '', '2025-06-23 15:26:34'),
(195, 'footer_shortcut_1_url', '', '2025-06-23 15:26:34'),
(196, 'footer_shortcut_2_name', '', '2025-06-23 15:26:34'),
(197, 'footer_shortcut_2_url', '', '2025-06-23 15:26:34'),
(198, 'footer_shortcut_3_name', '', '2025-06-23 15:26:34'),
(199, 'footer_shortcut_3_url', '', '2025-06-23 15:26:34'),
(200, 'site_logo', 'assets/images/site_logo_1750692394.png', '2025-06-23 15:26:34'),
(201, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:26:42'),
(202, 'footer_text', '', '2025-06-23 15:26:42'),
(203, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:26:42'),
(204, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:26:42'),
(205, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:26:42'),
(206, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:26:42'),
(207, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:26:42'),
(208, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:26:42'),
(209, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:26:42'),
(210, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:26:42'),
(211, 'footer_shortcut_1_name', '', '2025-06-23 15:26:42'),
(212, 'footer_shortcut_1_url', '', '2025-06-23 15:26:42'),
(213, 'footer_shortcut_2_name', '', '2025-06-23 15:26:42'),
(214, 'footer_shortcut_2_url', '', '2025-06-23 15:26:42'),
(215, 'footer_shortcut_3_name', '', '2025-06-23 15:26:42'),
(216, 'footer_shortcut_3_url', '', '2025-06-23 15:26:42'),
(217, 'site_logo', 'assets/images/site_logo_1750692402.png', '2025-06-23 15:26:42'),
(218, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:28:09'),
(219, 'footer_text', '', '2025-06-23 15:28:09'),
(220, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:28:09'),
(221, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:28:09'),
(222, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:28:09'),
(223, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:28:09'),
(224, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:28:09'),
(225, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:28:09'),
(226, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:28:09'),
(227, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:28:09'),
(228, 'footer_shortcut_1_name', '', '2025-06-23 15:28:09'),
(229, 'footer_shortcut_1_url', '', '2025-06-23 15:28:09'),
(230, 'footer_shortcut_2_name', '', '2025-06-23 15:28:09'),
(231, 'footer_shortcut_2_url', '', '2025-06-23 15:28:09'),
(232, 'footer_shortcut_3_name', '', '2025-06-23 15:28:09'),
(233, 'footer_shortcut_3_url', '', '2025-06-23 15:28:09'),
(234, 'site_logo', 'assets/images/site_logo_1750692489.png', '2025-06-23 15:28:09'),
(235, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:28:47'),
(236, 'footer_text', '', '2025-06-23 15:28:47'),
(237, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:28:47'),
(238, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:28:47'),
(239, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:28:47'),
(240, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:28:47'),
(241, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:28:47'),
(242, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:28:47'),
(243, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:28:47'),
(244, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:28:47'),
(245, 'footer_shortcut_1_name', '', '2025-06-23 15:28:47'),
(246, 'footer_shortcut_1_url', '', '2025-06-23 15:28:47'),
(247, 'footer_shortcut_2_name', '', '2025-06-23 15:28:47'),
(248, 'footer_shortcut_2_url', '', '2025-06-23 15:28:47'),
(249, 'footer_shortcut_3_name', '', '2025-06-23 15:28:47'),
(250, 'footer_shortcut_3_url', '', '2025-06-23 15:28:47'),
(251, 'site_logo', 'assets/images/site_logo_1750692527.jpg', '2025-06-23 15:28:47'),
(252, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:28:51'),
(253, 'footer_text', '', '2025-06-23 15:28:51'),
(254, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:28:51'),
(255, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:28:51'),
(256, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:28:51'),
(257, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:28:51'),
(258, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:28:51'),
(259, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:28:51'),
(260, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:28:51'),
(261, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:28:51'),
(262, 'footer_shortcut_1_name', '', '2025-06-23 15:28:51'),
(263, 'footer_shortcut_1_url', '', '2025-06-23 15:28:51'),
(264, 'footer_shortcut_2_name', '', '2025-06-23 15:28:51'),
(265, 'footer_shortcut_2_url', '', '2025-06-23 15:28:51'),
(266, 'footer_shortcut_3_name', '', '2025-06-23 15:28:51'),
(267, 'footer_shortcut_3_url', '', '2025-06-23 15:28:51'),
(268, 'site_logo', 'assets/images/site_logo_1750692531.jpg', '2025-06-23 15:28:51'),
(269, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:45:07'),
(270, 'footer_text', '', '2025-06-23 15:45:07'),
(271, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:45:07'),
(272, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:45:07'),
(273, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:45:07'),
(274, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:45:07'),
(275, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:45:07'),
(276, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:45:07'),
(277, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:45:07'),
(278, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:45:07'),
(279, 'footer_shortcut_1_name', '', '2025-06-23 15:45:07'),
(280, 'footer_shortcut_1_url', '', '2025-06-23 15:45:07'),
(281, 'footer_shortcut_2_name', '', '2025-06-23 15:45:07'),
(282, 'footer_shortcut_2_url', '', '2025-06-23 15:45:07'),
(283, 'footer_shortcut_3_name', '', '2025-06-23 15:45:07'),
(284, 'footer_shortcut_3_url', '', '2025-06-23 15:45:07'),
(285, 'site_logo', 'assets/images/site_logo_1750693507.jpg', '2025-06-23 15:45:07'),
(286, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:45:17'),
(287, 'footer_text', '', '2025-06-23 15:45:17'),
(288, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:45:17'),
(289, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:45:17'),
(290, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:45:17'),
(291, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:45:17'),
(292, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:45:17'),
(293, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:45:17'),
(294, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:45:17'),
(295, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:45:17'),
(296, 'footer_shortcut_1_name', '', '2025-06-23 15:45:17'),
(297, 'footer_shortcut_1_url', '', '2025-06-23 15:45:17'),
(298, 'footer_shortcut_2_name', '', '2025-06-23 15:45:17'),
(299, 'footer_shortcut_2_url', '', '2025-06-23 15:45:17'),
(300, 'footer_shortcut_3_name', '', '2025-06-23 15:45:17'),
(301, 'footer_shortcut_3_url', '', '2025-06-23 15:45:17'),
(302, 'site_logo', 'assets/images/site_logo_1750693517.jpg', '2025-06-23 15:45:17'),
(303, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:48:47'),
(304, 'footer_text', '', '2025-06-23 15:48:47'),
(305, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:48:47'),
(306, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:48:47'),
(307, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:48:47'),
(308, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:48:47'),
(309, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:48:47'),
(310, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:48:47'),
(311, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:48:47'),
(312, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:48:47'),
(313, 'footer_shortcut_1_name', '', '2025-06-23 15:48:47'),
(314, 'footer_shortcut_1_url', '', '2025-06-23 15:48:47'),
(315, 'footer_shortcut_2_name', '', '2025-06-23 15:48:47'),
(316, 'footer_shortcut_2_url', '', '2025-06-23 15:48:47'),
(317, 'footer_shortcut_3_name', '', '2025-06-23 15:48:47'),
(318, 'footer_shortcut_3_url', '', '2025-06-23 15:48:47'),
(319, 'site_logo', 'assets/images/site_logo_1750693727.jpg', '2025-06-23 15:48:47'),
(320, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:48:56'),
(321, 'footer_text', '', '2025-06-23 15:48:56'),
(322, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:48:56'),
(323, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:48:56'),
(324, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:48:56'),
(325, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:48:56'),
(326, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:48:56'),
(327, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:48:56'),
(328, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:48:56'),
(329, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:48:56'),
(330, 'footer_shortcut_1_name', '', '2025-06-23 15:48:56'),
(331, 'footer_shortcut_1_url', '', '2025-06-23 15:48:56'),
(332, 'footer_shortcut_2_name', '', '2025-06-23 15:48:56'),
(333, 'footer_shortcut_2_url', '', '2025-06-23 15:48:56'),
(334, 'footer_shortcut_3_name', '', '2025-06-23 15:48:56'),
(335, 'footer_shortcut_3_url', '', '2025-06-23 15:48:56'),
(336, 'site_logo', 'assets/images/site_logo_1750693736.jpg', '2025-06-23 15:48:56'),
(337, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:49:38'),
(338, 'footer_text', '', '2025-06-23 15:49:38'),
(339, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:49:38'),
(340, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:49:38'),
(341, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:49:38'),
(342, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:49:38'),
(343, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:49:38'),
(344, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:49:38'),
(345, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:49:38'),
(346, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:49:38'),
(347, 'footer_shortcut_1_name', '', '2025-06-23 15:49:38'),
(348, 'footer_shortcut_1_url', '', '2025-06-23 15:49:38'),
(349, 'footer_shortcut_2_name', '', '2025-06-23 15:49:38'),
(350, 'footer_shortcut_2_url', '', '2025-06-23 15:49:38'),
(351, 'footer_shortcut_3_name', '', '2025-06-23 15:49:38'),
(352, 'footer_shortcut_3_url', '', '2025-06-23 15:49:38'),
(353, 'site_logo', 'assets/images/site_logo_1750693778.jpg', '2025-06-23 15:49:38'),
(354, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:52:42'),
(355, 'footer_text', '', '2025-06-23 15:52:42'),
(356, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:52:42'),
(357, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:52:42'),
(358, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:52:42'),
(359, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:52:42'),
(360, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:52:42'),
(361, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:52:42'),
(362, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:52:42'),
(363, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:52:42'),
(364, 'footer_shortcut_1_name', '', '2025-06-23 15:52:42'),
(365, 'footer_shortcut_1_url', '', '2025-06-23 15:52:42'),
(366, 'footer_shortcut_2_name', '', '2025-06-23 15:52:42'),
(367, 'footer_shortcut_2_url', '', '2025-06-23 15:52:42'),
(368, 'footer_shortcut_3_name', '', '2025-06-23 15:52:42'),
(369, 'footer_shortcut_3_url', '', '2025-06-23 15:52:42'),
(370, 'site_logo', 'assets/images/site_logo_1750693962.jpg', '2025-06-23 15:52:42'),
(371, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:52:54'),
(372, 'footer_text', '', '2025-06-23 15:52:54'),
(373, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:52:54'),
(374, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:52:54'),
(375, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:52:54'),
(376, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:52:54'),
(377, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:52:54'),
(378, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:52:54'),
(379, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:52:54'),
(380, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:52:54'),
(381, 'footer_shortcut_1_name', '', '2025-06-23 15:52:54'),
(382, 'footer_shortcut_1_url', '', '2025-06-23 15:52:54'),
(383, 'footer_shortcut_2_name', '', '2025-06-23 15:52:54'),
(384, 'footer_shortcut_2_url', '', '2025-06-23 15:52:54'),
(385, 'footer_shortcut_3_name', '', '2025-06-23 15:52:54'),
(386, 'footer_shortcut_3_url', '', '2025-06-23 15:52:54'),
(387, 'site_logo', 'assets/images/site_logo_1750693974.jpg', '2025-06-23 15:52:54'),
(388, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:53:06'),
(389, 'footer_text', '', '2025-06-23 15:53:06'),
(390, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:53:06'),
(391, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:53:06'),
(392, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:53:06'),
(393, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:53:06'),
(394, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:53:06'),
(395, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:53:06'),
(396, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:53:06'),
(397, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:53:06'),
(398, 'footer_shortcut_1_name', '', '2025-06-23 15:53:06'),
(399, 'footer_shortcut_1_url', '', '2025-06-23 15:53:06'),
(400, 'footer_shortcut_2_name', '', '2025-06-23 15:53:06'),
(401, 'footer_shortcut_2_url', '', '2025-06-23 15:53:06'),
(402, 'footer_shortcut_3_name', '', '2025-06-23 15:53:06'),
(403, 'footer_shortcut_3_url', '', '2025-06-23 15:53:06'),
(404, 'site_logo', 'assets/images/site_logo_1750693986.jpg', '2025-06-23 15:53:06'),
(405, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:53:54'),
(406, 'footer_text', '', '2025-06-23 15:53:54'),
(407, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:53:54'),
(408, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:53:54'),
(409, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:53:54'),
(410, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:53:54'),
(411, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:53:54'),
(412, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:53:54'),
(413, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:53:54'),
(414, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:53:54'),
(415, 'footer_shortcut_1_name', '', '2025-06-23 15:53:54'),
(416, 'footer_shortcut_1_url', '', '2025-06-23 15:53:54'),
(417, 'footer_shortcut_2_name', '', '2025-06-23 15:53:54'),
(418, 'footer_shortcut_2_url', '', '2025-06-23 15:53:54'),
(419, 'footer_shortcut_3_name', '', '2025-06-23 15:53:54'),
(420, 'footer_shortcut_3_url', '', '2025-06-23 15:53:54'),
(421, 'site_logo', 'assets/images/site_logo_1750694034.jpg', '2025-06-23 15:53:54'),
(422, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:54:14'),
(423, 'footer_text', '', '2025-06-23 15:54:14'),
(424, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:54:14'),
(425, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:54:14'),
(426, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:54:14'),
(427, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:54:14'),
(428, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:54:14'),
(429, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:54:14'),
(430, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:54:14'),
(431, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:54:14'),
(432, 'footer_shortcut_1_name', '', '2025-06-23 15:54:14'),
(433, 'footer_shortcut_1_url', '', '2025-06-23 15:54:14'),
(434, 'footer_shortcut_2_name', '', '2025-06-23 15:54:14'),
(435, 'footer_shortcut_2_url', '', '2025-06-23 15:54:14'),
(436, 'footer_shortcut_3_name', '', '2025-06-23 15:54:14'),
(437, 'footer_shortcut_3_url', '', '2025-06-23 15:54:14'),
(438, 'site_logo', 'assets/images/site_logo_1750694054.png', '2025-06-23 15:54:14'),
(439, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 15:54:18'),
(440, 'footer_text', '', '2025-06-23 15:54:18'),
(441, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 15:54:18'),
(442, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 15:54:18'),
(443, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 15:54:18'),
(444, 'footer_address_line2', 'Sri Lanka', '2025-06-23 15:54:18'),
(445, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 15:54:18'),
(446, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 15:54:18'),
(447, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 15:54:18'),
(448, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 15:54:19'),
(449, 'footer_shortcut_1_name', '', '2025-06-23 15:54:19'),
(450, 'footer_shortcut_1_url', '', '2025-06-23 15:54:19'),
(451, 'footer_shortcut_2_name', '', '2025-06-23 15:54:19'),
(452, 'footer_shortcut_2_url', '', '2025-06-23 15:54:19'),
(453, 'footer_shortcut_3_name', '', '2025-06-23 15:54:19'),
(454, 'footer_shortcut_3_url', '', '2025-06-23 15:54:19'),
(455, 'site_logo', 'assets/images/site_logo_1750694059.png', '2025-06-23 15:54:19'),
(456, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:02:23'),
(457, 'footer_text', '', '2025-06-23 16:02:23'),
(458, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:02:23'),
(459, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:02:23'),
(460, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:02:23'),
(461, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:02:23'),
(462, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:02:23'),
(463, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:02:23'),
(464, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:02:23'),
(465, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:02:23'),
(466, 'footer_shortcut_1_name', '', '2025-06-23 16:02:23'),
(467, 'footer_shortcut_1_url', '', '2025-06-23 16:02:23'),
(468, 'footer_shortcut_2_name', '', '2025-06-23 16:02:23'),
(469, 'footer_shortcut_2_url', '', '2025-06-23 16:02:23'),
(470, 'footer_shortcut_3_name', '', '2025-06-23 16:02:23'),
(471, 'footer_shortcut_3_url', '', '2025-06-23 16:02:23'),
(472, 'site_logo', 'assets/images/site_logo_1750694543.png', '2025-06-23 16:02:23'),
(473, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:02:27'),
(474, 'footer_text', '', '2025-06-23 16:02:27'),
(475, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:02:27'),
(476, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:02:27'),
(477, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:02:27'),
(478, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:02:27'),
(479, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:02:27'),
(480, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:02:27'),
(481, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:02:27'),
(482, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:02:27'),
(483, 'footer_shortcut_1_name', '', '2025-06-23 16:02:27'),
(484, 'footer_shortcut_1_url', '', '2025-06-23 16:02:27'),
(485, 'footer_shortcut_2_name', '', '2025-06-23 16:02:27'),
(486, 'footer_shortcut_2_url', '', '2025-06-23 16:02:27'),
(487, 'footer_shortcut_3_name', '', '2025-06-23 16:02:27'),
(488, 'footer_shortcut_3_url', '', '2025-06-23 16:02:27'),
(489, 'site_logo', 'assets/images/site_logo_1750694547.png', '2025-06-23 16:02:27'),
(490, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:05:08'),
(491, 'footer_text', '', '2025-06-23 16:05:08'),
(492, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:05:08'),
(493, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:05:08'),
(494, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:05:08'),
(495, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:05:08'),
(496, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:05:08'),
(497, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:05:08'),
(498, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:05:08'),
(499, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:05:08'),
(500, 'footer_shortcut_1_name', '', '2025-06-23 16:05:08'),
(501, 'footer_shortcut_1_url', '', '2025-06-23 16:05:08'),
(502, 'footer_shortcut_2_name', '', '2025-06-23 16:05:08'),
(503, 'footer_shortcut_2_url', '', '2025-06-23 16:05:08'),
(504, 'footer_shortcut_3_name', '', '2025-06-23 16:05:08'),
(505, 'footer_shortcut_3_url', '', '2025-06-23 16:05:08'),
(506, 'site_logo', 'assets/images/site_logo_1750694708.png', '2025-06-23 16:05:08'),
(507, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:05:18'),
(508, 'footer_text', '', '2025-06-23 16:05:18'),
(509, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:05:18'),
(510, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:05:18'),
(511, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:05:18'),
(512, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:05:18'),
(513, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:05:18'),
(514, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:05:18'),
(515, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:05:18'),
(516, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:05:18'),
(517, 'footer_shortcut_1_name', '', '2025-06-23 16:05:18'),
(518, 'footer_shortcut_1_url', '', '2025-06-23 16:05:18'),
(519, 'footer_shortcut_2_name', '', '2025-06-23 16:05:18'),
(520, 'footer_shortcut_2_url', '', '2025-06-23 16:05:18'),
(521, 'footer_shortcut_3_name', '', '2025-06-23 16:05:18'),
(522, 'footer_shortcut_3_url', '', '2025-06-23 16:05:18'),
(523, 'site_logo', 'assets/images/site_logo_1750694718.png', '2025-06-23 16:05:18'),
(524, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:06:00'),
(525, 'footer_text', '', '2025-06-23 16:06:00'),
(526, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:06:00'),
(527, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:06:00'),
(528, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:06:00'),
(529, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:06:00'),
(530, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:06:00'),
(531, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:06:00'),
(532, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:06:00'),
(533, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:06:00'),
(534, 'footer_shortcut_1_name', '', '2025-06-23 16:06:00'),
(535, 'footer_shortcut_1_url', '', '2025-06-23 16:06:00'),
(536, 'footer_shortcut_2_name', '', '2025-06-23 16:06:00'),
(537, 'footer_shortcut_2_url', '', '2025-06-23 16:06:00'),
(538, 'footer_shortcut_3_name', '', '2025-06-23 16:06:00'),
(539, 'footer_shortcut_3_url', '', '2025-06-23 16:06:00'),
(540, 'site_logo', 'assets/images/site_logo_1750694760.png', '2025-06-23 16:06:00'),
(541, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:06:30'),
(542, 'footer_text', '', '2025-06-23 16:06:30'),
(543, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:06:30'),
(544, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:06:30'),
(545, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:06:30'),
(546, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:06:30'),
(547, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:06:30'),
(548, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:06:30'),
(549, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:06:30'),
(550, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:06:30'),
(551, 'footer_shortcut_1_name', '', '2025-06-23 16:06:30'),
(552, 'footer_shortcut_1_url', '', '2025-06-23 16:06:30'),
(553, 'footer_shortcut_2_name', '', '2025-06-23 16:06:30'),
(554, 'footer_shortcut_2_url', '', '2025-06-23 16:06:30'),
(555, 'footer_shortcut_3_name', '', '2025-06-23 16:06:30'),
(556, 'footer_shortcut_3_url', '', '2025-06-23 16:06:30'),
(557, 'site_logo', 'assets/images/site_logo_1750694790.png', '2025-06-23 16:06:30'),
(558, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:15:09'),
(559, 'footer_text', '', '2025-06-23 16:15:09'),
(560, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:15:09'),
(561, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:15:09'),
(562, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:15:09'),
(563, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:15:09'),
(564, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:15:09'),
(565, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:15:09'),
(566, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:15:09'),
(567, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:15:09'),
(568, 'footer_shortcut_1_name', '', '2025-06-23 16:15:09'),
(569, 'footer_shortcut_1_url', '', '2025-06-23 16:15:09'),
(570, 'footer_shortcut_2_name', '', '2025-06-23 16:15:09'),
(571, 'footer_shortcut_2_url', '', '2025-06-23 16:15:09'),
(572, 'footer_shortcut_3_name', '', '2025-06-23 16:15:09'),
(573, 'footer_shortcut_3_url', '', '2025-06-23 16:15:09'),
(574, 'site_logo', 'assets/images/site_logo_1750695309.png', '2025-06-23 16:15:09'),
(575, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:15:16'),
(576, 'footer_text', '', '2025-06-23 16:15:16'),
(577, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:15:16'),
(578, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:15:16'),
(579, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:15:16'),
(580, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:15:16'),
(581, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:15:16'),
(582, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:15:16'),
(583, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:15:16'),
(584, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:15:16'),
(585, 'footer_shortcut_1_name', '', '2025-06-23 16:15:16'),
(586, 'footer_shortcut_1_url', '', '2025-06-23 16:15:16'),
(587, 'footer_shortcut_2_name', '', '2025-06-23 16:15:16'),
(588, 'footer_shortcut_2_url', '', '2025-06-23 16:15:16'),
(589, 'footer_shortcut_3_name', '', '2025-06-23 16:15:16'),
(590, 'footer_shortcut_3_url', '', '2025-06-23 16:15:16'),
(591, 'site_logo', 'assets/images/site_logo_1750695316.png', '2025-06-23 16:15:16'),
(592, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:15:46'),
(593, 'footer_text', '', '2025-06-23 16:15:46'),
(594, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:15:46'),
(595, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:15:46'),
(596, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:15:46'),
(597, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:15:46'),
(598, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:15:46'),
(599, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:15:46'),
(600, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:15:46'),
(601, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:15:46'),
(602, 'footer_shortcut_1_name', '', '2025-06-23 16:15:46'),
(603, 'footer_shortcut_1_url', '', '2025-06-23 16:15:46'),
(604, 'footer_shortcut_2_name', '', '2025-06-23 16:15:46'),
(605, 'footer_shortcut_2_url', '', '2025-06-23 16:15:46'),
(606, 'footer_shortcut_3_name', '', '2025-06-23 16:15:46'),
(607, 'footer_shortcut_3_url', '', '2025-06-23 16:15:46'),
(608, 'site_logo', 'assets/images/site_logo_1750695346.png', '2025-06-23 16:15:46'),
(609, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:16:01'),
(610, 'footer_text', '', '2025-06-23 16:16:01'),
(611, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:16:01'),
(612, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:16:01'),
(613, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:16:01'),
(614, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:16:01'),
(615, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:16:01'),
(616, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:16:01'),
(617, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:16:01'),
(618, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:16:01'),
(619, 'footer_shortcut_1_name', '', '2025-06-23 16:16:01'),
(620, 'footer_shortcut_1_url', '', '2025-06-23 16:16:01'),
(621, 'footer_shortcut_2_name', '', '2025-06-23 16:16:01'),
(622, 'footer_shortcut_2_url', '', '2025-06-23 16:16:01'),
(623, 'footer_shortcut_3_name', '', '2025-06-23 16:16:01'),
(624, 'footer_shortcut_3_url', '', '2025-06-23 16:16:01'),
(625, 'site_logo', 'assets/images/site_logo_1750695361.png', '2025-06-23 16:16:01'),
(626, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:16:09'),
(627, 'footer_text', '', '2025-06-23 16:16:09'),
(628, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:16:09'),
(629, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:16:09'),
(630, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:16:09'),
(631, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:16:09'),
(632, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:16:09'),
(633, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:16:09'),
(634, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:16:09'),
(635, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:16:09'),
(636, 'footer_shortcut_1_name', '', '2025-06-23 16:16:09'),
(637, 'footer_shortcut_1_url', '', '2025-06-23 16:16:09'),
(638, 'footer_shortcut_2_name', '', '2025-06-23 16:16:09'),
(639, 'footer_shortcut_2_url', '', '2025-06-23 16:16:09'),
(640, 'footer_shortcut_3_name', '', '2025-06-23 16:16:09'),
(641, 'footer_shortcut_3_url', '', '2025-06-23 16:16:09'),
(642, 'site_logo', 'assets/images/site_logo_1750695369.png', '2025-06-23 16:16:09'),
(643, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:16:21'),
(644, 'footer_text', '', '2025-06-23 16:16:21'),
(645, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:16:21'),
(646, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:16:21'),
(647, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:16:21'),
(648, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:16:21'),
(649, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:16:21'),
(650, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:16:21'),
(651, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:16:21'),
(652, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:16:21'),
(653, 'footer_shortcut_1_name', '', '2025-06-23 16:16:21'),
(654, 'footer_shortcut_1_url', '', '2025-06-23 16:16:21'),
(655, 'footer_shortcut_2_name', '', '2025-06-23 16:16:21'),
(656, 'footer_shortcut_2_url', '', '2025-06-23 16:16:21'),
(657, 'footer_shortcut_3_name', '', '2025-06-23 16:16:21'),
(658, 'footer_shortcut_3_url', '', '2025-06-23 16:16:21'),
(659, 'site_logo', 'assets/images/site_logo_1750695381.jpg', '2025-06-23 16:16:21'),
(660, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:16:23'),
(661, 'footer_text', '', '2025-06-23 16:16:23'),
(662, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:16:23'),
(663, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:16:23'),
(664, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:16:23'),
(665, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:16:23'),
(666, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:16:23'),
(667, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:16:23'),
(668, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:16:23'),
(669, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:16:23'),
(670, 'footer_shortcut_1_name', '', '2025-06-23 16:16:23'),
(671, 'footer_shortcut_1_url', '', '2025-06-23 16:16:23'),
(672, 'footer_shortcut_2_name', '', '2025-06-23 16:16:23'),
(673, 'footer_shortcut_2_url', '', '2025-06-23 16:16:23'),
(674, 'footer_shortcut_3_name', '', '2025-06-23 16:16:23'),
(675, 'footer_shortcut_3_url', '', '2025-06-23 16:16:23'),
(676, 'site_logo', 'assets/images/site_logo_1750695383.jpg', '2025-06-23 16:16:23'),
(677, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 16:17:02'),
(678, 'footer_text', '', '2025-06-23 16:17:02'),
(679, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 16:17:02'),
(680, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 16:17:02'),
(681, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 16:17:02'),
(682, 'footer_address_line2', 'Sri Lanka', '2025-06-23 16:17:02'),
(683, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 16:17:02'),
(684, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 16:17:02'),
(685, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 16:17:02'),
(686, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 16:17:02'),
(687, 'footer_shortcut_1_name', '', '2025-06-23 16:17:02'),
(688, 'footer_shortcut_1_url', '', '2025-06-23 16:17:02'),
(689, 'footer_shortcut_2_name', '', '2025-06-23 16:17:02');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(690, 'footer_shortcut_2_url', '', '2025-06-23 16:17:02'),
(691, 'footer_shortcut_3_name', '', '2025-06-23 16:17:02'),
(692, 'footer_shortcut_3_url', '', '2025-06-23 16:17:02'),
(693, 'site_logo', 'assets/images/site_logo_1750695422.png', '2025-06-23 16:17:02'),
(694, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 17:46:56'),
(695, 'footer_text', '', '2025-06-23 17:46:56'),
(696, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 17:46:56'),
(697, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 17:46:56'),
(698, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 17:46:56'),
(699, 'footer_address_line2', 'Sri Lanka', '2025-06-23 17:46:56'),
(700, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 17:46:56'),
(701, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 17:46:56'),
(702, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 17:46:56'),
(703, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 17:46:56'),
(704, 'footer_shortcut_1_name', '', '2025-06-23 17:46:56'),
(705, 'footer_shortcut_1_url', '', '2025-06-23 17:46:56'),
(706, 'footer_shortcut_2_name', '', '2025-06-23 17:46:56'),
(707, 'footer_shortcut_2_url', '', '2025-06-23 17:46:56'),
(708, 'footer_shortcut_3_name', '', '2025-06-23 17:46:56'),
(709, 'footer_shortcut_3_url', '', '2025-06-23 17:46:56'),
(710, 'site_logo', 'assets/images/site_logo_1750700816.png', '2025-06-23 17:46:56'),
(711, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 17:50:49'),
(712, 'footer_text', '', '2025-06-23 17:50:49'),
(713, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 17:50:49'),
(714, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 17:50:49'),
(715, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 17:50:49'),
(716, 'footer_address_line2', 'Sri Lanka', '2025-06-23 17:50:49'),
(717, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 17:50:49'),
(718, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 17:50:49'),
(719, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 17:50:49'),
(720, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 17:50:49'),
(721, 'footer_shortcut_1_name', '', '2025-06-23 17:50:49'),
(722, 'footer_shortcut_1_url', '', '2025-06-23 17:50:49'),
(723, 'footer_shortcut_2_name', '', '2025-06-23 17:50:49'),
(724, 'footer_shortcut_2_url', '', '2025-06-23 17:50:49'),
(725, 'footer_shortcut_3_name', '', '2025-06-23 17:50:49'),
(726, 'footer_shortcut_3_url', '', '2025-06-23 17:50:49'),
(727, 'site_logo', 'assets/images/site_logo_1750701049.png', '2025-06-23 17:50:49'),
(728, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 17:51:03'),
(729, 'footer_text', '', '2025-06-23 17:51:03'),
(730, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 17:51:03'),
(731, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 17:51:03'),
(732, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 17:51:03'),
(733, 'footer_address_line2', 'Sri Lanka', '2025-06-23 17:51:03'),
(734, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 17:51:03'),
(735, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 17:51:03'),
(736, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 17:51:03'),
(737, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 17:51:03'),
(738, 'footer_shortcut_1_name', '', '2025-06-23 17:51:03'),
(739, 'footer_shortcut_1_url', '', '2025-06-23 17:51:03'),
(740, 'footer_shortcut_2_name', '', '2025-06-23 17:51:03'),
(741, 'footer_shortcut_2_url', '', '2025-06-23 17:51:03'),
(742, 'footer_shortcut_3_name', '', '2025-06-23 17:51:03'),
(743, 'footer_shortcut_3_url', '', '2025-06-23 17:51:03'),
(744, 'site_logo', 'assets/images/site_logo_1750701063.jpg', '2025-06-23 17:51:03'),
(745, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 17:51:05'),
(746, 'footer_text', '', '2025-06-23 17:51:05'),
(747, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 17:51:05'),
(748, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 17:51:05'),
(749, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 17:51:05'),
(750, 'footer_address_line2', 'Sri Lanka', '2025-06-23 17:51:05'),
(751, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 17:51:05'),
(752, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 17:51:05'),
(753, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 17:51:05'),
(754, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 17:51:05'),
(755, 'footer_shortcut_1_name', '', '2025-06-23 17:51:05'),
(756, 'footer_shortcut_1_url', '', '2025-06-23 17:51:05'),
(757, 'footer_shortcut_2_name', '', '2025-06-23 17:51:05'),
(758, 'footer_shortcut_2_url', '', '2025-06-23 17:51:05'),
(759, 'footer_shortcut_3_name', '', '2025-06-23 17:51:05'),
(760, 'footer_shortcut_3_url', '', '2025-06-23 17:51:05'),
(761, 'site_logo', 'assets/images/site_logo_1750701065.jpg', '2025-06-23 17:51:05'),
(762, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 17:51:41'),
(763, 'footer_text', '', '2025-06-23 17:51:41'),
(764, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 17:51:41'),
(765, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 17:51:41'),
(766, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 17:51:41'),
(767, 'footer_address_line2', 'Sri Lanka', '2025-06-23 17:51:41'),
(768, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 17:51:41'),
(769, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 17:51:41'),
(770, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 17:51:41'),
(771, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 17:51:41'),
(772, 'footer_shortcut_1_name', '', '2025-06-23 17:51:41'),
(773, 'footer_shortcut_1_url', '', '2025-06-23 17:51:41'),
(774, 'footer_shortcut_2_name', '', '2025-06-23 17:51:41'),
(775, 'footer_shortcut_2_url', '', '2025-06-23 17:51:41'),
(776, 'footer_shortcut_3_name', '', '2025-06-23 17:51:41'),
(777, 'footer_shortcut_3_url', '', '2025-06-23 17:51:41'),
(778, 'site_logo', 'assets/images/site_logo_1750701101.png', '2025-06-23 17:51:41'),
(779, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-06-23 17:51:45'),
(780, 'footer_text', '', '2025-06-23 17:51:45'),
(781, 'footer_contact_email', 'info@northportlogistics.com', '2025-06-23 17:51:45'),
(782, 'footer_contact_phone', '+94 11 2517445', '2025-06-23 17:51:45'),
(783, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-06-23 17:51:45'),
(784, 'footer_address_line2', 'Sri Lanka', '2025-06-23 17:51:45'),
(785, 'footer_social_facebook', 'https://facebook.com/northport', '2025-06-23 17:51:45'),
(786, 'footer_social_twitter', 'https://twitter.com/northport', '2025-06-23 17:51:45'),
(787, 'footer_social_linkedin', 'https://linkedin.com/company/northport', '2025-06-23 17:51:45'),
(788, 'footer_social_instagram', 'https://instagram.com/northport', '2025-06-23 17:51:45'),
(789, 'footer_shortcut_1_name', '', '2025-06-23 17:51:45'),
(790, 'footer_shortcut_1_url', '', '2025-06-23 17:51:45'),
(791, 'footer_shortcut_2_name', '', '2025-06-23 17:51:45'),
(792, 'footer_shortcut_2_url', '', '2025-06-23 17:51:45'),
(793, 'footer_shortcut_3_name', '', '2025-06-23 17:51:45'),
(794, 'footer_shortcut_3_url', '', '2025-06-23 17:51:45'),
(795, 'site_logo', 'assets/images/site_logo_1750701105.png', '2025-06-23 17:51:45');

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
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `is_active`, `created_at`, `updated_at`, `phone`, `street_address`, `city`, `state`, `postal_code`, `country`, `company_name`, `profile_pic`, `date_of_birth`, `preferences`, `notes`) VALUES
(1, 'Yohan Koshala', 'yohankoshala@gmail.com', '$2y$10$ZEvAE7pZ4HgM5qyQ4iGPjuXqPQxGAtwjJrRztLihtRcam2c2criKO', 'user', 1, '2025-06-23 11:26:47', '2025-06-23 17:53:51', '0766446354', 'No 73, Mount Lavinia', 'colombo', 'western province', '10390', 'Sri Lanka', 'Tecro Technologies (PVT) Ltd', '', '2000-01-21', NULL, ''),
(2, 'admin', 'admin@northport.com', '$2y$10$x2mFCP.UpSdTOctE3r9SRuD5tfWPw6A3UaP7frAspSiQ5egaQVtsm', 'admin', 1, '2025-06-23 12:38:27', '2025-06-23 12:38:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=796;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
