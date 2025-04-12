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
    // First try to connect without database to create it
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` 
                DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created successfully!\n";
    
    // Connect to the specific database
    $pdo->exec("USE `" . DB_NAME . "`");
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        user_id int(11) NOT NULL AUTO_INCREMENT,
        username varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        password varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        first_name varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        last_name varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        email varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
        role enum('Admin','Doctor','Technician','Receptionist') COLLATE utf8mb4_unicode_ci NOT NULL,
        reset_token varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        reset_token_expiry datetime DEFAULT NULL,
        created_at datetime DEFAULT current_timestamp(),
        PRIMARY KEY (user_id),
        UNIQUE KEY username (username),
        UNIQUE KEY email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "Users table created successfully!\n";
    
    // Create test user
    $stmt = $pdo->prepare("INSERT INTO users (username, password, first_name, last_name, email, role) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        'uky171991',
        password_hash('123456', PASSWORD_DEFAULT),
        'Yogesh',
        'Kumar',
        'uky171991@gmail.com',
        'Admin'
    ]);
    
    echo "\nTest user created successfully!\n";
    echo "Email: uky171991@gmail.com\n";
    echo "Password: 123456\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 