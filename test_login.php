<?php
// This is a direct test script to validate login functionality
require_once 'config.php';

echo "Testing login functionality...\n";

$username = 'admin';
$password = 'password';

try {
    // Check user credentials
    $stmt = $pdo->prepare("SELECT id, username, password, full_name, user_type FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "User found: " . ($user ? "Yes" : "No") . "\n";
    if ($user) {
        echo "User ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Password hash length: " . strlen($user['password']) . "\n";
        
        // Verify password
        $passwordValid = password_verify($password, $user['password']);
        echo "Password valid: " . ($passwordValid ? "Yes" : "No") . "\n";
        
        if ($passwordValid) {
            echo "Login would be successful\n";
            // Simulate session variables
            echo "Session variables that would be set:\n";
            echo "  user_id: " . $user['id'] . "\n";
            echo "  username: " . $user['username'] . "\n";
            echo "  full_name: " . $user['full_name'] . "\n";
            echo "  role: " . $user['user_type'] . "\n";
        } else {
            echo "Password hash from DB: " . substr($user['password'], 0, 10) . "...\n";
            echo "Generating new hash for comparison: " . substr(password_hash($password, PASSWORD_DEFAULT), 0, 10) . "...\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}