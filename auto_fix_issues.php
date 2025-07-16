<?php
/**
 * PathLab Pro - Issue Diagnosis and Auto-Fix Script
 * Identifies and automatically fixes common page function issues
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
    <title>PathLab Pro - Issue Diagnosis & Auto-Fix</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .test-pass { color: #28a745; }
        .test-fail { color: #dc3545; }
        .test-warning { color: #ffc107; }
        .test-info { color: #17a2b8; }
        .fix-applied { background: #f8fff8; border-left: 3px solid #28a745; padding: 10px; margin: 5px 0; }
        .issue-found { background: #fff8f8; border-left: 3px solid #dc3545; padding: 10px; margin: 5px 0; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">PathLab Pro - Issue Diagnosis & Auto-Fix</h1>
        
        <div class="row">
            <div class="col-12">
                
                <!-- Database Issues -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-database"></i> Database Issues Check & Fix</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $database_issues = [];
                        $database_fixes = [];

                        // Check database connection
                        try {
                            $pdo->query("SELECT 1");
                            echo '<div class="test-pass"><i class="fas fa-check"></i> Database connection working</div>';
                            
                            // Check and create missing tables
                            $required_tables = [
                                'users' => "CREATE TABLE IF NOT EXISTS users (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    username VARCHAR(50) UNIQUE NOT NULL,
                                    password VARCHAR(255) NOT NULL,
                                    full_name VARCHAR(100) NOT NULL,
                                    email VARCHAR(100),
                                    user_type ENUM('admin', 'user', 'technician') DEFAULT 'user',
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                                )",
                                'patients' => "CREATE TABLE IF NOT EXISTS patients (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    patient_id VARCHAR(20) UNIQUE NOT NULL,
                                    name VARCHAR(100) NOT NULL,
                                    phone VARCHAR(20),
                                    email VARCHAR(100),
                                    date_of_birth DATE,
                                    gender ENUM('male', 'female', 'other'),
                                    address TEXT,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                                )",
                                'doctors' => "CREATE TABLE IF NOT EXISTS doctors (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    doctor_id VARCHAR(20) UNIQUE,
                                    name VARCHAR(100) NOT NULL,
                                    email VARCHAR(100),
                                    phone VARCHAR(20),
                                    specialization VARCHAR(100),
                                    license_number VARCHAR(50),
                                    hospital VARCHAR(100),
                                    address TEXT,
                                    notes TEXT,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                                )",
                                'lab_tests' => "CREATE TABLE IF NOT EXISTS lab_tests (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    test_code VARCHAR(20) UNIQUE NOT NULL,
                                    test_name VARCHAR(100) NOT NULL,
                                    category VARCHAR(50),
                                    description TEXT,
                                    price DECIMAL(10,2),
                                    preparation_instructions TEXT,
                                    normal_range VARCHAR(100),
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                                )",
                                'test_orders' => "CREATE TABLE IF NOT EXISTS test_orders (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    order_id VARCHAR(20) UNIQUE,
                                    patient_id INT,
                                    test_id INT,
                                    doctor_id INT,
                                    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
                                    priority ENUM('normal', 'urgent', 'critical') DEFAULT 'normal',
                                    notes TEXT,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    FOREIGN KEY (patient_id) REFERENCES patients(id),
                                    FOREIGN KEY (test_id) REFERENCES lab_tests(id),
                                    FOREIGN KEY (doctor_id) REFERENCES doctors(id)
                                )",
                                'test_results' => "CREATE TABLE IF NOT EXISTS test_results (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    order_id INT,
                                    result_value VARCHAR(100),
                                    unit VARCHAR(20),
                                    reference_range VARCHAR(100),
                                    status ENUM('normal', 'abnormal', 'critical') DEFAULT 'normal',
                                    notes TEXT,
                                    technician_id INT,
                                    verified_by INT,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    FOREIGN KEY (order_id) REFERENCES test_orders(id),
                                    FOREIGN KEY (technician_id) REFERENCES users(id),
                                    FOREIGN KEY (verified_by) REFERENCES users(id)
                                )",
                                'equipment' => "CREATE TABLE IF NOT EXISTS equipment (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    equipment_id VARCHAR(20) UNIQUE,
                                    name VARCHAR(100) NOT NULL,
                                    model VARCHAR(100),
                                    manufacturer VARCHAR(100),
                                    serial_number VARCHAR(100),
                                    status ENUM('operational', 'maintenance', 'out_of_service') DEFAULT 'operational',
                                    purchase_date DATE,
                                    warranty_expiry DATE,
                                    notes TEXT,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                                )"
                            ];

                            foreach ($required_tables as $table => $create_sql) {
                                try {
                                    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                                    if ($stmt->rowCount() == 0) {
                                        echo "<div class='issue-found'><i class='fas fa-exclamation-triangle'></i> Table '$table' missing - Creating...</div>";
                                        $pdo->exec($create_sql);
                                        echo "<div class='fix-applied'><i class='fas fa-check'></i> Table '$table' created successfully</div>";
                                        $database_fixes[] = "Created table: $table";
                                    } else {
                                        echo "<div class='test-pass'><i class='fas fa-check'></i> Table '$table' exists</div>";
                                    }
                                } catch (Exception $e) {
                                    echo "<div class='test-fail'><i class='fas fa-times'></i> Error with table '$table': " . $e->getMessage() . "</div>";
                                    $database_issues[] = "Table $table error: " . $e->getMessage();
                                }
                            }

                            // Create sample admin user if users table is empty
                            try {
                                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                                $count = $stmt->fetch()['count'];
                                if ($count == 0) {
                                    echo "<div class='issue-found'><i class='fas fa-exclamation-triangle'></i> No users found - Creating admin user...</div>";
                                    $admin_password = password_hash('password', PASSWORD_DEFAULT);
                                    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, user_type) VALUES (?, ?, ?, ?, ?)");
                                    $stmt->execute(['admin', $admin_password, 'Administrator', 'admin@pathlab.com', 'admin']);
                                    echo "<div class='fix-applied'><i class='fas fa-check'></i> Admin user created (username: admin, password: password)</div>";
                                    $database_fixes[] = "Created admin user";
                                }
                            } catch (Exception $e) {
                                echo "<div class='test-fail'><i class='fas fa-times'></i> Error creating admin user: " . $e->getMessage() . "</div>";
                            }

                        } catch (Exception $e) {
                            echo '<div class="test-fail"><i class="fas fa-times"></i> Database connection failed: ' . $e->getMessage() . '</div>';
                            $database_issues[] = "Database connection failed: " . $e->getMessage();
                        }
                        ?>
                    </div>
                </div>

                <!-- JavaScript Issues -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-code"></i> JavaScript Issues Check & Fix</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $js_issues = [];
                        $js_fixes = [];

                        // Check if common.js exists and has required functions
                        if (!file_exists('js/common.js')) {
                            echo "<div class='issue-found'><i class='fas fa-exclamation-triangle'></i> js/common.js missing - Creating...</div>";
                            
                            // Create js directory if it doesn't exist
                            if (!is_dir('js')) {
                                mkdir('js', 0755, true);
                            }
                            
                            // Create common.js with essential functions
                            $common_js = '// PathLab Pro Common JavaScript Functions

function showAlert(type, message, containerId = "alertContainer") {
    const alertClass = type === "success" ? "alert-success" : 
                     type === "error" || type === "danger" ? "alert-danger" : 
                     type === "warning" ? "alert-warning" : "alert-info";
    
    const icon = type === "success" ? "fas fa-check" : 
                type === "error" || type === "danger" ? "fas fa-times" : 
                type === "warning" ? "fas fa-exclamation-triangle" : "fas fa-info-circle";
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="${icon}"></i> ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = alert;
        setTimeout(() => {
            const alertElement = container.querySelector(".alert");
            if (alertElement) {
                $(alertElement).alert("close");
            }
        }, 5000);
    } else {
        console.log(`${type.toUpperCase()}: ${message}`);
    }
}

function escapeHtml(text) {
    if (text === null || text === undefined) return "";
    const map = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        "\"": "&quot;",
        "\'": "&#39;"
    };
    return text.toString().replace(/[&<>"\']/g, m => map[m]);
}

function calculateAge(birthDate) {
    if (!birthDate) return "N/A";
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
}

function formatDate(dateStr) {
    if (!dateStr) return "N/A";
    const date = new Date(dateStr);
    return date.toLocaleDateString();
}

function formatCurrency(amount) {
    if (!amount) return "$0.00";
    return "$" + parseFloat(amount).toFixed(2);
}';
                            
                            file_put_contents('js/common.js', $common_js);
                            echo "<div class='fix-applied'><i class='fas fa-check'></i> js/common.js created with essential functions</div>";
                            $js_fixes[] = "Created js/common.js";
                        } else {
                            echo "<div class='test-pass'><i class='fas fa-check'></i> js/common.js exists</div>";
                            
                            // Check if it has required functions
                            $common_content = file_get_contents('js/common.js');
                            $required_functions = ['showAlert', 'escapeHtml', 'calculateAge', 'formatDate'];
                            
                            foreach ($required_functions as $func) {
                                if (strpos($common_content, "function $func") !== false || strpos($common_content, "$func =") !== false) {
                                    echo "<div class='test-pass'>&nbsp;&nbsp;<i class='fas fa-check'></i> Function '$func' found</div>";
                                } else {
                                    echo "<div class='test-warning'>&nbsp;&nbsp;<i class='fas fa-exclamation-triangle'></i> Function '$func' missing</div>";
                                    $js_issues[] = "Missing function: $func";
                                }
                            }
                        }

                        // Check CSS directory and files
                        if (!file_exists('css/custom.css')) {
                            echo "<div class='issue-found'><i class='fas fa-exclamation-triangle'></i> css/custom.css missing - Creating...</div>";
                            
                            if (!is_dir('css')) {
                                mkdir('css', 0755, true);
                            }
                            
                            $custom_css = '/* PathLab Pro Custom Styles */

.content-wrapper {
    min-height: calc(100vh - 100px);
}

.main-sidebar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
    background-color: rgba(255,255,255,0.2);
    color: #fff;
}

.small-box {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn {
    border-radius: 5px;
}

.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.alert {
    border-radius: 8px;
}

@media (max-width: 767px) {
    .content-wrapper, .right-side {
        min-height: calc(100vh - 100px);
    }
}';
                            
                            file_put_contents('css/custom.css', $custom_css);
                            echo "<div class='fix-applied'><i class='fas fa-check'></i> css/custom.css created</div>";
                            $js_fixes[] = "Created css/custom.css";
                        } else {
                            echo "<div class='test-pass'><i class='fas fa-check'></i> css/custom.css exists</div>";
                        }
                        ?>
                    </div>
                </div>

                <!-- API Issues -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-plug"></i> API Issues Check & Fix</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $api_issues = [];
                        $api_fixes = [];

                        // Check and create missing API directory
                        if (!is_dir('api')) {
                            echo "<div class='issue-found'><i class='fas fa-exclamation-triangle'></i> API directory missing - Creating...</div>";
                            mkdir('api', 0755, true);
                            echo "<div class='fix-applied'><i class='fas fa-check'></i> API directory created</div>";
                            $api_fixes[] = "Created API directory";
                        }

                        // Check critical API files
                        $critical_apis = [
                            'auth_api.php' => 'Authentication API',
                            'patients_api.php' => 'Patients API',
                            'doctors_api.php' => 'Doctors API',
                            'dashboard_api.php' => 'Dashboard API'
                        ];

                        foreach ($critical_apis as $file => $name) {
                            $filepath = "api/$file";
                            if (!file_exists($filepath)) {
                                echo "<div class='test-warning'><i class='fas fa-exclamation-triangle'></i> $name ($file) missing</div>";
                                $api_issues[] = "$name missing";
                            } else {
                                // Check PHP syntax
                                $syntax_check = shell_exec("php -l $filepath 2>&1");
                                if (strpos($syntax_check, 'No syntax errors') !== false) {
                                    echo "<div class='test-pass'><i class='fas fa-check'></i> $name syntax OK</div>";
                                } else {
                                    echo "<div class='test-fail'><i class='fas fa-times'></i> $name has syntax errors</div>";
                                    $api_issues[] = "$name has syntax errors";
                                }
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Page Issues -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-file-code"></i> Page Issues Check & Fix</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $page_issues = [];
                        $page_fixes = [];

                        // Check main pages
                        $main_pages = [
                            'dashboard.php' => 'Dashboard',
                            'patients.php' => 'Patients Management',
                            'doctors.php' => 'Doctors Management',
                            'tests.php' => 'Tests Management',
                            'results.php' => 'Results Management'
                        ];

                        foreach ($main_pages as $file => $name) {
                            if (!file_exists($file)) {
                                echo "<div class='test-fail'><i class='fas fa-times'></i> $name ($file) missing</div>";
                                $page_issues[] = "$name missing";
                            } else {
                                // Check PHP syntax
                                $syntax_check = shell_exec("php -l $file 2>&1");
                                if (strpos($syntax_check, 'No syntax errors') !== false) {
                                    echo "<div class='test-pass'><i class='fas fa-check'></i> $name syntax OK</div>";
                                } else {
                                    echo "<div class='test-fail'><i class='fas fa-times'></i> $name has syntax errors</div>";
                                    $page_issues[] = "$name has syntax errors";
                                }

                                // Check for proper includes
                                $content = file_get_contents($file);
                                if (strpos($content, "include 'includes/header.php'") !== false) {
                                    echo "<div class='test-pass'>&nbsp;&nbsp;<i class='fas fa-check'></i> Header include found</div>";
                                } else {
                                    echo "<div class='test-warning'>&nbsp;&nbsp;<i class='fas fa-exclamation-triangle'></i> Header include missing or different format</div>";
                                }
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5><i class="fas fa-clipboard-list"></i> Summary & Next Steps</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $total_issues = count($database_issues) + count($js_issues) + count($api_issues) + count($page_issues);
                        $total_fixes = count($database_fixes) + count($js_fixes) + count($api_fixes) + count($page_fixes);

                        if ($total_fixes > 0) {
                            echo "<h6 class='text-success'>Fixes Applied ($total_fixes):</h6>";
                            echo "<ul>";
                            foreach (array_merge($database_fixes, $js_fixes, $api_fixes, $page_fixes) as $fix) {
                                echo "<li class='test-pass'>$fix</li>";
                            }
                            echo "</ul>";
                        }

                        if ($total_issues > 0) {
                            echo "<h6 class='text-danger'>Remaining Issues ($total_issues):</h6>";
                            echo "<ul>";
                            foreach (array_merge($database_issues, $js_issues, $api_issues, $page_issues) as $issue) {
                                echo "<li class='test-fail'>$issue</li>";
                            }
                            echo "</ul>";
                        }

                        if ($total_issues == 0 && $total_fixes == 0) {
                            echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> No issues found! All systems appear to be working correctly.</div>';
                        } elseif ($total_issues == 0) {
                            echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> All issues have been automatically fixed! The system should now be working properly.</div>';
                        } else {
                            echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Some issues were automatically fixed, but ' . $total_issues . ' issues remain that require manual attention.</div>';
                        }
                        ?>

                        <h6 class="mt-4">Recommended Next Steps:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ol>
                                    <li>Refresh this page to re-run the diagnosis</li>
                                    <li>Test login functionality with admin/password</li>
                                    <li>Check each page manually for proper functionality</li>
                                    <li>Run the comprehensive function test</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-3">
                                    <a href="?" class="btn btn-primary"><i class="fas fa-redo"></i> Re-run Diagnosis</a>
                                    <a href="comprehensive_function_test.php" class="btn btn-info"><i class="fas fa-microscope"></i> Function Test</a>
                                    <a href="index.php" class="btn btn-success"><i class="fas fa-sign-in-alt"></i> Test Login</a>
                                </div>
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
