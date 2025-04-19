<?php
require_once 'inc/config.php';
require_once 'inc/db.php';

$username = 'uky171991@gmail.com';
$password = 'Uma@171991';
$name = 'Uday Kumar';
$role = 'admin';

// Generate password hash
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // First, remove existing user if any
    $stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    // Insert new user
    $stmt = $conn->prepare("
        INSERT INTO users (username, password, name, role, email, status, created_at)
        VALUES (?, ?, ?, ?, ?, 1, NOW())
    ");
    
    $stmt->execute([$username, $hash, $name, $role, $username]);
    
    echo "User created successfully!\n";
    echo "Username: " . $username . "\n";
    echo "Password hash: " . $hash . "\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 