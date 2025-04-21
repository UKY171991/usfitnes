-- Create branch_tests table
CREATE TABLE IF NOT EXISTS branch_tests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch_id INT NOT NULL,
    test_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    reporting_time VARCHAR(50),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE,
    UNIQUE KEY unique_branch_test (branch_id, test_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 