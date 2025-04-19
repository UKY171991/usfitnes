USE fitness;

-- Modify existing branches table
ALTER TABLE branches
    MODIFY id INT PRIMARY KEY AUTO_INCREMENT,
    MODIFY name VARCHAR(255) NOT NULL,
    MODIFY address TEXT,
    MODIFY contact_number VARCHAR(20),
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Insert a default main branch
INSERT INTO branches (name, address, contact_number) 
VALUES ('Main Branch', 'Main Address', '1234567890'); 