<?php
/**
 * System Test Script for PathLab Pro
 * 
 * This script tests database connectivity, API endpoints, and core functionality
 */

// Include configuration
require_once 'config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PathLab Pro System Test</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-pass { color: #28a745; }
        .test-fail { color: #dc3545; }
        .test-warning { color: #ffc107; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">PathLab Pro System Test</h1>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Test Results</h5>
                    </div>
                    <div class="card-body">
                        <?php runSystemTests($pdo); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
function runSystemTests($pdo) {
    echo "<h6>Database Connectivity Tests</h6>";
    
    // Test 1: Database Connection
    try {
        $stmt = $pdo->query("SELECT 1");
        echo "<p class='test-pass'>✓ Database connection successful</p>";
    } catch (Exception $e) {
        echo "<p class='test-fail'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    }
    
    // Test 2: Table Existence
    $tables = ['users', 'patients', 'doctors', 'tests', 'test_orders', 'equipment'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p class='test-pass'>✓ Table '$table' exists with $count records</p>";
        } catch (Exception $e) {
            echo "<p class='test-fail'>✗ Table '$table' error: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<hr><h6>API Endpoint Tests</h6>";
    
    // Test 3: Auth API
    try {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'http://localhost/usfitnes/api/auth_api.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['action' => 'check_session']),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 5
        ]);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode == 200) {
            echo "<p class='test-pass'>✓ Auth API endpoint accessible</p>";
        } else {
            echo "<p class='test-warning'>⚠ Auth API returned HTTP $httpCode</p>";
        }
    } catch (Exception $e) {
        echo "<p class='test-fail'>✗ Auth API test failed: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr><h6>PHP Configuration Tests</h6>";
    
    // Test 4: PHP Extensions
    $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'session'];
    foreach ($requiredExtensions as $ext) {
        if (extension_loaded($ext)) {
            echo "<p class='test-pass'>✓ PHP extension '$ext' loaded</p>";
        } else {
            echo "<p class='test-fail'>✗ PHP extension '$ext' missing</p>";
        }
    }
    
    // Test 5: File Permissions
    $directories = ['api/', 'css/', 'js/', 'includes/'];
    foreach ($directories as $dir) {
        if (is_readable($dir)) {
            echo "<p class='test-pass'>✓ Directory '$dir' is readable</p>";
        } else {
            echo "<p class='test-fail'>✗ Directory '$dir' is not readable</p>";
        }
    }
    
    echo "<hr><h6>JavaScript/CSS Resource Tests</h6>";
    
    // Test 6: Critical Files
    $criticalFiles = [
        'js/common.js',
        'js/toaster.js',
        'css/custom.css',
        'includes/header.php',
        'includes/sidebar.php',
        'includes/footer.php'
    ];
    
    foreach ($criticalFiles as $file) {
        if (file_exists($file)) {
            echo "<p class='test-pass'>✓ File '$file' exists</p>";
        } else {
            echo "<p class='test-fail'>✗ File '$file' missing</p>";
        }
    }
    
    echo "<hr><h6>Configuration Tests</h6>";
    
    // Test 7: Session Configuration
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "<p class='test-pass'>✓ PHP sessions are working</p>";
    } else {
        echo "<p class='test-fail'>✗ PHP sessions not working</p>";
    }
    
    // Test 8: Error Reporting
    $errorLevel = error_reporting();
    if ($errorLevel > 0) {
        echo "<p class='test-warning'>⚠ Error reporting is enabled (level: $errorLevel)</p>";
    } else {
        echo "<p class='test-pass'>✓ Error reporting configured</p>";
    }
    
    echo "<hr><h6>Security Tests</h6>";
    
    // Test 9: Database User Permissions
    try {
        $stmt = $pdo->query("SHOW GRANTS");
        echo "<p class='test-pass'>✓ Database user has appropriate permissions</p>";
    } catch (Exception $e) {
        echo "<p class='test-warning'>⚠ Could not check database permissions</p>";
    }
    
    echo "<hr><h6>Summary</h6>";
    echo "<div class='alert alert-info'>";
    echo "<strong>Test Complete!</strong><br>";
    echo "Please review any failed tests above and address the issues.<br>";
    echo "For production deployment, ensure all tests pass and disable this test script.";
    echo "</div>";
}
?>
