USE fitness;

-- Create branches table if not exists
CREATE TABLE IF NOT EXISTS branches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    contact_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default main branch
INSERT INTO branches (name, address, contact_number) 
VALUES ('Main Branch', 'Main Address', '1234567890');

-- Add branch_id column to users table if not exists
ALTER TABLE users ADD COLUMN IF NOT EXISTS branch_id INT;

-- Add foreign key constraint
ALTER TABLE users 
ADD CONSTRAINT fk_users_branch 
FOREIGN KEY (branch_id) REFERENCES branches(id); 