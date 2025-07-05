<?php
// Create demo user script for PathLab Pro
header('Content-Type: text/html; charset=utf-8');

echo "<h2>PathLab Pro Demo User Setup</h2>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>";

try {
    require_once 'config.php';
    echo "<div class='success'>✓ Database connection successful</div><br>";
    
    // Create users table if it doesn't exist
    $createUsersTable = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            user_type ENUM('admin', 'technician', 'doctor', 'user') DEFAULT 'user',
            is_verified TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($createUsersTable);
    echo "<div class='success'>✓ Users table created/verified</div><br>";
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Create admin user
        $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, full_name, user_type, email, is_verified) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'admin',
            $hashedPassword,
            'System Administrator',
            'admin',
            'admin@pathlab.com',
            1
        ]);
        
        echo "<div class='success'>✓ Demo admin user created successfully!</div><br>";
    } else {
        echo "<div class='info'>ℹ Demo admin user already exists</div><br>";
    }
    
    // List all users
    $stmt = $pdo->query("SELECT id, username, full_name, user_type, created_at FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Current Users in Database:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>User Type</th><th>Created</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td>{$user['user_type']}</td>";
        echo "<td>{$user['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    echo "<h3>Login Information:</h3>";
    echo "<div class='info'>Username: <strong>admin</strong></div>";
    echo "<div class='info'>Password: <strong>password</strong></div><br>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<div class='info'>1. Go to <a href='login.php'>Login Page</a></div>";
    echo "<div class='info'>2. Use the credentials above to log in</div>";
    echo "<div class='info'>3. After login, you can access <a href='doctors.php'>Doctors Page</a></div>";
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
    echo "<div class='info'>Please check your database configuration in config.php</div>";
}
?>