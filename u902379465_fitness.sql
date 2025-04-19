-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 19, 2025 at 06:30 AM
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
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `branch_id` int(11) NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  `branch_location` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`branch_id`, `branch_name`, `branch_location`, `created_at`) VALUES
(1, 'Main Branch', 'Downtown', '2025-04-14 10:04:58'),
(2, 'North Branch', 'Northside', '2025-04-14 10:04:58'),
(3, 'East Branch', 'Eastside', '2025-04-14 10:04:58'),
(4, 'West Branch', 'Westside', '2025-04-14 10:04:58'),
(5, 'South Branch', 'Southside', '2025-04-14 10:04:58'),
(7, 'ghg', 'ertgh', '2025-04-14 10:28:30');

-- --------------------------------------------------------

--
-- Table structure for table `Patients`
--

CREATE TABLE `Patients` (
  `patient_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Patients`
--

INSERT INTO `Patients` (`patient_id`, `user_id`, `first_name`, `last_name`, `date_of_birth`, `gender`, `phone`, `email`, `address`, `created_at`, `branch_id`) VALUES
(1, 1, 'John', 'Doe', '1980-05-15', 'Male', '123-456-7890', 'john.doe@gmail.com', '123 Elm St', '2025-03-24 09:12:09', NULL),
(2, 2, 'Jane', 'Smith', '1990-08-22', 'Female', '234-567-8901', 'jane.smith@yahoo.com', '456 Oak St', '2025-03-24 09:12:09', NULL),
(3, 3, 'Mike', 'Johnson', '1975-12-10', 'Other', '345-678-9012', 'mike.j@gmail.com', '789 Pine St', '2025-03-24 09:12:09', NULL),
(4, 4, 'Sara', 'Lee', '1988-03-17', 'Male', '456-789-0123', 'sara.lee@outlook.com', '101 Maple St', '2025-03-24 09:12:09', NULL),
(5, 5, 'Tom', 'Brown', '1965-07-25', 'Female', '567-890-1234', 'tom.brown@gmail.com', '202 Birch St', '2025-03-24 09:12:09', NULL),
(6, 6, 'Lisa', 'Davis', '1995-11-30', 'Other', '678-901-2345', 'lisa.davis@yahoo.com', '303 Cedar St', '2025-03-24 09:12:09', NULL),
(7, 7, 'Paul', 'Wilson', '1982-09-14', 'Male', '789-012-3456', 'paul.w@outlook.com', '404 Spruce St', '2025-03-24 09:12:09', NULL),
(8, 8, 'Emma', 'Clark', '1978-01-05', 'Female', '890-123-4567', 'emma.c@gmail.com', '505 Willow St', '2025-03-24 09:12:09', NULL),
(9, 9, 'Mark', 'Taylor', '1992-06-19', 'Other', '901-234-5678', 'mark.t@yahoo.com', '606 Ash St', '2025-03-24 09:12:09', NULL),
(10, 10, 'Anna', 'Moore', '1986-04-12', 'Male', '012-345-6789', 'anna.m@outlook.com', '707 Poplar St', '2025-03-24 09:12:09', NULL),
(11, 11, 'Greg', 'Hill', '1970-10-28', 'Female', '123-456-7891', 'greg.h@gmail.com', '808 Chestnut St', '2025-03-24 09:12:09', NULL),
(12, 12, 'Kate', 'Adams', '1998-02-03', 'Other', '234-567-8902', 'kate.a@yahoo.com', '909 Walnut St', '2025-03-24 09:12:09', NULL),
(13, 13, 'Luke', 'Evans', '1984-07-07', 'Male', '345-678-9013', 'luke.e@outlook.com', '1010 Sycamore St', '2025-03-24 09:12:09', NULL),
(14, 14, 'Zoe', 'King', '1976-12-20', 'Female', '456-789-0124', 'zoe.k@gmail.com', '1111 Magnolia St', '2025-03-24 09:12:09', NULL),
(15, 15, 'Owen', 'Reed', '1991-09-09', 'Other', '567-890-1235', 'owen.r@yahoo.com', '1212 Laurel St', '2025-03-24 09:12:09', NULL),
(16, 16, 'Mia', 'Perez', '1989-05-01', 'Male', '678-901-2346', 'mia.p@outlook.com', '1313 Hazel St', '2025-03-24 09:12:09', NULL),
(17, 17, 'Jack', 'Ford', '1972-03-15', 'Female', '789-012-3457', 'jack.f@gmail.com', '1414 Linden St', '2025-03-24 09:12:09', NULL),
(18, 18, 'Lily', 'Owen', '1994-08-27', 'Other', '890-123-4568', 'lily.o@yahoo.com', '1515 Cedar St', '2025-03-24 09:12:09', NULL),
(19, 19, 'Ryan', 'Nash', '1987-11-11', 'Male', '901-234-5679', 'ryan.n@outlook.com', '1616 Pine St', '2025-03-24 09:12:09', NULL),
(20, 20, 'Ella', 'Quay', '1979-06-23', 'Female', '012-345-6790', 'ella.q@gmail.com', '1717 Oak St', '2025-03-24 09:12:09', NULL),
(21, 1, 'Taylor', 'Hale', '2023-03-19', 'Male', '+1 (431) 418-15', 'vywan@mailinator.com', 'Elit aut consectetu', '2025-03-24 11:40:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_date` datetime DEFAULT current_timestamp(),
  `generated_by` int(11) NOT NULL,
  `report_content` text NOT NULL,
  `status` enum('Draft','Printed','Delivered') DEFAULT 'Draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Staff`
--

CREATE TABLE `Staff` (
  `staff_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Staff`
--

INSERT INTO `Staff` (`staff_id`, `first_name`, `last_name`, `role`, `phone`, `email`) VALUES
(1, 'Alice', 'Adams', 'Admin', '111-111-1111', 'alice.staff@lab.com'),
(2, 'Bob', 'Brown', 'Pathologist', '222-222-2222', 'bob.staff@lab.com'),
(3, 'Clara', 'Clark', 'Technician', '333-333-3333', 'clara.staff@lab.com'),
(4, 'David', 'Davis', 'Receptionist', '444-444-4444', 'david.staff@lab.com'),
(5, 'Emma', 'Evans', 'Pathologist', '555-555-5555', 'emma.staff@lab.com'),
(6, 'Frank', 'Ford', 'Technician', '666-666-6666', 'frank.staff@lab.com'),
(7, 'Grace', 'Green', 'Receptionist', '777-777-7777', 'grace.staff@lab.com'),
(8, 'Henry', 'Hill', 'Admin', '888-888-8888', 'henry.staff@lab.com'),
(9, 'Irene', 'Ivy', 'Pathologist', '999-999-9999', 'irene.staff@lab.com'),
(10, 'James', 'Jones', 'Technician', '101-010-1010', 'james.staff@lab.com'),
(11, 'Kelly', 'King', 'Receptionist', '111-121-2121', 'kelly.staff@lab.com'),
(12, 'Liam', 'Lee', 'Pathologist', '222-232-3232', 'liam.staff@lab.com'),
(13, 'Mia', 'Moore', 'Technician', '333-343-4343', 'mia.staff@lab.com'),
(14, 'Noah', 'Nash', 'Receptionist', '444-454-5454', 'noah.staff@lab.com'),
(15, 'Olivia', 'Owen', 'Admin', '555-565-6565', 'olivia.staff@lab.com'),
(16, 'Paul', 'Perez', 'Pathologist', '666-676-7676', 'paul.staff@lab.com'),
(17, 'Quinn', 'Quay', 'Technician', '777-787-8787', 'quinn.staff@lab.com'),
(18, 'Rose', 'Reed', 'Receptionist', '888-898-9898', 'rose.staff@lab.com'),
(19, 'Sam', 'Smith', 'Pathologist', '999-909-0909', 'sam.staff@lab.com'),
(20, 'Tina', 'Taylor', 'Technician', '101-112-1212', 'tina.staff@lab.com');

-- --------------------------------------------------------

--
-- Table structure for table `Tests_Catalog`
--

CREATE TABLE `Tests_Catalog` (
  `test_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `test_name` varchar(100) NOT NULL,
  `test_code` varchar(50) NOT NULL,
  `parameters` text DEFAULT NULL,
  `reference_range` text DEFAULT NULL,
  `normal_range` varchar(100) DEFAULT NULL,
  `unit` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Tests_Catalog`
--

INSERT INTO `Tests_Catalog` (`test_id`, `category_id`, `test_name`, `test_code`, `parameters`, `reference_range`, `normal_range`, `unit`, `price`, `created_at`) VALUES
(1, 1, 'Complete Blood Count', 'CBC001', 'Basophils,Eosinophils,Lymphocytes,Monocytes,Neutrophils,Total WBC Count', 'See sub-tests', NULL, '', 50.00, '2025-03-25 07:06:00'),
(2, 4, 'Widal Test', 'WID001', 'S. Typhi O,S. Typhi H,S. Paratyphi AH,S. Paratyphi BH', 'Negative', NULL, '', 30.00, '2025-03-25 07:06:00'),
(4, 1, 'Fredericka Phillips', 'CBC002', 'Basophils,Eosinophils,Hematocrit', '', NULL, '', 30.00, '2025-03-25 08:16:56'),
(5, 1, 'Complete Blood Count', 'dddd', 'Hematocrit', 'werg', NULL, '', 50.00, '2025-03-25 08:21:03');

-- --------------------------------------------------------

--
-- Table structure for table `tests_catalog_old`
--

CREATE TABLE `tests_catalog_old` (
  `test_id` int(11) NOT NULL,
  `test_name` varchar(100) NOT NULL,
  `test_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `normal_range` varchar(50) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tests_catalog_old`
--

INSERT INTO `tests_catalog_old` (`test_id`, `test_name`, `test_code`, `description`, `normal_range`, `unit`, `price`) VALUES
(1, 'Complete Blood Count', 'CBC', 'Blood cell analysis', '4.5-11.0', 'x10^9/L', 50.00),
(2, 'Lipid Panel', 'LIPID', 'Cholesterol levels', '120-200', 'mg/dL', 75.00),
(3, 'Blood Glucose', 'GLU', 'Sugar levels', '70-100', 'mg/dL', 30.00),
(4, 'Hemoglobin A1c', 'A1C', 'Diabetes monitoring', '4-6', '%', 60.00),
(5, 'Liver Function Test', 'LFT', 'Liver health', 'Varies', 'U/L', 80.00),
(6, 'Thyroid Stimulating Hormone', 'TSH', 'Thyroid function', '0.4-4.0', 'mIU/L', 55.00),
(7, 'C-Reactive Protein', 'CRP', 'Inflammation marker', '<1.0', 'mg/L', 45.00),
(8, 'Electrolytes', 'ELECT', 'Ion balance', 'Varies', 'mmol/L', 40.00),
(9, 'Kidney Function Test', 'KFT', 'Kidney health', 'Varies', 'mg/dL', 70.00),
(10, 'Vitamin D', 'VITD', 'Bone health', '20-50', 'ng/mL', 65.00),
(11, 'Prothrombin Time', 'PT', 'Blood clotting', '11-13.5', 'seconds', 35.00),
(12, 'Iron Levels', 'IRON', 'Iron deficiency', '60-170', 'µg/dL', 50.00),
(13, 'Uric Acid', 'UA', 'Gout risk', '3.4-7.0', 'mg/dL', 40.00),
(14, 'Blood Urea Nitrogen', 'BUN', 'Kidney function', '7-20', 'mg/dL', 30.00),
(15, 'Creatinine', 'CREAT', 'Kidney function', '0.6-1.2', 'mg/dL', 25.00),
(16, 'PSA', 'PSA', 'Prostate health', '<4.0', 'ng/mL', 60.00),
(17, 'ESR', 'ESR', 'Inflammation rate', '<20', 'mm/hr', 20.00),
(18, 'Ferritin', 'FERR', 'Iron storage', '20-250', 'ng/mL', 55.00),
(19, 'Calcium', 'CA', 'Bone health', '8.5-10.2', 'mg/dL', 30.00),
(20, 'Magnesium', 'MG', 'Muscle function', '1.7-2.2', 'mg/dL', 35.00);

-- --------------------------------------------------------

--
-- Table structure for table `Test_Categories`
--

CREATE TABLE `Test_Categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Test_Categories`
--

INSERT INTO `Test_Categories` (`category_id`, `category_name`, `created_at`) VALUES
(1, 'Blood Test', '2025-03-25 07:05:47'),
(2, 'Urine Test', '2025-03-25 07:05:47'),
(3, 'Imaging Test', '2025-03-25 07:05:47'),
(4, 'Serology Test', '2025-03-25 07:05:47'),
(8, 'xc', '2025-04-18 11:23:21');

-- --------------------------------------------------------

--
-- Table structure for table `Test_Parameters`
--

CREATE TABLE `Test_Parameters` (
  `parameter_id` int(11) NOT NULL,
  `parameter_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Test_Parameters`
--

INSERT INTO `Test_Parameters` (`parameter_id`, `parameter_name`, `created_at`) VALUES
(1, 'Total WBC Count', '2025-03-25 08:03:21'),
(2, 'Neutrophils', '2025-03-25 08:03:21'),
(3, 'Lymphocytes', '2025-03-25 08:03:21'),
(4, 'Monocytes', '2025-03-25 08:03:21'),
(5, 'Eosinophils', '2025-03-25 08:03:21'),
(6, 'Basophils', '2025-03-25 08:03:21'),
(7, 'Hemoglobin', '2025-03-25 08:03:21'),
(8, 'RBC Count', '2025-03-25 08:03:21'),
(9, 'Platelet Count', '2025-03-25 08:03:21'),
(10, 'Hematocrit', '2025-03-25 08:03:21');

-- --------------------------------------------------------

--
-- Table structure for table `Test_Requests`
--

CREATE TABLE `Test_Requests` (
  `request_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_date` datetime DEFAULT current_timestamp(),
  `ordered_by` varchar(100) NOT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `priority` enum('Normal','Urgent') DEFAULT 'Normal',
  `branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Test_Requests`
--

INSERT INTO `Test_Requests` (`request_id`, `patient_id`, `test_id`, `user_id`, `request_date`, `ordered_by`, `status`, `priority`, `branch_id`) VALUES
(1, 1, 1, 1, '2025-03-24 09:12:09', 'Dr. Brown', 'Completed', 'Normal', NULL),
(2, 2, 2, 2, '2025-03-24 09:12:09', 'Dr. Evans', 'In Progress', 'Urgent', NULL),
(3, 3, 3, 3, '2025-03-24 09:12:09', 'Dr. Ivy', 'Pending', 'Normal', NULL),
(4, 4, 4, 4, '2025-03-24 09:12:09', 'Dr. Lee', 'Completed', 'Normal', NULL),
(5, 5, 5, 5, '2025-03-24 09:12:09', 'Dr. Perez', 'In Progress', 'Urgent', NULL),
(6, 6, 6, 6, '2025-03-24 09:12:09', 'Dr. Smith', 'Pending', 'Normal', NULL),
(7, 7, 7, 7, '2025-03-24 09:12:09', 'Dr. Brown', 'Completed', 'Normal', NULL),
(8, 8, 8, 8, '2025-03-24 09:12:09', 'Dr. Evans', 'In Progress', 'Urgent', NULL),
(9, 9, 9, 9, '2025-03-24 09:12:09', 'Dr. Ivy', 'Pending', 'Normal', NULL),
(10, 10, 10, 10, '2025-03-24 09:12:09', 'Dr. Lee', 'Completed', 'Normal', NULL),
(11, 11, 11, 11, '2025-03-24 09:12:09', 'Dr. Perez', 'In Progress', 'Urgent', NULL),
(12, 12, 12, 12, '2025-03-24 09:12:09', 'Dr. Smith', 'Pending', 'Normal', NULL),
(13, 13, 13, 13, '2025-03-24 09:12:09', 'Dr. Brown', 'Completed', 'Normal', NULL),
(14, 14, 14, 14, '2025-03-24 09:12:09', 'Dr. Evans', 'In Progress', 'Urgent', NULL),
(15, 15, 15, 15, '2025-03-24 09:12:09', 'Dr. Ivy', 'Pending', 'Normal', NULL),
(16, 16, 16, 16, '2025-03-24 09:12:09', 'Dr. Lee', 'Completed', 'Normal', NULL),
(17, 17, 17, 17, '2025-03-24 09:12:09', 'Dr. Perez', 'In Progress', 'Urgent', NULL),
(18, 18, 18, 18, '2025-03-24 09:12:09', 'Dr. Smith', 'Pending', 'Normal', NULL),
(19, 19, 19, 19, '2025-03-24 09:12:09', 'Dr. Brown', 'Completed', 'Normal', NULL),
(20, 20, 20, 20, '2025-03-24 09:12:09', 'Dr. Evans', 'In Progress', 'Urgent', NULL),
(21, 10, 3, 1, '2025-03-24 11:59:55', 'Ram', 'Completed', 'Urgent', NULL),
(22, 21, 2, 1, '2025-04-12 05:51:44', 'Jackson Pickett', 'Pending', 'Normal', NULL),
(23, 7, 5, 1, '2025-04-12 05:51:57', 'Justina Nelson', 'Completed', 'Urgent', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Test_Results`
--

CREATE TABLE `Test_Results` (
  `result_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `result_value` varchar(50) NOT NULL,
  `comments` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `recorded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Test_Results`
--

INSERT INTO `Test_Results` (`result_id`, `request_id`, `result_value`, `comments`, `recorded_by`, `recorded_at`) VALUES
(1, 1, '7.5', 'Normal', 3, '2025-03-24 09:12:09'),
(2, 2, '180', 'Elevated', 6, '2025-03-24 09:12:09'),
(3, 3, '85', 'Within range', 10, '2025-03-24 09:12:09'),
(4, 4, '5.5', 'Normal', 12, '2025-03-24 09:12:09'),
(5, 5, '45', 'Slightly high', 13, '2025-03-24 09:12:09'),
(6, 6, '2.5', 'Normal', 17, '2025-03-24 09:12:09'),
(7, 7, '0.8', 'Low', 3, '2025-03-24 09:12:09'),
(8, 8, '140', 'Normal', 6, '2025-03-24 09:12:09'),
(9, 9, '15', 'Within range', 10, '2025-03-24 09:12:09'),
(10, 10, '35', 'Normal', 12, '2025-03-24 09:12:09'),
(11, 11, '12.5', 'High', 13, '2025-03-24 09:12:09'),
(12, 12, '100', 'Normal', 17, '2025-03-24 09:12:09'),
(13, 13, '6.0', 'Normal', 3, '2025-03-24 09:12:09'),
(14, 14, '5.5', 'Low', 6, '2025-03-24 09:12:09'),
(15, 15, '0.9', 'Within range', 10, '2025-03-24 09:12:09'),
(16, 16, '3.8', 'Normal', 12, '2025-03-24 09:12:09'),
(17, 17, '45', 'Elevated', 13, '2025-03-24 09:12:09'),
(18, 18, '22', 'Normal', 17, '2025-03-24 09:12:09'),
(19, 19, '9.5', 'High', 3, '2025-03-24 09:12:09'),
(20, 20, '2.0', 'Normal', 6, '2025-03-24 09:12:09'),
(21, 21, '100', 'Normal', 18, '2025-03-24 12:18:26'),
(22, 13, '100', '', 8, '2025-03-25 03:56:57'),
(23, 13, '100', 'd', 17, '2025-03-25 03:57:13'),
(24, 4, 'Labore debitis susci', 'Non duis quo sit ei', 12, '2025-03-25 05:59:16');

-- --------------------------------------------------------

--
-- Table structure for table `test_result_details`
--

CREATE TABLE `test_result_details` (
  `detail_id` int(11) NOT NULL,
  `result_id` int(11) NOT NULL,
  `sub_test_name` varchar(100) NOT NULL,
  `result_value` varchar(50) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `normal_range` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('Admin','Doctor','Technician','Receptionist') NOT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL,
  `profile_image` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`user_id`, `username`, `password`, `first_name`, `last_name`, `email`, `role`, `reset_token`, `reset_token_expiry`, `status`, `profile_image`, `created_at`, `branch_id`) VALUES
(1, 'uky171991', '$2y$10$cmMjwzSu2uEKyhEM5ZdTseh.SiJK9bsNhMmAPVTj2KLzhkyX88VRy', 'Yogesh', 'Kumar', 'uky171991@gmail.com', 'Admin', NULL, NULL, 'active', 'assets/img/How to Prevent Sports Injuries_Dr.Jaya Krishna Reddy-min.jpg', '2025-03-24 09:12:09', 1),
(2, 'Alok', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alok', 'Yadav', 'admin@admin.com', 'Admin', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(3, 'tech1', 'hashed_pass3', 'Clara', 'Clark', 'clara@lab.com', 'Technician', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(4, 'rec1', '$2y$10$PaEHcwZfRNgQ9U61MgFLi.B2clt9lXVQOP0359xRK40SvG4g7DeoO', 'David', 'Davis', 'david@lab.com', 'Receptionist', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(5, 'doc2', 'hashed_pass5', 'Emma', 'Evans', 'emma@lab.com', 'Doctor', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(6, 'tech2', 'hashed_pass6', 'Frank', 'Ford', 'frank@lab.com', 'Technician', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(7, 'rec2', 'hashed_pass7', 'Grace', 'Green', 'grace@lab.com', 'Receptionist', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(8, 'admin2', 'hashed_pass8', 'Henry', 'Hill', 'henry@lab.com', 'Admin', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(9, 'doc3', 'hashed_pass9', 'Irene', 'Ivy', 'irene@lab.com', 'Doctor', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(10, 'tech3', 'hashed_pass10', 'James', 'Jones', 'james@lab.com', 'Technician', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(11, 'rec3', 'hashed_pass11', 'Kelly', 'King', 'kelly@lab.com', 'Receptionist', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(12, 'doc4', 'hashed_pass12', 'Liam', 'Lee', 'liam@lab.com', 'Doctor', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(13, 'tech4', 'hashed_pass13', 'Mia', 'Moore', 'mia@lab.com', 'Technician', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(14, 'rec4', 'hashed_pass14', 'Noah', 'Nash', 'noah@lab.com', 'Receptionist', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(15, 'admin3', 'hashed_pass15', 'Olivia', 'Owen', 'olivia@lab.com', 'Admin', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(16, 'doc5', 'hashed_pass16', 'Paul', 'Perez', 'paul@lab.com', 'Doctor', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(17, 'tech5', 'hashed_pass17', 'Quinn', 'Quay', 'quinn@lab.com', 'Technician', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(18, 'rec5', 'hashed_pass18', 'Rose', 'Reed', 'rose@lab.com', 'Receptionist', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(19, 'doc6', 'hashed_pass19', 'Sam', 'Smith', 'sam@lab.com', 'Doctor', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(20, 'tech6', 'hashed_pass20', 'Tina', 'Taylor', 'tina@lab.com', 'Technician', NULL, NULL, 'active', NULL, '2025-03-24 09:12:09', NULL),
(23, 'doctor', '$2y$10$fit2v8qibZ9dtsyVQHMN.e9QPWw4pt9CAZtormb3PY.VYaII3ZwcO', 'John', 'Doe', 'doctor@example.com', 'Doctor', NULL, NULL, 'active', NULL, '2025-04-14 08:00:00', NULL),
(24, 'tech', '$2y$10$rprxuZ/soqUVHLJMzIyAbu/FfnhF.mYi01GnYpYxMtzkxjCDSY9gG', 'Tech', 'Support', 'tech@example.com', 'Technician', NULL, NULL, 'active', NULL, '2025-04-14 08:00:00', NULL),
(25, 'receptionist', '$2y$10$vHIa5hAxYMCHX8FNZK3kleeukrlZyd7Nx44zy.PtDeFfNFXy6UadO', 'Front', 'Desk', 'reception@example.com', 'Receptionist', NULL, NULL, 'active', NULL, '2025-04-14 08:00:00', NULL),
(26, 'demo_user', '$2y$10$8rycEx5cbsnzL6vjU8LuKuarL15cbXzIehw4kB/QzFxeV6uRFeURm', 'Demo', 'User', 'demo@example.com', 'Receptionist', NULL, NULL, 'active', NULL, '2025-04-14 08:00:00', NULL),
(30, 'demo', '$2y$10$sTMJiZKRgNP6CRvsS9gmg.fKIyeGcTYQZ9H7qd4.2oVi1skIOwKue', 'Demo', 'User', 'demo@usfitnes.com', 'Admin', NULL, NULL, 'active', 'assets/img/avatar.png', '2025-04-18 11:06:50', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`branch_id`);

--
-- Indexes for table `Patients`
--
ALTER TABLE `Patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_patients_branch` (`branch_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `Staff`
--
ALTER TABLE `Staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `Tests_Catalog`
--
ALTER TABLE `Tests_Catalog`
  ADD PRIMARY KEY (`test_id`),
  ADD UNIQUE KEY `test_code` (`test_code`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `tests_catalog_old`
--
ALTER TABLE `tests_catalog_old`
  ADD PRIMARY KEY (`test_id`);

--
-- Indexes for table `Test_Categories`
--
ALTER TABLE `Test_Categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `Test_Parameters`
--
ALTER TABLE `Test_Parameters`
  ADD PRIMARY KEY (`parameter_id`),
  ADD UNIQUE KEY `parameter_name` (`parameter_name`);

--
-- Indexes for table `Test_Requests`
--
ALTER TABLE `Test_Requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `test_id` (`test_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_test_requests_branch` (`branch_id`);

--
-- Indexes for table `Test_Results`
--
ALTER TABLE `Test_Results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `test_result_details`
--
ALTER TABLE `test_result_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `result_id` (`result_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_branch` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Patients`
--
ALTER TABLE `Patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Staff`
--
ALTER TABLE `Staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `Tests_Catalog`
--
ALTER TABLE `Tests_Catalog`
  MODIFY `test_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tests_catalog_old`
--
ALTER TABLE `tests_catalog_old`
  MODIFY `test_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `Test_Categories`
--
ALTER TABLE `Test_Categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `Test_Parameters`
--
ALTER TABLE `Test_Parameters`
  MODIFY `parameter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `Test_Requests`
--
ALTER TABLE `Test_Requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `Test_Results`
--
ALTER TABLE `Test_Results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `test_result_details`
--
ALTER TABLE `test_result_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Patients`
--
ALTER TABLE `Patients`
  ADD CONSTRAINT `Patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_patients_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `Reports_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `Patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Reports_ibfk_2` FOREIGN KEY (`request_id`) REFERENCES `Test_Requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Reports_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`),
  ADD CONSTRAINT `Reports_ibfk_4` FOREIGN KEY (`generated_by`) REFERENCES `Staff` (`staff_id`);

--
-- Constraints for table `Tests_Catalog`
--
ALTER TABLE `Tests_Catalog`
  ADD CONSTRAINT `Tests_Catalog_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `Test_Categories` (`category_id`);

--
-- Constraints for table `Test_Requests`
--
ALTER TABLE `Test_Requests`
  ADD CONSTRAINT `Test_Requests_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `Patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Test_Requests_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests_catalog_old` (`test_id`),
  ADD CONSTRAINT `Test_Requests_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`),
  ADD CONSTRAINT `fk_test_requests_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`);

--
-- Constraints for table `Test_Results`
--
ALTER TABLE `Test_Results`
  ADD CONSTRAINT `Test_Results_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `Test_Requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Test_Results_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `Staff` (`staff_id`);

--
-- Constraints for table `test_result_details`
--
ALTER TABLE `test_result_details`
  ADD CONSTRAINT `Test_Result_Details_ibfk_1` FOREIGN KEY (`result_id`) REFERENCES `Test_Results` (`result_id`) ON DELETE CASCADE;

--
-- Constraints for table `Users`
--
ALTER TABLE `Users`
  ADD CONSTRAINT `fk_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
