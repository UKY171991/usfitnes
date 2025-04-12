<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.local.php';

echo "Database settings:\n";
echo "Host: " . DB_HOST . "\n";
echo "Database: " . DB_NAME . "\n";
echo "User: " . DB_USER . "\n";

try {
    // First try to create the database if it doesn't exist
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "Database created successfully or already exists.\n";
    
    // Connect to the specific database
    $pdo->exec("USE " . DB_NAME);
    
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
    echo "Users table created successfully!\n";
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@admin.com']);
    
    if (!$stmt->fetch()) {
        // Create admin user
        $sql = "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'Administrator',
            'admin@admin.com',
            password_hash('admin123', PASSWORD_DEFAULT),
            'Admin',
            'active'
        ]);
        echo "\nAdmin user created successfully!\n";
        echo "Email: admin@admin.com\n";
        echo "Password: admin123\n";
    } else {
        echo "\nAdmin user already exists.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 