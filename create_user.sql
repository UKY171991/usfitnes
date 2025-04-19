USE fitness;

-- Insert user
-- Username: uky171991@gmail.com
-- Password: Uma@171991
INSERT INTO users (username, password, name, role, created_at)
VALUES (
    'uky171991@gmail.com',
    '$2y$10$Ue9ZGXHfYNGQvgHVGvkqxOQQrNkUm.VxMrYKfYPxC5IXfzW8WmHHi', -- hashed password for "Uma@171991"
    'Uday Kumar',
    'admin',
    NOW()
); 