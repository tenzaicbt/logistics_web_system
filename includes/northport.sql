-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2025 at 04:08 PM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `seen` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('Pending','Solved') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_messages`
--

INSERT INTO `admin_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `seen`, `status`) VALUES
(1, 'yohan', 'yohankoshala@gmail.com', 'gg', 'ggggg', '2025-06-26 03:19:01', 0, 'Solved'),
(2, 'yohan koshala', 'vimash@northport.com', 'gg', 'ggggggggggggggg', '2025-06-26 04:36:29', 0, 'Solved'),
(3, 'yohan', 'yohankoshala@gmail.com', 'gg', 'create p', '2025-06-26 11:58:25', 0, 'Solved'),
(4, 'yohan', 'vimash@northport.com', 'gg', 'maleeeessaaaa', '2025-06-27 03:17:11', 0, 'Solved');

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `status` enum('Present','Absent','Late','Leave','Remote') NOT NULL DEFAULT 'Present',
  `remarks` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_info` varchar(255) DEFAULT NULL,
  `is_manual_entry` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_details`
--

CREATE TABLE `bank_details` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'LKR',
  `swift_code` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_details`
--

INSERT INTO `bank_details` (`id`, `user_id`, `bank_name`, `branch_name`, `account_number`, `account_name`, `currency`, `swift_code`, `created_at`, `updated_at`) VALUES
(1, 4, 'Commercial Bank PLC', 'Ratmalana', '8008683111', 'Christy Philip', 'LKR', NULL, '2025-06-30 08:54:45', '2025-06-30 09:52:32');

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

--
-- Dumping data for table `containers`
--

INSERT INTO `containers` (`id`, `container_no`, `type`, `status`, `location`, `assigned_fleet_id`, `last_inspected_at`, `created_at`, `updated_at`) VALUES
(1, 'CNT-2025-001', '40ft', 'Available', 'Colombo Port', 1, '2025-05-01', '2025-06-30 15:41:44', '2025-07-01 12:18:10'),
(2, 'CNT-2025-002', 'Reefer', 'Available', 'Storage Yard B', NULL, '2025-04-15', '2025-06-30 15:41:44', '2025-06-30 15:41:44'),
(3, 'CNT-2025-003', '20ft', 'Under Maintenance', 'Repair Bay 3', 3, '2025-06-01', '2025-06-30 15:41:44', '2025-06-30 15:41:44'),
(4, 'CNT-2025-004', 'Open Top', 'Damaged', 'Colombo Port', NULL, '2025-03-10', '2025-06-30 15:41:44', '2025-06-30 15:41:44'),
(5, 'CNT-2025-005', 'Tank', 'Available', 'Jaffna Terminal', 5, '2025-06-15', '2025-06-30 15:41:44', '2025-06-30 15:41:44');

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
-- Table structure for table `employee_leaves`
--

CREATE TABLE `employee_leaves` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `admin_reason` text DEFAULT NULL,
  `employer_reject_reason` text DEFAULT NULL,
  `cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `cancelled_at` datetime DEFAULT NULL,
  `requested_on` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rejected_by` int(11) DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_leaves`
--

INSERT INTO `employee_leaves` (`id`, `employee_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `admin_reason`, `employer_reject_reason`, `cancelled`, `cancelled_at`, `requested_on`, `created_at`, `updated_at`, `rejected_by`, `rejected_at`) VALUES
(1, 4, 'Sick', '2025-06-26', '2025-06-26', 'want leave', 'Rejected', 'cant give leave at this time', NULL, 0, NULL, '2025-06-25 20:22:51', '2025-06-25 14:52:51', '2025-06-25 14:54:56', NULL, NULL),
(2, 4, 'Casual', '2025-06-29', '2025-06-30', 'i want leave', 'Pending', NULL, NULL, 0, NULL, '2025-06-27 08:59:15', '2025-06-27 03:29:15', '2025-06-27 03:29:15', NULL, NULL);

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
(1, 'Pacific Voyager', 'Vessel', 'PV-2025-001', 5000, 'Active', '2025-06-24 23:11:08', '2025-06-27 08:55:48', 'Oceanic Shipyards', 'Voyager X200', 2018, 'Singapore Port', ''),
(2, 'Ocean Carrier A', 'Vessel', 'OC-A-1234', 5000, 'Active', '2025-06-30 15:41:34', '2025-06-30 15:41:34', 'MarineWorks', 'X500', 2015, 'Colombo Port', 'Flagship vessel'),
(3, 'Harbor Hauler B', 'Vessel', 'HH-B-5678', 3000, 'Inactive', '2025-06-30 15:41:34', '2025-06-30 15:41:34', 'OceanX', 'SeaGo 300', 2010, 'Galle Dock', 'Retired from regular use'),
(4, 'Highway Express', 'Truck', 'HX-T-7777', 12000, 'Under Maintenance', '2025-06-30 15:41:34', '2025-06-30 15:41:34', 'Volvo', 'FH16', 2019, 'Warehouse 5', 'Needs tire replacement'),
(5, 'Mountain Mover', 'Truck', 'MM-T-8899', 8000, 'Active', '2025-06-30 15:41:34', '2025-06-30 15:41:34', 'Isuzu', 'GigaMax', 2020, 'Kandy Depot', 'Reliable for heavy cargo'),
(6, 'Island Runner', 'Vessel', 'IR-V-3210', 4000, 'Active', '2025-06-30 15:41:34', '2025-06-30 15:41:34', 'ShipLine', 'AquaPro', 2017, 'Trincomalee Bay', 'Short-distance ferry service');

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
-- Table structure for table `paysheets`
--

CREATE TABLE `paysheets` (
  `id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `month` varchar(20) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paysheets`
--

INSERT INTO `paysheets` (`id`, `employer_id`, `uploaded_by`, `file_path`, `month`, `notes`, `created_at`) VALUES
(1, 4, 2, '../uploads/paysheets/1750935188_Pay-slip-template.pdf', 'june 2025', '', '2025-06-26 16:23:08'),
(2, 4, 2, '../uploads/paysheets/1750935588_Pay-slip-template.pdf', 'july 2025', 'july 2025 paysheets', '2025-06-26 16:29:48'),
(3, 4, 2, '../uploads/paysheets/1750939262_Pay-slip-template.pdf', 'june 2025', 'ffffff', '2025-06-26 17:31:02');

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
(1, 'company_name', 'NorthPort Logistics Pvt Ltd', '2025-07-01 08:05:18'),
(2, 'logo_path', 'assets/images/logo.png', '2025-06-26 12:11:14'),
(3, 'footer_text', '', '2025-07-01 08:05:18'),
(4, 'site_logo', 'assets/images/site_logo_1751357118.png', '2025-07-01 08:05:18'),
(5, 'footer_contact_email', 'info@northportlogistics.com', '2025-07-01 08:05:18'),
(6, 'footer_contact_phone', '+94 11 2517446', '2025-07-01 08:05:18'),
(7, 'footer_address_line1', 'No. 46, Kesbewa Road, Boralesgamuwa Colombo – 10290', '2025-07-01 08:05:18'),
(8, 'footer_address_line2', 'Sri Lanka', '2025-07-01 08:05:18');

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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sender_name` varchar(100) DEFAULT NULL,
  `sender_address` varchar(255) DEFAULT NULL,
  `sender_contact` varchar(50) DEFAULT NULL,
  `recipient_name` varchar(100) DEFAULT NULL,
  `recipient_address` varchar(255) DEFAULT NULL,
  `recipient_contact` varchar(50) DEFAULT NULL,
  `package_contents` text DEFAULT NULL,
  `package_weight` varchar(50) DEFAULT NULL,
  `package_value` varchar(50) DEFAULT NULL,
  `delivery_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `shipment_id`, `user_id`, `booking_id`, `container_id`, `origin`, `destination`, `status`, `departure_date`, `arrival_date`, `created_at`, `updated_at`, `sender_name`, `sender_address`, `sender_contact`, `recipient_name`, `recipient_address`, `recipient_contact`, `package_contents`, `package_weight`, `package_value`, `delivery_type`) VALUES
(1, 'SHIP-2025-001', 1, 10, 1, 'Colombo', 'Dubai', 'Delivered', '2025-04-01', '2025-04-10', '2025-06-30 15:43:40', '2025-06-30 15:43:40', 'ABC Exports', '123 Ocean Road, Colombo', '0771234567', 'XYZ Imports', '78 Bay Street, Dubai', '+971502345678', 'Electronics and gadgets', '1500', '25000', 'Air Freight'),
(2, 'SHIP-2025-002', 2, 12, 2, 'Hambantota', 'Chennai', 'In Transit', '2025-06-20', NULL, '2025-06-30 15:43:40', '2025-06-30 15:43:40', 'Sun Lanka Pvt Ltd', 'Port Access Road, Hambantota', '0762345678', 'IndoSea Traders', 'Chennai Port, India', '+91-9876543210', 'Marine spare parts', '800', '18000', 'Sea Freight'),
(3, 'SHIP-2025-003', 3, 15, 3, 'Galle', 'Singapore', 'Pending', '2025-07-10', NULL, '2025-06-30 15:43:40', '2025-06-30 15:43:40', 'Galle Textiles', '456 Lighthouse Ave, Galle', '0753456789', 'Singapore Retail Hub', '2 Temasek Blvd, Singapore', '+65 61234567', 'Fabric rolls and accessories', '1200', '15000', 'Air Freight'),
(4, 'SHIP-2025-004', 4, 18, NULL, 'Trincomalee', 'Jakarta', 'Cancelled', '2025-05-15', NULL, '2025-06-30 15:43:40', '2025-06-30 15:43:40', 'Trinco Steel Co.', 'Industrial Zone, Trincomalee', '0787654321', 'Jakarta Construction Ltd', 'Jakarta Industrial Estate', '+62 812345678', 'Steel bars and rods', '3500', '40000', 'Sea Freight'),
(5, 'SHIP-2025-005', 5, 20, 5, 'Jaffna', 'Kuala Lumpur', 'Delivered', '2025-03-01', '2025-03-12', '2025-06-30 15:43:40', '2025-06-30 15:43:40', 'North Lanka Herbs', '456 Palmyrah Rd, Jaffna', '0749988776', 'KL Herbal Market', 'Jalan Ampang, Kuala Lumpur', '+60 1123456789', 'Medicinal herbs and oils', '900', '11000', 'Land + Sea Freight');

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
(4, 'Christy Philip', 'philip@northport.com', '$2y$10$M.2aRUl1T/vX3FZi2OjYkOu7cs.mATiMxIvy1JMNmgR2tx67Oj5Vm', 'employer', 1, '2025-06-24 17:20:33', '2025-06-30 09:27:41', '0766446366', 'No 73, Mount Lavinia', 'colombo', 'western Province', '10360', 'sri lanka', 'Aramex (PVT) Ltd', NULL, '2000-01-24', NULL, '', '', '', NULL, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_date` (`user_id`,`date`);

--
-- Indexes for table `bank_details`
--
ALTER TABLE `bank_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `employee_leaves`
--
ALTER TABLE `employee_leaves`
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
-- Indexes for table `paysheets`
--
ALTER TABLE `paysheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_employer` (`employer_id`),
  ADD KEY `fk_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `roles_permissions`
--
ALTER TABLE `roles_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_setting_key` (`setting_key`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_details`
--
ALTER TABLE `bank_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `containers`
--
ALTER TABLE `containers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- AUTO_INCREMENT for table `employee_leaves`
--
ALTER TABLE `employee_leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fleets`
--
ALTER TABLE `fleets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- AUTO_INCREMENT for table `paysheets`
--
ALTER TABLE `paysheets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles_permissions`
--
ALTER TABLE `roles_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bank_details`
--
ALTER TABLE `bank_details`
  ADD CONSTRAINT `bank_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `paysheets`
--
ALTER TABLE `paysheets`
  ADD CONSTRAINT `fk_employer` FOREIGN KEY (`employer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
