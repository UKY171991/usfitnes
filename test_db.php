<?php
try {
    require_once 'config.php';
    echo "✓ Database connection successful\n";
    
    // Check if admin user exists
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute(['admin']);
    $user = $stmt->fetch();
    
    if($user) {
        echo "✓ Admin user exists with ID: " . $user['id'] . "\n";
        echo "  Username: " . $user['username'] . "\n";
        echo "  Full Name: " . $user['full_name'] . "\n";
        echo "  User Type: " . $user['user_type'] . "\n";
    } else {
        echo "✗ Admin user not found\n";
    }
    
    // Check patients table
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM patients');
    $stmt->execute();
    $count = $stmt->fetch();
    echo "✓ Patients table has " . $count['count'] . " records\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
