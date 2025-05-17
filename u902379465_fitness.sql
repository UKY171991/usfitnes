-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 17, 2025 at 06:27 AM
-- Server version: 10.11.10-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u902379465_fitness`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `user_id`, `description`, `created_at`) VALUES
(1, 4, 'User logged out: uky171991@gmail.com', '2025-04-19 09:32:57'),
(2, 4, 'User logged in: uky171991@gmail.com', '2025-04-19 09:32:59'),
(3, 4, 'User logged out: uky171991@gmail.com', '2025-04-19 09:38:26'),
(4, 4, 'User logged in: uky171991@gmail.com', '2025-04-19 09:38:28'),
(5, 4, 'Added new branch: Umakant Yadav', '2025-04-19 09:45:52'),
(6, 4, 'Added new branch: weq qw', '2025-04-19 09:46:05'),
(7, 4, 'Added new branch: UMAKANT YADAV', '2025-04-19 09:48:19'),
(8, 4, 'Updated branch: Umakant Yadav', '2025-04-19 09:50:25'),
(9, 4, 'User updated: ID 4', '2025-04-19 09:55:47'),
(10, 4, 'User deleted: ID 1', '2025-04-19 09:56:10'),
(11, 4, 'New user added: gydodapyxa', '2025-04-19 09:56:18'),
(12, 4, 'New test category added: Blood Test', '2025-04-19 10:03:09'),
(13, 4, 'User logged in: uky171991@gmail.com', '2025-04-20 18:09:47'),
(14, 4, 'User logged out: uky171991@gmail.com', '2025-04-20 18:13:54'),
(15, 4, 'User logged in: uky171991@gmail.com', '2025-04-20 18:13:55'),
(16, 4, 'New user added: uky171992@gmail.com', '2025-04-20 18:15:41'),
(17, 4, 'User logged out: uky171991@gmail.com', '2025-04-20 18:15:49'),
(18, 8, 'User logged in: uky171992@gmail.com', '2025-04-20 18:15:54'),
(21, 8, 'New patient added: Cole Rutledge', '2025-04-20 18:30:06'),
(22, 8, 'New patient added: Sage Garrett', '2025-04-20 18:34:54'),
(23, 8, 'User logged out: uky171992@gmail.com', '2025-04-20 18:49:17'),
(24, 4, 'User logged in: uky171991@gmail.com', '2025-04-20 18:49:18'),
(25, 4, 'User logged out: uky171991@gmail.com', '2025-04-20 18:49:25'),
(26, 8, 'User logged in: uky171992@gmail.com', '2025-04-20 18:49:30'),
(27, 8, 'User logged out: uky171992@gmail.com', '2025-04-20 18:51:20'),
(28, 4, 'User logged in: uky171991@gmail.com', '2025-04-20 18:51:22'),
(29, 4, 'Deleted branch: Main Branch', '2025-04-20 18:55:48'),
(30, 4, 'Deleted branch: ', '2025-04-20 18:56:03'),
(31, 4, 'Deleted branch: ', '2025-04-20 18:57:30'),
(32, 4, 'Deleted branch: ', '2025-04-20 18:57:36'),
(33, 4, 'Deleted branch: ', '2025-04-20 18:57:36'),
(34, 4, 'Deleted branch: ', '2025-04-20 18:57:36'),
(35, 4, 'Added new branch: Camden Berger', '2025-04-20 18:58:03'),
(36, 4, 'Added new branch: Melyssa Finch', '2025-04-20 18:58:25'),
(37, 4, 'Deleted branch: weq qw', '2025-04-20 18:58:57'),
(38, 4, 'New test added: Complete Blood Count', '2025-04-20 19:04:48'),
(39, 4, 'Test deleted: ID 10', '2025-04-20 19:05:42'),
(40, 4, 'User logged out: uky171991@gmail.com', '2025-04-20 19:05:49'),
(41, 4, 'User logged in: uky171991@gmail.com', '2025-04-20 19:05:54'),
(42, 4, 'User logged out: uky171991@gmail.com', '2025-04-20 19:05:57'),
(43, 8, 'User logged in: uky171992@gmail.com', '2025-04-20 19:06:02'),
(44, 8, 'User logged out: uky171992@gmail.com', '2025-04-20 19:20:05'),
(45, 4, 'User logged in: uky171991@gmail.com', '2025-04-20 19:20:07'),
(46, 4, 'User logged out: uky171991@gmail.com', '2025-04-20 19:24:27'),
(47, 8, 'User logged in: uky171992@gmail.com', '2025-04-20 19:24:32'),
(48, 8, 'Created new report #5 with payment', '2025-04-20 19:40:30'),
(49, 8, 'Updated report #5 - Status: pending', '2025-04-20 19:44:21'),
(50, 8, 'Updated report #5 - Status: completed', '2025-04-20 19:44:34'),
(51, 8, 'Updated payment #INV-000005 with additional amount of ₹500.00', '2025-04-20 19:50:07'),
(52, 8, 'Updated payment #INV-000005 with additional amount of ₹500.00', '2025-04-20 19:50:53'),
(53, 8, 'Updated payment #INV-000005 with additional amount of ₹1.00', '2025-04-20 19:56:17'),
(54, 8, 'Updated payment #INV-000005 with additional amount of ₹50.00', '2025-04-20 19:56:51'),
(55, 8, 'Updated payment #INV-000005 with additional amount of ₹1.00', '2025-04-20 20:05:39'),
(56, 8, 'Updated payment #INV-000005 with additional amount of ₹1.00', '2025-04-20 20:39:05'),
(57, 8, 'Created new report #6 with payment', '2025-04-20 20:39:59'),
(58, 8, 'Created new report #7 with payment', '2025-04-20 20:49:10'),
(59, 8, 'Created new report #8 with payment', '2025-04-20 20:50:14'),
(60, 8, 'Updated payment #INV-000005 with additional amount of ₹95.00', '2025-04-20 20:52:24'),
(61, 8, 'Created new report #9 with payment', '2025-04-20 21:02:34'),
(62, 4, 'User logged in: uky171991@gmail.com', '2025-04-21 05:45:29'),
(63, 4, 'User logged out: uky171991@gmail.com', '2025-04-21 05:45:32'),
(64, 4, 'User logged in: uky171991@gmail.com', '2025-04-21 05:45:36'),
(65, 4, 'User logged in: uky171991@gmail.com', '2025-04-21 05:54:11'),
(66, 4, 'User logged in: uky171991@gmail.com', '2025-04-23 11:19:31'),
(67, 4, 'User logged in: uky171991@gmail.com', '2025-04-24 04:24:57'),
(68, 4, 'User logged in: uky171991@gmail.com', '2025-04-26 04:43:47'),
(69, 4, 'User logged in: uky171991@gmail.com', '2025-05-06 04:09:08'),
(70, 4, 'User logged in: uky171991@gmail.com', '2025-05-06 11:39:56'),
(71, 4, 'User logged out: uky171991@gmail.com', '2025-05-06 11:50:42'),
(72, 8, 'User logged in: uky171992@gmail.com', '2025-05-06 11:50:49'),
(73, 4, 'User logged in: uky171991@gmail.com', '2025-05-12 10:16:22'),
(74, 4, 'User updated: ID 9', '2025-05-12 10:17:23'),
(75, 4, 'User logged out: uky171991@gmail.com', '2025-05-12 12:11:41'),
(76, 8, 'User logged in: uky171992@gmail.com', '2025-05-12 12:11:48'),
(77, 4, 'User logged in: uky171991@gmail.com', '2025-05-15 04:52:51'),
(78, 4, 'User logged in: uky171991@gmail.com', '2025-05-16 10:25:25'),
(79, 4, 'User logged in: uky171991@gmail.com', '2025-05-17 05:23:46');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `branch_code` varchar(20) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_code`, `branch_name`, `address`, `city`, `state`, `pincode`, `phone`, `email`, `status`, `created_at`) VALUES
(1, 'BR005', 'Umakant Yadav', 'Jaunpur Rd', NULL, NULL, NULL, '345678', NULL, 1, '2025-04-19 09:45:52'),
(3, 'BR004', 'UMAKANT YADAV', 'Village Newada\r\nPost Marhat', NULL, NULL, NULL, '02425342424', NULL, 1, '2025-04-19 09:48:19'),
(4, 'BR003', 'Main Branch', 'Main Address', NULL, NULL, NULL, '1234567890', NULL, 1, '2025-04-19 09:51:32'),
(6, 'BR001', 'Camden Berger', 'Exercitation vel lab', 'In unde in sequi quos est iure est eaque', 'Quas ut consequatur Ut et suscipit assumenda error', 'Duis volup', '+1 (565) 788-21', 'cityjufab@mailinator.com', 1, '2025-04-20 18:58:03'),
(7, 'BR002', 'Melyssa Finch', 'Quidem anim quia mag', 'Anim molestias cillum in aut', 'Laboriosam laboris ratione mollit proident', 'Laboriosam', '+1 (651) 206-36', 'vinobeneli@mailinator.com', 1, '2025-04-20 18:58:25');

-- --------------------------------------------------------

--
-- Table structure for table `branch_tests`
--

CREATE TABLE `branch_tests` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `reporting_time` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_tests`
--

INSERT INTO `branch_tests` (`id`, `branch_id`, `test_id`, `price`, `reporting_time`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 8, 1100.00, '', 1, '2025-04-20 19:10:43', '2025-04-20 19:10:43'),
(2, 4, 7, 1200.00, '', 1, '2025-04-20 19:10:53', '2025-04-20 19:10:53');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `patient_code` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `referred_by` varchar(100) DEFAULT NULL,
  `doctor_name` varchar(100) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `patient_code`, `name`, `gender`, `age`, `dob`, `phone`, `email`, `address`, `referred_by`, `doctor_name`, `branch_id`, `created_at`) VALUES
(1, NULL, 'John Doe', 'Male', 35, NULL, '9876543210', 'john@example.com', '456 Park Ave, City', NULL, NULL, 1, '2025-04-20 18:27:16'),
(2, NULL, 'Jane Smith', 'Female', 28, NULL, '8765432109', 'jane@example.com', '789 Oak St, City', NULL, NULL, 1, '2025-04-20 18:27:16'),
(3, NULL, 'Cole Rutledge', 'Other', 97, NULL, '+1 (172) 568-57', 'rujed@mailinator.com', 'Voluptates sit susc', NULL, NULL, 4, '2025-04-20 18:30:06'),
(4, NULL, 'Sage Garrett', 'Male', 50, NULL, '+1 (948) 561-89', 'fiqipigo@mailinator.com', 'Blanditiis quod atqu', NULL, NULL, 4, '2025-04-20 18:34:54');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `due_amount` decimal(10,2) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `invoice_no`, `patient_id`, `total_amount`, `discount`, `paid_amount`, `due_amount`, `payment_mode`, `transaction_id`, `payment_date`, `branch_id`, `created_by`, `created_at`, `updated_at`) VALUES
(3, 'INV-000005', 3, 1200.00, NULL, 1200.00, 0.00, 'cash', '', '2025-04-21', 4, 8, '2025-04-20 19:40:30', '2025-04-20 20:52:24'),
(4, 'INV-000006', 3, 1100.00, NULL, 1000.00, 100.00, 'cash', '', '2025-04-21', 4, 8, '2025-04-20 20:39:59', '2025-04-20 20:39:59'),
(5, 'INV-000007', 4, 1200.00, NULL, 55.00, 1145.00, 'upi', 'Et quasi nostrum qui', '2025-04-21', 4, 8, '2025-04-20 20:49:10', '2025-04-20 20:49:10'),
(6, 'INV-000008', 4, 1100.00, NULL, 30.00, 1070.00, 'netbanking', 'Sit eos fugit earum', '2025-04-21', 4, 8, '2025-04-20 20:50:14', '2025-04-20 20:50:14'),
(7, 'INV-000009', 3, 1100.00, NULL, 47.00, 1053.00, 'netbanking', 'Corporis fuga Enim ', '2025-04-21', 4, 8, '2025-04-20 21:02:34', '2025-04-20 21:02:34');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `test_id` int(11) DEFAULT NULL,
  `result` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `notes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `patient_id`, `test_id`, `result`, `created_at`, `updated_at`, `status`, `notes`) VALUES
(5, 3, 7, 'Normal', '2025-04-20 19:40:30', '2025-04-20 19:44:34', 'completed', ''),
(6, 3, 8, 'Normal', '2025-04-20 20:39:59', '2025-04-20 20:48:45', 'completed', 'wert'),
(7, 4, 7, 'Normal', '2025-04-20 20:49:10', '2025-04-20 20:49:20', 'completed', ''),
(8, 4, 8, 'Normal', '2025-04-20 20:50:14', '2025-04-20 20:50:39', 'completed', ''),
(9, 3, 8, NULL, '2025-04-20 21:02:34', '2025-04-20 21:02:34', 'pending', '');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `setting_key` varchar(100) DEFAULT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tests`
--

CREATE TABLE `tests` (
  `id` int(11) NOT NULL,
  `test_name` varchar(150) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `method` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `normal_range` varchar(100) DEFAULT NULL,
  `sample_type` varchar(100) DEFAULT NULL,
  `preparation` text DEFAULT NULL,
  `reporting_time` varchar(50) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tests`
--

INSERT INTO `tests` (`id`, `test_name`, `category_id`, `method`, `description`, `price`, `unit`, `normal_range`, `sample_type`, `preparation`, `reporting_time`, `status`, `created_at`) VALUES
(1, 'Complete Blood Count (CBC)', 1, 'Flow Cytometry', NULL, 500.00, 'Various', 'Varies by parameter', 'Whole Blood', NULL, NULL, 1, '2025-04-20 19:02:30'),
(2, 'Hemoglobin', 1, 'Spectrophotometry', NULL, 150.00, 'g/dL', '13.5-17.5 g/dL (male), 12.0-15.5 g/dL (female)', 'Whole Blood', NULL, NULL, 1, '2025-04-20 19:02:30'),
(3, 'Platelet Count', 1, 'Flow Cytometry', NULL, 200.00, 'cells/µL', '150,000-450,000/µL', 'Whole Blood', NULL, NULL, 1, '2025-04-20 19:02:30'),
(4, 'Lipid Profile', 2, 'Spectrophotometry', NULL, 800.00, 'mg/dL', 'Varies by parameter', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(5, 'Liver Function Test', 2, 'Spectrophotometry', NULL, 1000.00, 'Various', 'Varies by parameter', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(6, 'Kidney Function Test', 2, 'Spectrophotometry', NULL, 900.00, 'Various', 'Varies by parameter', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(7, 'Testosterone', 3, 'ELISA', NULL, 1200.00, 'ng/dL', '280-1,100 ng/dL (male)', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(8, 'Estradiol', 3, 'ELISA', NULL, 1100.00, 'pg/mL', '10-50 pg/mL (male), 30-400 pg/mL (female)', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(9, 'Troponin', 4, 'ELISA', NULL, 1500.00, 'ng/mL', '<0.04 ng/mL', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(11, 'HbA1c', 5, 'HPLC', NULL, 800.00, '%', '4.0-5.6%', 'Whole Blood', NULL, NULL, 1, '2025-04-20 19:02:30'),
(12, 'Fasting Blood Sugar', 5, 'GOD-POD', NULL, 200.00, 'mg/dL', '70-100 mg/dL', 'Plasma', NULL, NULL, 1, '2025-04-20 19:02:30'),
(13, 'TSH', 6, 'ELISA', NULL, 600.00, 'mIU/L', '0.4-4.0 mIU/L', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(14, 'T3', 6, 'ELISA', NULL, 700.00, 'ng/dL', '80-200 ng/dL', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(15, 'T4', 6, 'ELISA', NULL, 700.00, 'µg/dL', '5.0-12.0 µg/dL', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(16, 'SGPT/ALT', 7, 'UV Kinetic', NULL, 300.00, 'U/L', '7-56 U/L', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(17, 'SGOT/AST', 7, 'UV Kinetic', NULL, 300.00, 'U/L', '5-40 U/L', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(18, 'Creatinine', 8, 'Jaffe Method', NULL, 250.00, 'mg/dL', '0.7-1.3 mg/dL (male), 0.6-1.1 mg/dL (female)', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(19, 'Urea', 8, 'UV Kinetic', NULL, 250.00, 'mg/dL', '15-45 mg/dL', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(20, 'Vitamin D', 9, 'CLIA', NULL, 1800.00, 'ng/mL', '20-50 ng/mL', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(21, 'Vitamin B12', 9, 'CLIA', NULL, 1200.00, 'pg/mL', '200-900 pg/mL', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(22, 'Total IgE', 10, 'ELISA', NULL, 1000.00, 'IU/mL', '<100 IU/mL', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(23, 'Specific IgE Panel', 10, 'ImmunoCAP', NULL, 2500.00, 'kU/L', '<0.35 kU/L', 'Serum', NULL, NULL, 1, '2025-04-20 19:02:30'),
(24, 'Complete Blood Count', 5, NULL, 'qwerfg', 100.00, NULL, '3434', '111', 'wertgh', '44', 1, '2025-04-20 19:04:48');

-- --------------------------------------------------------

--
-- Table structure for table `test_categories`
--

CREATE TABLE `test_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `test_categories`
--

INSERT INTO `test_categories` (`id`, `category_name`, `description`, `created_at`) VALUES
(1, 'Blood Test', 'dcv', '2025-04-19 10:03:09'),
(2, 'Blood Tests', 'Various blood test categories', '2025-04-20 18:27:16'),
(3, 'Urine Tests', 'Various urine test categories', '2025-04-20 18:27:16'),
(4, 'Hematology', 'Blood cell counts and related tests', '2025-04-20 19:02:30'),
(5, 'Biochemistry', 'Tests for various chemical substances in blood', '2025-04-20 19:02:30'),
(6, 'Hormone', 'Endocrine system and hormone level tests', '2025-04-20 19:02:30'),
(7, 'Cardiac', 'Heart and cardiovascular system tests', '2025-04-20 19:02:30'),
(8, 'Diabetes', 'Blood sugar and diabetes-related tests', '2025-04-20 19:02:30'),
(9, 'Thyroid', 'Thyroid function tests', '2025-04-20 19:02:30'),
(10, 'Liver', 'Liver function tests', '2025-04-20 19:02:30'),
(11, 'Kidney', 'Kidney function tests', '2025-04-20 19:02:30'),
(12, 'Vitamin', 'Vitamin level tests', '2025-04-20 19:02:30'),
(13, 'Allergy', 'Allergy and immunology tests', '2025-04-20 19:02:30');

-- --------------------------------------------------------

--
-- Table structure for table `test_parameters`
--

CREATE TABLE `test_parameters` (
  `id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `parameter_name` varchar(255) NOT NULL,
  `default_unit` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `test_reports`
--

CREATE TABLE `test_reports` (
  `id` int(11) NOT NULL,
  `report_number` varchar(30) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `test_id` int(11) DEFAULT NULL,
  `result_value` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `test_date` date DEFAULT NULL,
  `uploaded_file` varchar(255) DEFAULT NULL,
  `conducted_by` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','branch_admin','receptionist','technician') DEFAULT 'technician',
  `branch_id` int(11) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `phone`, `email`, `role`, `branch_id`, `last_login`, `status`, `created_at`) VALUES
(4, 'Umakant Yadav', 'uky171991@gmail.com', '$2y$10$5AtCyZKeghbOjXa846MQo.VC0hWnF65Y0EIXY5qHakphz6QqPkaUO', '09453619260', 'uky171991@gmail.com', 'admin', 3, NULL, 1, '2025-04-19 09:26:34'),
(6, 'Garrett Webster', 'gydodapyxa', '$2y$10$ydvj.rB9BsBXizOLwzoi9ORm5Vx7bJDOxDrRbmHPyxJq1P7vBRkRC', '+1 (112) 408-16', 'rycazosype@mailinator.com', 'admin', 4, NULL, 1, '2025-04-19 09:56:18'),
(8, 'Umakant Yadav', 'uky171992@gmail.com', '$2y$10$FOIJmXlGLuWA8p8V/Ox5zu43hNxt7mDY3KDEx/kgLldZo25jAHxZm', '09453619260', 'uky171992@gmail.com', 'branch_admin', 4, NULL, 1, '2025-04-20 18:15:41'),
(9, 'weq qw', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '02425342424', 'admin@example.com', 'admin', 4, NULL, 1, '2025-04-20 18:27:16'),
(10, 'Branch Admin', 'branch_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'branch_admin', 1, NULL, 1, '2025-04-20 18:27:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_code` (`branch_code`);

--
-- Indexes for table `branch_tests`
--
ALTER TABLE `branch_tests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_branch_test` (`branch_id`,`test_id`),
  ADD KEY `test_id` (`test_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patient_code` (`patient_code`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `test_id` (`test_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `tests`
--
ALTER TABLE `tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `test_categories`
--
ALTER TABLE `test_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_parameters`
--
ALTER TABLE `test_parameters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_reports`
--
ALTER TABLE `test_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_number` (`report_number`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `test_id` (`test_id`),
  ADD KEY `conducted_by` (`conducted_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `branch_id` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `branch_tests`
--
ALTER TABLE `branch_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tests`
--
ALTER TABLE `tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `test_categories`
--
ALTER TABLE `test_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `test_parameters`
--
ALTER TABLE `test_parameters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `test_reports`
--
ALTER TABLE `test_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `branch_tests`
--
ALTER TABLE `branch_tests`
  ADD CONSTRAINT `branch_tests_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_tests_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`);

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `tests`
--
ALTER TABLE `tests`
  ADD CONSTRAINT `tests_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `test_categories` (`id`);

--
-- Constraints for table `test_reports`
--
ALTER TABLE `test_reports`
  ADD CONSTRAINT `test_reports_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `test_reports_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`),
  ADD CONSTRAINT `test_reports_ibfk_3` FOREIGN KEY (`conducted_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
