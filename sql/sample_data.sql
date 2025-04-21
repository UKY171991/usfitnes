-- Insert sample branch
INSERT INTO `branches` (`name`, `address`, `phone`, `email`) 
VALUES ('Main Branch', '123 Main St, City', '1234567890', 'main@example.com');

-- Insert sample users
INSERT INTO `users` (`name`, `username`, `password`, `role`, `branch_id`) 
VALUES 
('Admin User', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL),
('Branch Admin', 'branch_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'branch_admin', 1);

-- Insert sample test categories
INSERT INTO `test_categories` (`category_name`, `description`) 
VALUES 
('Blood Tests', 'Various blood test categories'),
('Urine Tests', 'Various urine test categories');

-- Insert sample tests
INSERT INTO `tests` (`test_name`, `category_id`, `price`, `description`, `normal_range`, `sample_type`, `preparation`, `reporting_time`) 
VALUES 
('Complete Blood Count', 1, 500.00, 'CBC Test', '4.5-11.0 x10^9/L', 'Blood', 'Fasting for 8 hours', '24 hours'),
('Urine Analysis', 2, 300.00, 'Basic urine test', 'pH 4.6-8.0', 'Urine', 'First morning sample', '12 hours');

-- Insert sample patients
INSERT INTO `patients` (`name`, `age`, `gender`, `phone`, `email`, `address`, `branch_id`) 
VALUES 
('John Doe', 35, 'male', '9876543210', 'john@example.com', '456 Park Ave, City', 1),
('Jane Smith', 28, 'female', '8765432109', 'jane@example.com', '789 Oak St, City', 1);

-- Insert sample reports
INSERT INTO `reports` (`patient_id`, `test_id`, `status`) 
VALUES 
(1, 1, 'pending'),
(2, 2, 'completed');

-- Insert sample payments
INSERT INTO `payments` (`patient_id`, `report_id`, `amount`, `payment_method`, `status`, `branch_id`) 
VALUES 
(1, 1, 500.00, 'cash', 'completed', 1),
(2, 2, 300.00, 'card', 'completed', 1);

-- Insert sample activities
INSERT INTO `activities` (`user_id`, `description`) 
VALUES 
(2, 'Logged into the system'),
(2, 'Created new patient record'); 