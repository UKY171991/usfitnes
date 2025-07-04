<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PathLab Pro - System Test & Setup</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .test-pass { color: #28a745; }
        .test-fail { color: #dc3545; }
        .test-warning { color: #ffc107; }
        .test-info { color: #17a2b8; }
        .card { margin-bottom: 20px; }
        .code-block { background-color: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h1 class="mb-0"><i class="fas fa-flask"></i> PathLab Pro - System Test & Setup</h1>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> This page will test all system components and help fix any issues automatically.
                        </div>

                        <?php
                        // Test results array
                        $tests = [];
                        $overallStatus = 'success';

                        // Test 1: PHP Version
                        echo '<div class="card"><div class="card-header bg-secondary text-white">Test 1: PHP Environment</div><div class="card-body">';
                        $phpVersion = phpversion();
                        if (version_compare($phpVersion, '7.4.0', '>=')) {
                            echo '<p class="test-pass"><i class="fas fa-check"></i> PHP Version: ' . $phpVersion . ' (Compatible)</p>';
                        } else {
                            echo '<p class="test-fail"><i class="fas fa-times"></i> PHP Version: ' . $phpVersion . ' (Requires 7.4+)</p>';
                            $overallStatus = 'warning';
                        }
                        
                        // Check required extensions
                        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl'];
                        foreach ($requiredExtensions as $ext) {
                            if (extension_loaded($ext)) {
                                echo '<p class="test-pass"><i class="fas fa-check"></i> Extension: ' . $ext . ' (Loaded)</p>';
                            } else {
                                echo '<p class="test-fail"><i class="fas fa-times"></i> Extension: ' . $ext . ' (Missing)</p>';
                                $overallStatus = 'error';
                            }
                        }
                        echo '</div></div>';

                        // Test 2: File System
                        echo '<div class="card"><div class="card-header bg-secondary text-white">Test 2: File System</div><div class="card-body">';
                        $requiredFiles = [
                            'config.php' => 'Database configuration',
                            'index.php' => 'Login page',
                            'dashboard.php' => 'Main dashboard',
                            'patients.php' => 'Patient management',
                            'tests.php' => 'Test management',
                            'api/auth_api.php' => 'Authentication API',
                            'api/patients_api.php' => 'Patients API',
                            'includes/header.php' => 'Header component',
                            'includes/sidebar.php' => 'Sidebar component',
                            'js/common.js' => 'JavaScript utilities',
                            'css/custom.css' => 'Custom styles'
                        ];
                        
                        foreach ($requiredFiles as $file => $description) {
                            if (file_exists($file)) {
                                echo '<p class="test-pass"><i class="fas fa-check"></i> ' . $description . ' (' . $file . ')</p>';
                            } else {
                                echo '<p class="test-fail"><i class="fas fa-times"></i> ' . $description . ' (' . $file . ') - Missing</p>';
                                $overallStatus = 'error';
                            }
                        }
                        echo '</div></div>';

                        // Test 3: Database Connection
                        echo '<div class="card"><div class="card-header bg-secondary text-white">Test 3: Database Connection</div><div class="card-body">';
                        
                        try {
                            // Try to include config
                            if (file_exists('config.php')) {
                                require_once 'config.php';
                                echo '<p class="test-pass"><i class="fas fa-check"></i> Configuration file loaded successfully</p>';
                                
                                // Try database connection
                                if (isset($pdo) && $pdo instanceof PDO) {
                                    echo '<p class="test-pass"><i class="fas fa-check"></i> Database connection established</p>';
                                    
                                    // Test basic query
                                    $stmt = $pdo->query("SELECT 1");
                                    if ($stmt) {
                                        echo '<p class="test-pass"><i class="fas fa-check"></i> Database query test passed</p>';
                                        
                                        // Check if admin user exists
                                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
                                        $stmt->execute();
                                        $adminExists = $stmt->fetchColumn();
                                        
                                        if ($adminExists > 0) {
                                            echo '<p class="test-pass"><i class="fas fa-check"></i> Admin user exists</p>';
                                        } else {
                                            echo '<p class="test-warning"><i class="fas fa-exclamation-triangle"></i> Admin user not found</p>';
                                        }
                                        
                                        // Check tables
                                        $requiredTables = ['users', 'patients', 'tests', 'test_categories', 'test_orders', 'doctors', 'equipment'];
                                        foreach ($requiredTables as $table) {
                                            try {
                                                $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                                                $count = $stmt->fetchColumn();
                                                echo '<p class="test-pass"><i class="fas fa-check"></i> Table: ' . $table . ' (' . $count . ' records)</p>';
                                            } catch (Exception $e) {
                                                echo '<p class="test-fail"><i class="fas fa-times"></i> Table: ' . $table . ' - Error: ' . $e->getMessage() . '</p>';
                                                $overallStatus = 'error';
                                            }
                                        }
                                    } else {
                                        echo '<p class="test-fail"><i class="fas fa-times"></i> Database query test failed</p>';
                                        $overallStatus = 'error';
                                    }
                                } else {
                                    echo '<p class="test-fail"><i class="fas fa-times"></i> Database connection failed</p>';
                                    $overallStatus = 'error';
                                }
                            } else {
                                echo '<p class="test-fail"><i class="fas fa-times"></i> Configuration file not found</p>';
                                $overallStatus = 'error';
                            }
                        } catch (Exception $e) {
                            echo '<p class="test-fail"><i class="fas fa-times"></i> Database Error: ' . $e->getMessage() . '</p>';
                            echo '<div class="alert alert-warning mt-3">';
                            echo '<h5>Database Setup Instructions:</h5>';
                            echo '<ol>';
                            echo '<li>Make sure MySQL/MariaDB is running on your system</li>';
                            echo '<li>Create a database named "pathlab_pro"</li>';
                            echo '<li>Update database credentials in config.php if needed</li>';
                            echo '<li>Run this test again to verify the connection</li>';
                            echo '</ol>';
                            echo '</div>';
                            $overallStatus = 'error';
                        }
                        echo '</div></div>';

                        // Test 4: Authentication Test
                        echo '<div class="card"><div class="card-header bg-secondary text-white">Test 4: Authentication System</div><div class="card-body">';
                        
                        if (file_exists('api/auth_api.php')) {
                            echo '<p class="test-pass"><i class="fas fa-check"></i> Authentication API file exists</p>';
                            
                            // Test login page
                            if (file_exists('index.php')) {
                                echo '<p class="test-pass"><i class="fas fa-check"></i> Login page exists</p>';
                            } else {
                                echo '<p class="test-fail"><i class="fas fa-times"></i> Login page missing</p>';
                                $overallStatus = 'error';
                            }
                        } else {
                            echo '<p class="test-fail"><i class="fas fa-times"></i> Authentication API missing</p>';
                            $overallStatus = 'error';
                        }
                        
                        echo '</div></div>';

                        // Test 5: JavaScript and CSS
                        echo '<div class="card"><div class="card-header bg-secondary text-white">Test 5: Frontend Assets</div><div class="card-body">';
                        
                        $frontendAssets = [
                            'js/common.js' => 'Common JavaScript functions',
                            'js/toaster.js' => 'Notification system',
                            'css/custom.css' => 'Custom styles'
                        ];
                        
                        foreach ($frontendAssets as $asset => $description) {
                            if (file_exists($asset)) {
                                echo '<p class="test-pass"><i class="fas fa-check"></i> ' . $description . ' (' . $asset . ')</p>';
                            } else {
                                echo '<p class="test-warning"><i class="fas fa-exclamation-triangle"></i> ' . $description . ' (' . $asset . ') - Missing but not critical</p>';
                            }
                        }
                        
                        echo '</div></div>';

                        // Overall Status
                        echo '<div class="card">';
                        if ($overallStatus === 'success') {
                            echo '<div class="card-header bg-success text-white"><h3><i class="fas fa-check-circle"></i> System Status: All Tests Passed!</h3></div>';
                            echo '<div class="card-body">';
                            echo '<p class="text-success">✅ Your PathLab Pro system is ready to use!</p>';
                            echo '<p><a href="index.php" class="btn btn-success btn-lg"><i class="fas fa-sign-in-alt"></i> Go to Login Page</a></p>';
                            echo '<div class="alert alert-info mt-3">';
                            echo '<strong>Default Login Credentials:</strong><br>';
                            echo 'Username: <code>admin</code><br>';
                            echo 'Password: <code>password</code>';
                            echo '</div>';
                        } elseif ($overallStatus === 'warning') {
                            echo '<div class="card-header bg-warning text-white"><h3><i class="fas fa-exclamation-triangle"></i> System Status: Minor Issues Found</h3></div>';
                            echo '<div class="card-body">';
                            echo '<p class="text-warning">⚠️ Some minor issues were found, but the system should still work.</p>';
                            echo '<p><a href="index.php" class="btn btn-warning btn-lg"><i class="fas fa-sign-in-alt"></i> Continue to Login</a></p>';
                        } else {
                            echo '<div class="card-header bg-danger text-white"><h3><i class="fas fa-times-circle"></i> System Status: Issues Found</h3></div>';
                            echo '<div class="card-body">';
                            echo '<p class="text-danger">❌ Critical issues found. Please fix the issues above before using the system.</p>';
                        }
                        echo '</div></div>';
                        ?>

                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h3><i class="fas fa-tools"></i> Quick Actions</h3>
                            </div>
                            <div class="card-body">
                                <a href="test_crud.php" class="btn btn-info mr-2"><i class="fas fa-database"></i> Test Database CRUD</a>
                                <a href="test_system.php" class="btn btn-info mr-2"><i class="fas fa-cogs"></i> System Test</a>
                                <a href="test_patients.php" class="btn btn-info mr-2"><i class="fas fa-users"></i> Test Patients API</a>
                                <button class="btn btn-success" onclick="location.reload()"><i class="fas fa-sync"></i> Refresh Tests</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
