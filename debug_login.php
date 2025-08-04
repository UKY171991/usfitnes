<?php
require_once 'config.php';

echo "<h2>Database & Login Debug</h2>";

try {
    // Check database connection
    echo "<p>✅ Database connected successfully</p>";
    echo "<p>Database name: " . $dbname . "</p>";
    
    // Check if users table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();
    if ($tableCheck) {
        echo "<p>✅ Users table exists</p>";
        
        // Count users
        $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "<p>Total users in database: " . $userCount . "</p>";
        
        // Check admin user
        $stmt = $pdo->prepare("SELECT id, username, password, name, user_type FROM users WHERE username = 'admin'");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            echo "<p>✅ Admin user exists:</p>";
            echo "<ul>";
            echo "<li>ID: " . $admin['id'] . "</li>";
            echo "<li>Username: " . $admin['username'] . "</li>";
            echo "<li>Name: " . $admin['name'] . "</li>";
            echo "<li>Type: " . $admin['user_type'] . "</li>";
            echo "</ul>";
            
            // Test password
            if (password_verify('password', $admin['password'])) {
                echo "<p>✅ Password 'password' is correct</p>";
            } else {
                echo "<p>❌ Password 'password' is incorrect</p>";
            }
        } else {
            echo "<p>❌ Admin user not found</p>";
        }
        
    } else {
        echo "<p>❌ Users table does not exist</p>";
    }
    
    // Test direct login simulation
    echo "<h3>Testing Login Process:</h3>";
    $testUsername = 'admin';
    $testPassword = 'password';
    
    $stmt = $pdo->prepare("SELECT id, username, password, name, user_type FROM users WHERE username = ?");
    $stmt->execute([$testUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($testPassword, $user['password'])) {
        echo "<p>✅ Login simulation successful</p>";
        echo "<p>User data would be:</p>";
        echo "<ul>";
        echo "<li>user_id: " . $user['id'] . "</li>";
        echo "<li>username: " . $user['username'] . "</li>";
        echo "<li>full_name: " . $user['name'] . "</li>";
        echo "<li>user_type: " . $user['user_type'] . "</li>";
        echo "</ul>";
    } else {
        echo "<p>❌ Login simulation failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Error details: " . $e->getTraceAsString() . "</p>";
}
?>
