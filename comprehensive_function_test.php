<?php
/**
 * Comprehensive Function Test for PathLab Pro
 * Tests all page functions and identifies issues
 */

// Include configuration
require_once 'config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Test results array
$test_results = [];
$page_issues = [];
$function_issues = [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PathLab Pro - Comprehensive Function Test</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .test-pass { color: #28a745; }
        .test-fail { color: #dc3545; }
        .test-warning { color: #ffc107; }
        .test-info { color: #17a2b8; }
        .card { margin-bottom: 20px; }
        .function-test { margin: 10px 0; padding: 10px; border-left: 3px solid #ddd; }
        .function-test.pass { border-color: #28a745; background: #f8fff8; }
        .function-test.fail { border-color: #dc3545; background: #fff8f8; }
        .function-test.warning { border-color: #ffc107; background: #fffdf8; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">PathLab Pro - Comprehensive Function Test</h1>
        
        <div class="row">
            <div class="col-12">
                
                <!-- Database Connection Test -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-database"></i> Database Connection & Schema Test</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Test database connection
                        try {
                            $pdo->query("SELECT 1");
                            echo '<div class="test-pass"><i class="fas fa-check"></i> Database connection successful</div>';
                            
                            // Test required tables
                            $required_tables = [
                                'users', 'patients', 'doctors', 'lab_tests', 'test_orders', 
                                'test_results', 'equipment', 'test_categories'
                            ];
                            
                            foreach ($required_tables as $table) {
                                try {
                                    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                                    if ($stmt->rowCount() > 0) {
                                        echo "<div class='test-pass'><i class='fas fa-check'></i> Table '$table' exists</div>";
                                        
                                        // Check table structure
                                        $count_stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                                        $count = $count_stmt->fetch()['count'];
                                        echo "<div class='test-info'>&nbsp;&nbsp;&nbsp;<i class='fas fa-info'></i> $count records in '$table'</div>";
                                    } else {
                                        echo "<div class='test-fail'><i class='fas fa-times'></i> Table '$table' missing</div>";
                                        $page_issues[] = "Missing table: $table";
                                    }
                                } catch (Exception $e) {
                                    echo "<div class='test-fail'><i class='fas fa-times'></i> Error checking table '$table': " . $e->getMessage() . "</div>";
                                    $page_issues[] = "Table error: $table - " . $e->getMessage();
                                }
                            }
                            
                        } catch (Exception $e) {
                            echo '<div class="test-fail"><i class="fas fa-times"></i> Database connection failed: ' . $e->getMessage() . '</div>';
                            $page_issues[] = "Database connection failed: " . $e->getMessage();
                        }
                        ?>
                    </div>
                </div>

                <!-- Page Function Tests -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-file-code"></i> Page Function Tests</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Test each main page
                        $main_pages = [
                            'index.php' => 'Login Page',
                            'dashboard.php' => 'Dashboard',
                            'patients.php' => 'Patients Management',
                            'doctors.php' => 'Doctors Management',
                            'tests.php' => 'Tests Management',
                            'test-orders.php' => 'Test Orders',
                            'results.php' => 'Results Management',
                            'equipment.php' => 'Equipment Management',
                            'users.php' => 'Users Management',
                            'reports.php' => 'Reports',
                            'settings.php' => 'Settings'
                        ];

                        foreach ($main_pages as $file => $name) {
                            echo "<div class='function-test'>";
                            echo "<h6><i class='fas fa-file'></i> Testing: $name ($file)</h6>";
                            
                            // Check if file exists
                            if (!file_exists($file)) {
                                echo "<div class='test-fail'><i class='fas fa-times'></i> File not found</div>";
                                $page_issues[] = "$name - File not found";
                                echo "</div>";
                                continue;
                            }
                            
                            // Check for PHP syntax errors
                            $syntax_check = shell_exec("php -l $file 2>&1");
                            if (strpos($syntax_check, 'No syntax errors') !== false) {
                                echo "<div class='test-pass'><i class='fas fa-check'></i> PHP syntax valid</div>";
                            } else {
                                echo "<div class='test-fail'><i class='fas fa-times'></i> PHP syntax error: $syntax_check</div>";
                                $page_issues[] = "$name - PHP syntax error";
                            }
                            
                            // Check for required includes
                            $content = file_get_contents($file);
                            
                            if (strpos($content, "include 'includes/header.php'") !== false || strpos($content, 'include "includes/header.php"') !== false) {
                                echo "<div class='test-pass'><i class='fas fa-check'></i> Header include found</div>";
                            } else {
                                echo "<div class='test-warning'><i class='fas fa-exclamation-triangle'></i> Header include missing or different format</div>";
                            }
                            
                            if (strpos($content, "include 'includes/sidebar.php'") !== false || strpos($content, 'include "includes/sidebar.php"') !== false) {
                                echo "<div class='test-pass'><i class='fas fa-check'></i> Sidebar include found</div>";
                            } else {
                                echo "<div class='test-warning'><i class='fas fa-exclamation-triangle'></i> Sidebar include missing or different format</div>";
                            }
                            
                            // Check for common JavaScript functions
                            if (strpos($content, 'showAlert') !== false) {
                                echo "<div class='test-pass'><i class='fas fa-check'></i> showAlert function used</div>";
                            } else {
                                echo "<div class='test-info'><i class='fas fa-info'></i> showAlert function not used</div>";
                            }
                            
                            // Check for AJAX usage
                            if (strpos($content, '$.ajax') !== false || strpos($content, 'XMLHttpRequest') !== false) {
                                echo "<div class='test-pass'><i class='fas fa-check'></i> AJAX functionality found</div>";
                            } else {
                                echo "<div class='test-info'><i class='fas fa-info'></i> No AJAX functionality detected</div>";
                            }
                            
                            // Check for form handling
                            if (strpos($content, '$_POST') !== false || strpos($content, '$_GET') !== false) {
                                echo "<div class='test-pass'><i class='fas fa-check'></i> Form handling found</div>";
                            } else {
                                echo "<div class='test-info'><i class='fas fa-info'></i> No form handling detected</div>";
                            }
                            
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>

                <!-- API Function Tests -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-plug"></i> API Function Tests</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Test API files
                        $api_files = [
                            'api/auth_api.php' => 'Authentication API',
                            'api/patients_api.php' => 'Patients API', 
                            'api/doctors_api.php' => 'Doctors API',
                            'api/tests_api.php' => 'Tests API',
                            'api/test_orders_api.php' => 'Test Orders API',
                            'api/results_api.php' => 'Results API',
                            'api/equipment_api.php' => 'Equipment API',
                            'api/users_api.php' => 'Users API',
                            'api/dashboard_api.php' => 'Dashboard API'
                        ];

                        foreach ($api_files as $file => $name) {
                            echo "<div class='function-test'>";
                            echo "<h6><i class='fas fa-plug'></i> Testing: $name ($file)</h6>";
                            
                            if (!file_exists($file)) {
                                echo "<div class='test-fail'><i class='fas fa-times'></i> API file not found</div>";
                                $function_issues[] = "$name - File not found";
                                echo "</div>";
                                continue;
                            }
                            
                            // Check PHP syntax
                            $syntax_check = shell_exec("php -l $file 2>&1");
                            if (strpos($syntax_check, 'No syntax errors') !== false) {
                                echo "<div class='test-pass'><i class='fas fa-check'></i> PHP syntax valid</div>";
                            } else {
                                echo "<div class='test-fail'><i class='fas fa-times'></i> PHP syntax error</div>";
                                $function_issues[] = "$name - PHP syntax error";
                            }
                            
                            // Check API structure
                            $content = file_get_contents($file);
                            
                            if (strpos($content, 'header(') !== false && strpos($content, 'application/json') !== false) {
                                echo "<div class='test-pass'><i class='fas fa-check'></i> JSON headers configured</div>";
                            } else {
                                echo "<div class='test-warning'><i class='fas fa-exclamation-triangle'></i> JSON headers missing</div>";
                            }
                            
                            if (strpos($content, '$_SESSION') !== false || strpos($content, 'session_start') !== false) {
                                echo "<div class='test-pass'><i class='fas fa-check'></i> Session handling found</div>";
                            } else {
                                echo "<div class='test-warning'><i class='fas fa-exclamation-triangle'></i> No session handling</div>";
                            }
                            
                            if (strpos($content, 'try') !== false && strpos($content, 'catch') !== false) {
                                echo "<div class='test-pass'><i class='fas fa-check'></i> Error handling found</div>";
                            } else {
                                echo "<div class='test-warning'><i class='fas fa-exclamation-triangle'></i> No error handling</div>";
                            }
                            
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Include Files Test -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-folder-open"></i> Include Files Test</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $include_files = [
                            'includes/header.php' => 'Header Include',
                            'includes/sidebar.php' => 'Sidebar Include',
                            'includes/footer.php' => 'Footer Include',
                            'includes/init.php' => 'Initialization Include',
                            'config.php' => 'Configuration File'
                        ];

                        foreach ($include_files as $file => $name) {
                            echo "<div class='function-test'>";
                            echo "<h6><i class='fas fa-file-code'></i> Testing: $name ($file)</h6>";
                            
                            if (!file_exists($file)) {
                                echo "<div class='test-fail'><i class='fas fa-times'></i> Include file not found</div>";
                                $function_issues[] = "$name - File not found";
                                echo "</div>";
                                continue;
                            }
                            
                            // Check PHP syntax
                            $syntax_check = shell_exec("php -l $file 2>&1");
                            if (strpos($syntax_check, 'No syntax errors') !== false) {
                                echo "<div class='test-pass'><i class='fas fa-check'></i> PHP syntax valid</div>";
                            } else {
                                echo "<div class='test-fail'><i class='fas fa-times'></i> PHP syntax error</div>";
                                $function_issues[] = "$name - PHP syntax error";
                            }
                            
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>

                <!-- JavaScript Files Test -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5><i class="fas fa-code"></i> JavaScript Files Test</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $js_files = [
                            'js/common.js' => 'Common JavaScript Functions',
                            'js/dashboard.js' => 'Dashboard JavaScript',
                            'js/patients.js' => 'Patients JavaScript',
                            'js/doctors.js' => 'Doctors JavaScript'
                        ];

                        foreach ($js_files as $file => $name) {
                            echo "<div class='function-test'>";
                            echo "<h6><i class='fas fa-file-code'></i> Testing: $name ($file)</h6>";
                            
                            if (file_exists($file)) {
                                echo "<div class='test-pass'><i class='fas fa-check'></i> JavaScript file exists</div>";
                                
                                $content = file_get_contents($file);
                                
                                // Check for common functions
                                if (strpos($content, 'showAlert') !== false) {
                                    echo "<div class='test-pass'><i class='fas fa-check'></i> showAlert function found</div>";
                                }
                                if (strpos($content, 'function') !== false || strpos($content, '=>') !== false) {
                                    echo "<div class='test-pass'><i class='fas fa-check'></i> Functions defined</div>";
                                }
                            } else {
                                echo "<div class='test-warning'><i class='fas fa-exclamation-triangle'></i> JavaScript file not found (optional)</div>";
                            }
                            
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Issues Summary -->
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5><i class="fas fa-exclamation-triangle"></i> Issues Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        if (empty($page_issues) && empty($function_issues)) {
                            echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> No critical issues found! All page functions appear to be working properly.</div>';
                        } else {
                            if (!empty($page_issues)) {
                                echo '<h6>Page Issues:</h6>';
                                echo '<ul>';
                                foreach ($page_issues as $issue) {
                                    echo '<li class="test-fail">' . htmlspecialchars($issue) . '</li>';
                                }
                                echo '</ul>';
                            }
                            
                            if (!empty($function_issues)) {
                                echo '<h6>Function Issues:</h6>';
                                echo '<ul>';
                                foreach ($function_issues as $issue) {
                                    echo '<li class="test-fail">' . htmlspecialchars($issue) . '</li>';
                                }
                                echo '</ul>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Recommendations -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5><i class="fas fa-lightbulb"></i> Recommendations</h5>
                    </div>
                    <div class="card-body">
                        <h6>To Fix Common Issues:</h6>
                        <ol>
                            <li><strong>Missing Database Tables:</strong> Run <code>setup_database.php</code> to create missing tables</li>
                            <li><strong>PHP Syntax Errors:</strong> Check and fix PHP syntax in reported files</li>
                            <li><strong>Missing API Files:</strong> Create missing API files with proper structure</li>
                            <li><strong>JavaScript Issues:</strong> Ensure <code>js/common.js</code> exists with required functions</li>
                            <li><strong>Include File Issues:</strong> Verify all include files exist and have correct paths</li>
                        </ol>
                        
                        <h6>Quick Actions:</h6>
                        <div class="mt-3">
                            <a href="setup_test.php" class="btn btn-primary"><i class="fas fa-tools"></i> Run System Setup</a>
                            <a href="test_system.php" class="btn btn-info"><i class="fas fa-microscope"></i> Run System Test</a>
                            <a href="functional_test.php" class="btn btn-warning"><i class="fas fa-check-double"></i> Run Functional Test</a>
                            <a href="diagnostic.php" class="btn btn-secondary"><i class="fas fa-stethoscope"></i> Run Diagnostics</a>
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
