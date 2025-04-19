USE fitness;

-- First, let's remove the existing user if exists
DELETE FROM users WHERE username = 'uky171991@gmail.com';

-- Now insert the user with proper password hash
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
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- This is the hash for password: password
    'Uday Kumar',
    'admin',
    'uky171991@gmail.com',
    1,
    NOW()
); 