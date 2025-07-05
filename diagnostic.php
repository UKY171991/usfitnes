<?php
// Diagnostic script to check server status
header('Content-Type: text/html; charset=utf-8');

echo "<h2>PathLab Pro Diagnostic Report</h2>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>";

// Check PHP version
echo "<h3>PHP Configuration</h3>";
echo "<div class='info'>PHP Version: " . phpversion() . "</div>";

// Check database connection
echo "<h3>Database Connection</h3>";
try {
    require_once 'config.php';
    echo "<div class='success'>✓ Database connection successful</div>";
    
    // Check if tables exist
    $tables = ['users', 'doctors'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✓ Table '$table' exists</div>";
            
            // Count records
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<div class='info'>  → $count records in $table</div>";
        } else {
            echo "<div class='error'>✗ Table '$table' does not exist</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Database connection failed: " . $e->getMessage() . "</div>";
}

// Check session functionality
echo "<h3>Session Configuration</h3>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<div class='success'>✓ Sessions are working</div>";
echo "<div class='info'>Session ID: " . session_id() . "</div>";

// Check file permissions
echo "<h3>File System</h3>";
$checkDirs = ['api/', 'includes/', 'css/', 'js/'];
foreach ($checkDirs as $dir) {
    if (is_dir($dir)) {
        echo "<div class='success'>✓ Directory '$dir' exists</div>";
        if (is_readable($dir)) {
            echo "<div class='success'>  → Directory '$dir' is readable</div>";
        } else {
            echo "<div class='error'>  → Directory '$dir' is not readable</div>";
        }
    } else {
        echo "<div class='error'>✗ Directory '$dir' does not exist</div>";
    }
}

// Check specific API files
echo "<h3>API Files</h3>";
$apiFiles = ['api/auth_api.php', 'api/doctors_api.php'];
foreach ($apiFiles as $file) {
    if (file_exists($file)) {
        echo "<div class='success'>✓ API file '$file' exists</div>";
    } else {
        echo "<div class='error'>✗ API file '$file' missing</div>";
    }
}

// Test login functionality
echo "<h3>Login Test</h3>";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "<div class='success'>✓ Demo user 'admin' exists</div>";
    } else {
        echo "<div class='error'>✗ Demo user 'admin' not found</div>";
        echo "<div class='info'>Run setup_database.php to create demo user</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Cannot check users: " . $e->getMessage() . "</div>";
}

echo "<h3>Recommendations</h3>";
echo "<div class='info'>1. If demo user doesn't exist, run: https://usfitnes.com/setup_database.php</div>";
echo "<div class='info'>2. If tables don't exist, run the setup script first</div>";
echo "<div class='info'>3. Login at: https://usfitnes.com/login.php</div>";
echo "<div class='info'>4. After login, access doctors at: https://usfitnes.com/doctors.php</div>";
?>
