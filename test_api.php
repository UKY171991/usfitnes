<?php
// Simple test script to check API functionality
session_start();

// Include database configuration 
require_once 'config.php';

// First let's verify the admin user exists
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Create admin user if not exists
        $adminPassword = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, user_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $adminPassword, 'admin@pathlabpro.com', 'System Administrator', 'admin']);
        echo "Admin user created successfully.\n";
    } else {
        echo "Admin user exists with ID: " . $user['id'] . "\n";
    }
} catch (Exception $e) {
    echo "Error checking/creating admin user: " . $e->getMessage() . "\n";
}

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    echo "No active session found. Please login first.\n";
    echo "Current session data: " . print_r($_SESSION, true);
}

echo "Session active for user ID: " . $_SESSION['user_id'] . "\n";
echo "User name: " . ($_SESSION['full_name'] ?? 'N/A') . "\n";

// Test database connection
try {
    require_once 'config.php';
    echo "Database connection successful.\n";
    
    // Test patients query
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM patients");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "Total patients in database: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

// Script usage information
echo "\n\n";
echo "=== PATHLAB PRO DATABASE INFO ===\n";
echo "Checking database tables...\n";

try {
    // Check users table structure
    $stmt = $pdo->prepare("DESCRIBE users");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Users table structure:\n";
    foreach ($columns as $column) {
        echo "  - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // Check for any users with unusual passwords
    $stmt = $pdo->prepare("SELECT id, username, password FROM users LIMIT 3");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nUsers in database:\n";
    foreach ($users as $user) {
        echo "  - ID " . $user['id'] . ": " . $user['username'] . " (pwd length: " . strlen($user['password']) . ")\n";
    }
    
} catch (Exception $e) {
    echo "Error examining database: " . $e->getMessage() . "\n";
}

echo "=== END DATABASE INFO ===\n";
?>
