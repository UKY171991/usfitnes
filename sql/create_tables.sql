-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `username` varchar(50) NOT NULL UNIQUE,
    `password` varchar(255) NOT NULL,
    `role` enum('admin', 'branch_admin', 'staff') NOT NULL,
    `branch_id` int(11) DEFAULT NULL,
    `status` enum('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create branches table
CREATE TABLE IF NOT EXISTS `branches` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `address` text,
    `phone` varchar(20),
    `email` varchar(100),
    `status` enum('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create patients table
CREATE TABLE IF NOT EXISTS `patients` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `age` int(11),
    `gender` enum('male', 'female', 'other') NOT NULL,
    `phone` varchar(20),
    `email` varchar(100),
    `address` text,
    `branch_id` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create test_categories table
CREATE TABLE IF NOT EXISTS `test_categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `category_name` varchar(100) NOT NULL,
    `description` text,
    `status` enum('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tests table
CREATE TABLE IF NOT EXISTS `tests` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `test_name` varchar(100) NOT NULL,
    `category_id` int(11) NOT NULL,
    `price` decimal(10,2) NOT NULL,
    `description` text,
    `normal_range` text,
    `sample_type` varchar(100),
    `preparation` text,
    `reporting_time` varchar(50),
    `status` enum('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`category_id`) REFERENCES `test_categories`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create reports table
CREATE TABLE IF NOT EXISTS `reports` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `patient_id` int(11) NOT NULL,
    `test_id` int(11) NOT NULL,
    `result` text,
    `status` enum('pending', 'completed') NOT NULL DEFAULT 'pending',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`),
    FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create payments table
CREATE TABLE IF NOT EXISTS `payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `patient_id` int(11) NOT NULL,
    `report_id` int(11) NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `payment_method` enum('cash', 'card', 'upi') NOT NULL,
    `status` enum('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    `branch_id` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`),
    FOREIGN KEY (`report_id`) REFERENCES `reports`(`id`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create activities table
CREATE TABLE IF NOT EXISTS `activities` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `description` text NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 