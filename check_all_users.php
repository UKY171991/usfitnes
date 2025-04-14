<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    $pdo = new PDO(DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Define users to check or insert
    $users = [
        [
            'username' => 'uky171991',
            'password' => password_hash('Uma@171991', PASSWORD_BCRYPT),
            'first_name' => 'Yogesh',
            'last_name' => 'Kumar',
            'email' => 'uky171991@gmail.com',
            'role' => 'Admin'
        ],
        [
            'username' => 'doctor',
            'password' => password_hash('doctor123', PASSWORD_BCRYPT),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'doctor@example.com',
            'role' => 'Doctor'
        ],
        [
            'username' => 'tech',
            'password' => password_hash('tech123', PASSWORD_BCRYPT),
            'first_name' => 'Tech',
            'last_name' => 'Support',
            'email' => 'tech@example.com',
            'role' => 'Technician'
        ],
        [
            'username' => 'receptionist',
            'password' => password_hash('reception123', PASSWORD_BCRYPT),
            'first_name' => 'Front',
            'last_name' => 'Desk',
            'email' => 'reception@example.com',
            'role' => 'Receptionist'
        ]
    ];

    foreach ($users as $user) {
        // Check if the user already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $user['email']]);

        if ($stmt->rowCount() > 0) {
            // Update existing user
            $stmt = $pdo->prepare(
                "UPDATE users SET username = :username, password = :password, first_name = :first_name, last_name = :last_name, role = :role WHERE email = :email"
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
                "INSERT INTO users (username, password, first_name, last_name, email, role, created_at) 
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
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>