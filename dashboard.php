<?php
require_once 'db_connect.php';

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Restrict access to Admin only
if ($_SESSION['role'] !== 'Admin') {
    header("Location: index.php"); // Redirect non-Admins to dashboard
    exit();
}

// Get user data
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Fetch user data
    $stmt = $pdo->prepare("SELECT name, role FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Fetch quick stats
    $stats = [
        'patients' => $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
        'tests' => $pdo->query("SELECT COUNT(*) FROM test_requests WHERE status = 'pending'")->fetchColumn(),
        'reports' => $pdo->query("SELECT COUNT(*) FROM test_results WHERE DATE(created_at) = CURDATE()")->fetchColumn()
    ];
} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $error = "Failed to load dashboard data";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <?php include('inc/head.php'); ?>
    
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include('inc/top.php'); ?>
        <?php include('inc/sidebar.php'); ?>
        
        <div class="main-content">
            
            <div class="welcome-section">
                <div class="container-fluid">
                    <h1 class="h3 mb-0 text-gray-800">Welcome back, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?>!</h1>
                    <p class="text-muted">Here's what's happening at <?php echo APP_NAME; ?> today.</p>
                </div>
            </div>

            <div class="container-fluid">
                <!-- Stats Row -->
                <div class="row mb-4">
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card stats-card primary h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <p class="stats-label">Total Patients</p>
                                        <div class="stats-number"><?php echo number_format($stats['patients'] ?? 0); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people stats-icon text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card stats-card success h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <p class="stats-label">Pending Tests</p>
                                        <div class="stats-number"><?php echo number_format($stats['tests'] ?? 0); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-clipboard2-pulse stats-icon text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card stats-card warning h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <p class="stats-label">Today's Reports</p>
                                        <div class="stats-number"><?php echo number_format($stats['reports'] ?? 0); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-file-earmark-text stats-icon text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Cards -->
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="card action-card">
                            <div class="card-header bg-primary">
                                <i class="bi bi-clipboard-data"></i>
                                <h5 class="mb-0">Manage Tests</h5>
                            </div>
                            <div class="card-body">
                                <p>Add or edit pathology tests like CBC, Blood Sugar, and more.</p>
                                <a href="test.php" class="btn btn-primary w-100">
                                    <i class="bi bi-arrow-right-circle"></i>Go to Tests
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card action-card">
                            <div class="card-header bg-success">
                                <i class="bi bi-person-lines-fill"></i>
                                <h5 class="mb-0">Manage Patients</h5>
                            </div>
                            <div class="card-body">
                                <p>View and manage patient records, history, and test results.</p>
                                <a href="patients.php" class="btn btn-success w-100">
                                    <i class="bi bi-arrow-right-circle"></i>Go to Patients
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card action-card">
                            <div class="card-header bg-warning">
                                <i class="bi bi-file-earmark-bar-graph"></i>
                                <h5 class="mb-0">Generate Reports</h5>
                            </div>
                            <div class="card-body">
                                <p>Generate and manage pathology reports for patients.</p>
                                <a href="reports.php" class="btn btn-warning w-100 text-white">
                                    <i class="bi bi-arrow-right-circle"></i>Go to Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer mt-auto">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; <?php echo APP_NAME . " " . date('Y'); ?></div>
                        <div class="text-muted">Version 1.0</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <?php include('inc/js.php'); ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Handle sidebar toggle
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelector('.app-wrapper').classList.toggle('sidebar-collapsed');
                });
            }
        });
    </script>
</body>
</html>