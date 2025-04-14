<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Get database connection
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Drop existing users table to match the exact structure from screenshot
 
 
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
    
    // Insert or update demo users
    foreach ($users as $user) {
        try {
            // Check if the user already exists
            $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = :email");
            $stmt->execute(['email' => $user['email']]);

            if ($stmt->rowCount() > 0) {
                // Update existing user details
                $stmt = $pdo->prepare(
                    "UPDATE Users SET username = :username, password = :password, first_name = :first_name, last_name = :last_name, role = :role WHERE email = :email"
                );
                $stmt->execute([
                    'username' => $user['username'],
                    'password' => $user['password'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'role' => $user['role'],
                    'email' => $user['email']
                ]);

                echo "User with email {$user['email']} updated successfully.<br>";
            } else {
                // Insert new user
                $stmt = $pdo->prepare(
                    "INSERT INTO Users (username, password, first_name, last_name, email, role, created_at) 
                    VALUES (:username, :password, :first_name, :last_name, :email, :role, NOW())"
                );
                $stmt->execute([
                    'username' => $user['username'],
                    'password' => $user['password'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]);

                echo "User with email {$user['email']} inserted successfully.<br>";
            }
        } catch (PDOException $e) {
            echo "Error processing user {$user['username']}: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<br>Demo users processed successfully! You can now log in with any of these accounts.<br>";
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