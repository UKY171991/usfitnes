USE fitness;

-- Modify existing branches table structure
ALTER TABLE branches
    MODIFY COLUMN `id` int(11) NOT NULL AUTO_INCREMENT,
    MODIFY COLUMN `branch_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    MODIFY COLUMN `branch_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    MODIFY COLUMN `address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    MODIFY COLUMN `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    MODIFY COLUMN `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    MODIFY COLUMN `pincode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    MODIFY COLUMN `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    MODIFY COLUMN `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    MODIFY COLUMN `status` tinyint(4) DEFAULT 1,
    MODIFY COLUMN `created_at` timestamp DEFAULT current_timestamp();

-- Add unique key if it doesn't exist
ALTER TABLE branches
    ADD UNIQUE KEY IF NOT EXISTS `branch_code` (`branch_code`);

-- Insert a default main branch
INSERT INTO branches (branch_name, address, phone) 
VALUES ('Main Branch', 'Main Address', '1234567890'); 