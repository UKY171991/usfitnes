<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Get database connection
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'Staff',
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        profile_image VARCHAR(255),
        reset_token VARCHAR(64),
        reset_token_expiry DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "Users table created or already exists.<br>";
    
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@admin.com']);
    
    if (!$stmt->fetch()) {
        // Insert admin user
        $sql = "INSERT INTO users (name, email, password, role, status) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'Administrator',
            'admin@admin.com',
            password_hash('admin123', PASSWORD_DEFAULT),
            'Admin',
            'active'
        ]);
        
        echo "Admin user created successfully!<br>";
        echo "Email: admin@admin.com<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Admin user already exists.<br>";
    }
    
    echo "<br>You can now log in using these credentials at <a href='login.php'>login page</a>.";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    error_log("Create Admin Error: " . $e->getMessage());
} 