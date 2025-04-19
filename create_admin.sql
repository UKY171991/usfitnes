USE fitness;

-- Delete existing user if any
DELETE FROM users WHERE username = 'uky171991@gmail.com';

-- Insert admin user with explicit role
INSERT INTO users (
    username,
    password,
    name,
    role,
    email,
    status,
    created_at
) VALUES (
    'uky171991@gmail.com',
    '$2y$10$YourNewPasswordHashHere',  -- Will be replaced with actual hash
    'Uday Kumar',
    'admin',
    'uky171991@gmail.com',
    1,
    NOW()
); 