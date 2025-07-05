<?php
// Create demo user script
require_once 'config.php';

try {
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "Demo user 'admin' already exists.\n";
    } else {
        // Create admin user
        $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, full_name, user_type, email, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            'admin',
            $hashedPassword,
            'System Administrator',
            'admin',
            'admin@pathlab.com'
        ]);
        
        echo "Demo user 'admin' created successfully!\n";
        echo "Login credentials:\n";
        echo "Username: admin\n";
        echo "Password: password\n";
    }
    
    // List all users
    $stmt = $pdo->query("SELECT id, username, full_name, user_type, created_at FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent users in database:\n";
    foreach ($users as $user) {
        echo "- ID: {$user['id']}, Username: {$user['username']}, Name: {$user['full_name']}, Type: {$user['user_type']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
