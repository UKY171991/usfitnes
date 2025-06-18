-- Updated Database Schema for Pathology Lab Website
-- This file contains additional tables and modifications to align with project specifications

-- ============================================
-- 1. ADD BOOKING SYSTEM TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `booking_date` datetime NOT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `status` enum('pending','confirmed','sample_collected','processing','completed','cancelled') DEFAULT 'pending',
  `payment_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_patient_id` (`patient_id`),
  INDEX `idx_test_id` (`test_id`),
  INDEX `idx_branch_id` (`branch_id`),
  INDEX `idx_booking_date` (`booking_date`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. UPDATE USERS TABLE FOR PATIENT ROLE
-- ============================================

-- Add patient role to existing enum if not exists
ALTER TABLE `users` 
MODIFY `role` enum('master_admin','admin','branch_admin','patient','technician','receptionist') DEFAULT 'patient';

-- Add additional fields for patients
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `date_of_birth` date DEFAULT NULL AFTER `email`,
ADD COLUMN IF NOT EXISTS `gender` enum('male','female','other') DEFAULT NULL AFTER `date_of_birth`,
ADD COLUMN IF NOT EXISTS `address` text DEFAULT NULL AFTER `gender`,
ADD COLUMN IF NOT EXISTS `city` varchar(100) DEFAULT NULL AFTER `address`,
ADD COLUMN IF NOT EXISTS `state` varchar(100) DEFAULT NULL AFTER `city`,
ADD COLUMN IF NOT EXISTS `pincode` varchar(10) DEFAULT NULL AFTER `state`,
ADD COLUMN IF NOT EXISTS `emergency_contact` varchar(15) DEFAULT NULL AFTER `pincode`,
ADD COLUMN IF NOT EXISTS `email_verified` tinyint(1) DEFAULT 0 AFTER `emergency_contact`,
ADD COLUMN IF NOT EXISTS `phone_verified` tinyint(1) DEFAULT 0 AFTER `email_verified`;

-- ============================================
-- 3. UPDATE REPORTS TABLE STRUCTURE
-- ============================================

-- Add booking relationship and improve structure
ALTER TABLE `reports` 
ADD COLUMN IF NOT EXISTS `booking_id` int(11) DEFAULT NULL AFTER `id`,
ADD COLUMN IF NOT EXISTS `test_results` longtext DEFAULT NULL AFTER `remarks`,
ADD COLUMN IF NOT EXISTS `pdf_path` varchar(255) DEFAULT NULL AFTER `test_results`,
ADD COLUMN IF NOT EXISTS `report_status` enum('pending','in_progress','completed','verified') DEFAULT 'pending' AFTER `pdf_path`,
ADD COLUMN IF NOT EXISTS `verified_by` int(11) DEFAULT NULL AFTER `report_status`,
ADD COLUMN IF NOT EXISTS `verified_at` timestamp NULL DEFAULT NULL AFTER `verified_by`,
ADD INDEX `idx_booking_id` (`booking_id`),
ADD INDEX `idx_patient_id` (`patient_id`),
ADD INDEX `idx_report_status` (`report_status`);

-- ============================================
-- 4. UPDATE PAYMENTS TABLE FOR INSTAMOJO
-- ============================================

-- Enhance payments table for Instamojo integration
ALTER TABLE `payments`
ADD COLUMN IF NOT EXISTS `booking_id` int(11) DEFAULT NULL AFTER `id`,
ADD COLUMN IF NOT EXISTS `payment_request_id` varchar(100) DEFAULT NULL AFTER `booking_id`,
ADD COLUMN IF NOT EXISTS `instamojo_payment_id` varchar(100) DEFAULT NULL AFTER `payment_request_id`,
ADD COLUMN IF NOT EXISTS `payment_status` enum('pending','completed','failed','refunded','cancelled') DEFAULT 'pending' AFTER `instamojo_payment_id`,
ADD COLUMN IF NOT EXISTS `payment_method` varchar(50) DEFAULT NULL AFTER `payment_status`,
ADD COLUMN IF NOT EXISTS `transaction_id` varchar(100) DEFAULT NULL AFTER `payment_method`,
ADD COLUMN IF NOT EXISTS `failure_reason` text DEFAULT NULL AFTER `transaction_id`,
ADD COLUMN IF NOT EXISTS `refund_id` varchar(100) DEFAULT NULL AFTER `failure_reason`,
ADD COLUMN IF NOT EXISTS `webhook_verified` tinyint(1) DEFAULT 0 AFTER `refund_id`,
ADD INDEX `idx_booking_id` (`booking_id`),
ADD INDEX `idx_payment_request_id` (`payment_request_id`),
ADD INDEX `idx_payment_status` (`payment_status`);

-- ============================================
-- 5. ADD SYSTEM SETTINGS TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_category` (`category`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`, `category`) VALUES
('site_name', 'Pathology Lab', 'string', 'Website name', 'general'),
('site_url', 'https://yourdomain.com', 'string', 'Website URL', 'general'),
('contact_email', 'admin@pathlabs.com', 'string', 'Contact email', 'general'),
('contact_phone', '+91-9999999999', 'string', 'Contact phone', 'general'),
('lab_license', 'LAB/2024/001', 'string', 'Lab license number', 'general'),
('instamojo_mode', 'test', 'string', 'Instamojo mode (test/live)', 'payment'),
('instamojo_test_api_key', '', 'string', 'Instamojo test API key', 'payment'),
('instamojo_test_auth_token', '', 'string', 'Instamojo test auth token', 'payment'),
('instamojo_test_salt', '', 'string', 'Instamojo test salt', 'payment'),
('instamojo_live_api_key', '', 'string', 'Instamojo live API key', 'payment'),
('instamojo_live_auth_token', '', 'string', 'Instamojo live auth token', 'payment'),
('instamojo_live_salt', '', 'string', 'Instamojo live salt', 'payment'),
('report_header_text', 'Pathology Lab Report', 'string', 'Report header text', 'reports'),
('report_footer_text', 'Thank you for choosing our services', 'string', 'Report footer text', 'reports'),
('email_notifications', '1', 'boolean', 'Enable email notifications', 'notifications'),
('sms_notifications', '0', 'boolean', 'Enable SMS notifications', 'notifications');

-- ============================================
-- 6. ADD NOTIFICATION SYSTEM
-- ============================================

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `category` varchar(50) DEFAULT 'general',
  `is_read` tinyint(1) DEFAULT 0,
  `action_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_is_read` (`is_read`),
  INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. ADD AUDIT LOG TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_action` (`action`),
  INDEX `idx_table_name` (`table_name`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. ADD FOREIGN KEY CONSTRAINTS
-- ============================================

-- Add foreign keys for better data integrity
SET FOREIGN_KEY_CHECKS = 0;

-- Bookings foreign keys
ALTER TABLE `bookings` 
ADD CONSTRAINT `fk_bookings_patient` FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_bookings_test` FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_bookings_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE;

-- Reports foreign keys
ALTER TABLE `reports`
ADD CONSTRAINT `fk_reports_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_reports_patient` FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_reports_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- Payments foreign keys
ALTER TABLE `payments`
ADD CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE;

-- Test parameters foreign keys (if not exists)
ALTER TABLE `test_parameters`
ADD CONSTRAINT `fk_test_parameters_test` FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE CASCADE;

-- Users branch foreign key
ALTER TABLE `users`
ADD CONSTRAINT `fk_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL;

-- Notifications foreign keys
ALTER TABLE `notifications`
ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE;

-- Audit logs foreign keys
ALTER TABLE `audit_logs`
ADD CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 9. UPDATE EXISTING DATA
-- ============================================

-- Update existing admin users to have master_admin role if they have admin role
UPDATE `users` SET `role` = 'master_admin' WHERE `role` = 'admin' AND `branch_id` IS NULL;

-- ============================================
-- 10. CREATE INDEXES FOR PERFORMANCE
-- ============================================

-- Add indexes for better performance
ALTER TABLE `tests` ADD INDEX IF NOT EXISTS `idx_category_id` (`category_id`);
ALTER TABLE `tests` ADD INDEX IF NOT EXISTS `idx_status` (`status`);
ALTER TABLE `branches` ADD INDEX IF NOT EXISTS `idx_status` (`status`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_role` (`role`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_status` (`status`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_email` (`email`);

-- ============================================
-- COMPLETION MESSAGE
-- ============================================

-- Log completion
INSERT INTO `activities` (`user_id`, `description`) VALUES 
(1, 'Database schema updated for pathology lab specifications');

SELECT 'Database schema updated successfully! New tables: bookings, system_settings, notifications, audit_logs. Enhanced existing tables with additional fields and foreign keys.' as message;
