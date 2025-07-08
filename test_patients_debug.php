<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP Error Check for Patients Page<br><br>";

// Test if we can include the config file
try {
    require_once 'config.php';
    echo "✅ Config file loaded successfully<br>";
} catch(Exception $e) {
    echo "❌ Config file error: " . $e->getMessage() . "<br>";
}

// Test if we can include the header
try {
    $page_title = 'Test Page';
    require_once 'includes/header.php';
    echo "✅ Header file loaded successfully<br>";
} catch(Exception $e) {
    echo "❌ Header file error: " . $e->getMessage() . "<br>";
}

// Test database query
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
    $count = $stmt->fetch()['count'];
    echo "✅ Database query successful, patients count: " . $count . "<br>";
} catch(Exception $e) {
    echo "❌ Database query error: " . $e->getMessage() . "<br>";
}

// Test session variables
echo "<br><strong>Session Variables:</strong><br>";
session_start();
foreach($_SESSION as $key => $value) {
    echo $key . " = " . $value . "<br>";
}

?>
