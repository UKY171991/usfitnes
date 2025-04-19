USE fitness;

-- Drop existing table if it exists
DROP TABLE IF EXISTS tests;

-- Create tests table with proper structure
CREATE TABLE `tests` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `test_name` varchar(100) NOT NULL,
    `category_id` int(11) NOT NULL,
    `price` decimal(10,2) NOT NULL,
    `description` text DEFAULT NULL,
    `normal_range` varchar(255) DEFAULT NULL,
    `sample_type` varchar(100) DEFAULT NULL,
    `preparation` text DEFAULT NULL,
    `reporting_time` varchar(100) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `category_id` (`category_id`),
    CONSTRAINT `tests_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `test_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 