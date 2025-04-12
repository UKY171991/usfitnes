<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Get database connection
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Drop existing users table to match the exact structure from screenshot
    $pdo->exec("DROP TABLE IF EXISTS users");
    
    // Create users table with exact structure from screenshot
    $sql = "CREATE TABLE users (
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
        PRIMARY KEY (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "Users table created successfully with the exact structure!<br>";
    
    // Create demo users
    $users = [
        [
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@admin.com',
            'role' => 'Admin'
        ],
        [
            'username' => 'doctor',
            'password' => password_hash('doctor123', PASSWORD_DEFAULT),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'doctor@example.com',
            'role' => 'Doctor'
        ],
        [
            'username' => 'tech',
            'password' => password_hash('tech123', PASSWORD_DEFAULT),
            'first_name' => 'Tech',
            'last_name' => 'Support',
            'email' => 'tech@example.com',
            'role' => 'Technician'
        ],
        [
            'username' => 'receptionist',
            'password' => password_hash('reception123', PASSWORD_DEFAULT),
            'first_name' => 'Front',
            'last_name' => 'Desk',
            'email' => 'reception@example.com',
            'role' => 'Receptionist'
        ]
    ];
    
    // Insert demo users
    $stmt = $pdo->prepare("INSERT INTO users (username, password, first_name, last_name, email, role) 
                          VALUES (:username, :password, :first_name, :last_name, :email, :role)");
    
    foreach ($users as $user) {
        $stmt->execute($user);
        echo "Created user: {$user['username']} ({$user['role']})<br>";
        echo "Email: {$user['email']}<br>";
        echo "Password: " . str_replace('123', '123', substr($user['username'], 0, 5) . '123') . "<br><br>";
    }
    
    echo "<br>Demo users created successfully! You can now log in with any of these accounts.<br>";
    echo "For example:<br>";
    echo "Admin - admin@admin.com / admin123<br>";
    echo "Doctor - doctor@example.com / doctor123<br>";
    echo "Technician - tech@example.com / tech123<br>";
    echo "Receptionist - reception@example.com / reception123<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    error_log("Create Demo Users Error: " . $e->getMessage());
} 