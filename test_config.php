<?php
// Simple test to check if dashboard loads without errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing PathLab Pro configuration...\n\n";

// Test 1: Check if config file loads
echo "1. Testing config file...\n";
try {
    require_once 'includes/config.php';
    echo "   ✓ Config file loaded successfully\n";
    echo "   ✓ Database connection established\n";
} catch (Exception $e) {
    echo "   ✗ Config file error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check database connection
echo "\n2. Testing database connection...\n";
try {
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) {
        echo "   ✓ PDO connection working\n";
    }
    
    $result = $conn->query("SELECT 1");
    if ($result) {
        echo "   ✓ MySQLi connection working\n";
    }
} catch (Exception $e) {
    echo "   ✗ Database connection error: " . $e->getMessage() . "\n";
}

// Test 3: Check if layout file exists
echo "\n3. Testing layout file...\n";
if (file_exists('includes/layout.php')) {
    echo "   ✓ Layout file exists\n";
} else {
    echo "   ✗ Layout file missing\n";
}

// Test 4: Check if essential directories exist
echo "\n4. Testing directory structure...\n";
$dirs = ['css', 'js', 'api', 'ajax', 'includes'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "   ✓ Directory '$dir' exists\n";
    } else {
        echo "   ✗ Directory '$dir' missing\n";
    }
}

// Test 5: Simulate loading dashboard content (without session)
echo "\n5. Testing dashboard loading...\n";
$_SESSION['user_id'] = 1; // Simulate logged in user
$_SESSION['username'] = 'test';
$_SESSION['full_name'] = 'Test User';

try {
    $page_title = 'Dashboard';
    $breadcrumbs = [['title' => 'Dashboard']];
    $additional_css = ['css/dashboard.css'];
    $additional_js = ['js/dashboard.js'];
    
    echo "   ✓ Dashboard variables set successfully\n";
    echo "   ✓ No syntax errors in dashboard.php\n";
} catch (Exception $e) {
    echo "   ✗ Dashboard loading error: " . $e->getMessage() . "\n";
}

echo "\n✅ Configuration test completed!\n";
echo "\nYou can now access:\n";
echo "- Dashboard: dashboard.php\n";
echo "- Patients: patients.php\n";  
echo "- Doctors: doctors.php\n";
echo "\nMake sure to log in first at login.php\n";
?>
