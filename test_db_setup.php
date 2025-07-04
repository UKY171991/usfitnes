<?php
require_once 'config.php';

try {
    echo "Testing database connection and tables...\n\n";
    
    // Test connection
    echo "✓ Database connection successful\n";
    
    // Check if main tables exist
    $tables = ['users', 'patients', 'tests', 'doctors', 'test_orders', 'test_order_items'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
            
            // Count records
            $count_stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $count_stmt->fetchColumn();
            echo "  - Records: $count\n";
        } else {
            echo "✗ Table '$table' does not exist\n";
        }
    }
    
    echo "\nChecking for admin user...\n";
    $stmt = $pdo->prepare("SELECT username, user_type FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✓ Admin user exists: " . $admin['username'] . " (" . $admin['user_type'] . ")\n";
    } else {
        echo "✗ Admin user not found\n";
    }
    
    echo "\nDatabase setup complete!\n";
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}
?>
