<?php
// Simple test file to check if the application is working
// This file should only be accessible in development mode

echo "<h2>🔧 USFitness Lab - System Test</h2>";
echo "<p><strong>PHP is working!</strong></p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

echo "<hr>";
echo "<h3>Environment Detection Test</h3>";

try {
    require_once 'config.php';
    echo "<p>✅ Config file loaded successfully</p>";
    echo "<p><strong>Environment:</strong> " . getEnvironment() . "</p>";
    echo "<p><strong>Is Local:</strong> " . (isLocal() ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>Database:</strong> " . $dbname . "</p>";
    echo "<p><strong>Database Host:</strong> " . $host . "</p>";
    echo "<p><strong>Database User:</strong> " . $username . "</p>";
    
    // Test database connection
    echo "<p>✅ Database connection successful</p>";
    echo "<p>✅ PDO object created successfully</p>";
    
    // Test some basic queries
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch();
    echo "<p>✅ Database query test successful - Users in database: " . $result['user_count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h3>Server Information</h3>";
echo "<p><strong>Server Name:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Not set') . "</p>";
echo "<p><strong>HTTP Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "</p>";
echo "<p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "</p>";
echo "<p><strong>Script Path:</strong> " . __FILE__ . "</p>";

echo "<hr>";
echo "<h3>Quick Links</h3>";
echo '<p><a href="environment_status.php">📊 Environment Status</a></p>';
echo '<p><a href="index.php">🏠 Main Application</a></p>';
echo '<p><a href="login.php">🔐 Login Page</a></p>';

echo "<hr>";
echo "<p><em>This test page should only be accessible in development mode.</em></p>";
?>
