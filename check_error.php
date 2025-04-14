<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if logs directory exists, if not create it
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Create error log file if it doesn't exist
$errorLog = $logDir . '/error.log';
if (!file_exists($errorLog)) {
    file_put_contents($errorLog, '');
    chmod($errorLog, 0666);
}

echo "<h2>System Information:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current Script Path: " . __FILE__ . "<br><br>";

echo "<h2>Error Log Contents:</h2>";
if (file_exists($errorLog)) {
    echo "<pre>" . htmlspecialchars(file_get_contents($errorLog)) . "</pre>";
} else {
    echo "Error log file not found at: " . $errorLog;
}

echo "<h2>Database Connection Test:</h2>";
try {
    require_once 'config.php';
    echo "Database Settings:<br>";
    echo "Host: " . DB_HOST . "<br>";
    echo "Database: " . DB_NAME . "<br>";
    echo "User: " . DB_USER . "<br><br>";
    
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connection successful!<br>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "Number of users in database: " . $count . "<br>";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
}

echo "<h2>File Permissions:</h2>";
echo "Config file permissions: " . substr(sprintf('%o', fileperms('config.php')), -4) . "<br>";
if (file_exists('config.local.php')) {
    echo "Local config file permissions: " . substr(sprintf('%o', fileperms('config.local.php')), -4) . "<br>";
}
echo "Logs directory permissions: " . substr(sprintf('%o', fileperms($logDir)), -4) . "<br>";
echo "Error log file permissions: " . substr(sprintf('%o', fileperms($errorLog)), -4) . "<br>"; 