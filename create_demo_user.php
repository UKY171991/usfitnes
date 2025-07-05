<?php
// Create demo user script
header('Content-Type: text/html; charset=utf-8');
echo "<h2>PathLab Pro Demo User Setup</h2>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>";

require_once 'config.php';
try {
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        echo "<div class='info'>Demo user 'admin' already exists.</div>";
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
            'admin', // user_type must be 'admin' as per ENUM
            'admin@pathlab.com'
        ]);
        echo "<div class='success'>Demo user 'admin' created successfully!</div>";
        echo "<div class='info'>Login credentials:<br>Username: <b>admin</b><br>Password: <b>password</b></div>";
    }
    // List all users
    $stmt = $pdo->query("SELECT id, username, full_name, user_type, created_at FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Current users in database:</h3>";
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
    echo "</table>";
} catch (Exception $e) {
    echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
}
?>
