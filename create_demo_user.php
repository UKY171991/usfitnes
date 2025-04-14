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
    $sql = "CREATE TABLE Users (
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
    echo "Users table created successfully with the exact structure!<br>";
    
    // Create demo users including the specific user from the screenshot
    $users = [
        [
            'username' => 'uky171991',
            'password' => password_hash('Uma@171991', PASSWORD_DEFAULT),
            'first_name' => 'Yogesh',
            'last_name' => 'Kumar',
            'email' => 'uky171991@gmail.com',
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
    $stmt = $pdo->prepare("INSERT INTO Users (username, password, first_name, last_name, email, role) 
                          VALUES (:username, :password, :first_name, :last_name, :email, :role)");
    
    foreach ($users as $user) {
        try {
            $stmt->execute($user);
            echo "Created user: {$user['username']} ({$user['role']})<br>";
            echo "Email: {$user['email']}<br>";
            if ($user['username'] === 'uky171991') {
                echo "Password: 123456<br><br>";
            } else {
                echo "Password: " . str_replace('123', '123', substr($user['username'], 0, 5) . '123') . "<br><br>";
            }
        } catch (PDOException $e) {
            echo "Error creating user {$user['username']}: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<br>Demo users created successfully! You can now log in with any of these accounts.<br>";
    echo "Main user account:<br>";
    echo "Email: uky171991@gmail.com<br>";
    echo "Password: Uma@171991<br><br>";
    
    echo "Other test accounts:<br>";
    echo "Doctor - doctor@example.com / doctor123<br>";
    echo "Technician - tech@example.com / tech123<br>";
    echo "Receptionist - reception@example.com / reception123<br>";
    
    // Check if the demo user already exists
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = :email");
    $stmt->execute(['email' => 'demo@example.com']);

    if ($stmt->rowCount() === 0) {
        // Insert demo user
        $hashedPassword = password_hash('password123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            "INSERT INTO Users (username, password, first_name, last_name, email, role, created_at) 
            VALUES (:username, :password, :first_name, :last_name, :email, :role, NOW())"
        );
        $stmt->execute([
            'username' => 'demo_user',
            'password' => $hashedPassword,
            'first_name' => 'Demo',
            'last_name' => 'User',
            'email' => 'demo@example.com',
            'role' => 'Receptionist'
        ]);

        echo "Demo user created successfully.";
    } else {
        // Update password for existing demo user
        $hashedPassword = password_hash('password123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE Users SET password = :password WHERE email = :email");
        $stmt->execute([
            'password' => $hashedPassword,
            'email' => 'demo@example.com'
        ]);

        echo "Demo user already exists. Password updated successfully.";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    error_log("Create Demo Users Error: " . $e->getMessage());
}
?>