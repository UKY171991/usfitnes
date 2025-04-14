-- Create branches table
CREATE TABLE branches (
    branch_id INT AUTO_INCREMENT PRIMARY KEY,
    branch_name VARCHAR(255) NOT NULL,
    branch_location VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert demo data into branches table
INSERT INTO branches (branch_name, branch_location) VALUES
('Main Branch', 'Downtown'),
('North Branch', 'Northside'),
('East Branch', 'Eastside'),
('West Branch', 'Westside'),
('South Branch', 'Southside');

-- Add branch_id to Users table
ALTER TABLE Users ADD COLUMN branch_id INT;

-- Add branch_id to Patients table
ALTER TABLE Patients ADD COLUMN branch_id INT;

-- Add branch_id to Test_Requests table
ALTER TABLE Test_Requests ADD COLUMN branch_id INT;

-- Add foreign key constraints
ALTER TABLE Users ADD CONSTRAINT fk_users_branch FOREIGN KEY (branch_id) REFERENCES branches(branch_id);
ALTER TABLE Patients ADD CONSTRAINT fk_patients_branch FOREIGN KEY (branch_id) REFERENCES branches(branch_id);
ALTER TABLE Test_Requests ADD CONSTRAINT fk_test_requests_branch FOREIGN KEY (branch_id) REFERENCES branches(branch_id);