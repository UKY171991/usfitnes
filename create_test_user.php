<?php
// Create a test user for login verification
require_once 'config.php';

try {
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Create admin user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, user_type) VALUES (?, ?, ?, ?, ?)");
        $password = password_hash('password', PASSWORD_DEFAULT);
        $stmt->execute(['admin', $password, 'Administrator', 'admin@pathlab.com', 'admin']);
        echo "Admin user created successfully!\n";
        echo "Username: admin\n";
        echo "Password: password\n";
    } else {
        echo "Admin user already exists!\n";
    }
    
    // Show all users
    $stmt = $pdo->query("SELECT id, username, full_name, user_type FROM users");
    $users = $stmt->fetchAll();
    
    echo "\nExisting users:\n";
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Username: {$user['username']}, Name: {$user['full_name']}, Type: {$user['user_type']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
