<?php
require_once 'config.php';
require_once 'db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

try {
    $db = Database::getInstance();
    
    // Get user details
    $stmt = $db->query(
        "SELECT first_name, last_name, role FROM Users WHERE user_id = :user_id",
        ['user_id' => $_SESSION['user_id']]
    );
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get total patients count
    $stmt = $db->query("SELECT COUNT(*) as total FROM Patients");
    $total_patients = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get pending tests count
    $stmt = $db->query("SELECT COUNT(*) as total FROM Test_Requests WHERE status = 'Pending'");
    $pending_tests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get today's reports count
    $stmt = $db->query(
        "SELECT COUNT(*) as total FROM Test_Results WHERE DATE(recorded_at) = CURDATE()"
    );
    $today_reports = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get monthly revenue
    $stmt = $db->query(
        "SELECT COALESCE(SUM(tc.price), 0) as total 
         FROM Test_Requests tr 
         JOIN tests_catalog_old tc ON tr.test_id = tc.test_id 
         WHERE MONTH(tr.request_date) = MONTH(CURRENT_DATE()) 
         AND YEAR(tr.request_date) = YEAR(CURRENT_DATE())"
    );
    $monthly_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get recent test requests
    $stmt = $db->query(
        "SELECT tr.request_id, p.first_name, p.last_name, tc.test_name, tr.status, tr.request_date 
         FROM Test_Requests tr 
         JOIN Patients p ON tr.patient_id = p.patient_id 
         JOIN tests_catalog_old tc ON tr.test_id = tc.test_id 
         ORDER BY tr.request_date DESC LIMIT 5"
    );
    $recent_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get test categories distribution
    $stmt = $db->query(
        "SELECT tc.category_name, COUNT(tr.request_id) as count 
         FROM Test_Categories tc 
         LEFT JOIN Tests_Catalog t ON tc.category_id = t.category_id 
         LEFT JOIN Test_Requests tr ON t.test_id = tr.test_id 
         GROUP BY tc.category_id"
    );
    $test_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $_SESSION['error_message'] = "Failed to load dashboard data";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'inc/head.php'; ?>
    <title>Dashboard | Lab Management System</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="layout-fixed">
    <div class="wrapper">
        <?php include 'inc/sidebar.php'; ?>
        
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Dashboard</h1>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <!-- Welcome Message -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h4>Welcome back, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h4>
                                    <p class="mb-0">Here's what's happening in your lab today.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Total Patients</h6>
                                            <h3 class="mb-0"><?php echo number_format($total_patients); ?></h3>
                                        </div>
                                        <div class="text-primary">
                                            <i class="bi bi-people fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Pending Tests</h6>
                                            <h3 class="mb-0"><?php echo number_format($pending_tests); ?></h3>
                                        </div>
                                        <div class="text-warning">
                                            <i class="bi bi-clock-history fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Today's Reports</h6>
                                            <h3 class="mb-0"><?php echo number_format($today_reports); ?></h3>
                                        </div>
                                        <div class="text-success">
                                            <i class="bi bi-file-earmark-text fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Monthly Revenue</h6>
                                            <h3 class="mb-0">$<?php echo number_format($monthly_revenue, 2); ?></h3>
                                        </div>
                                        <div class="text-info">
                                            <i class="bi bi-currency-dollar fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Monthly Test Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="monthlyStats" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Test Categories</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="categoryDistribution" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Recent Test Requests</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Patient</th>
                                                    <th>Test</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_requests as $request): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($request['test_name']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $request['status'] === 'Completed' ? 'success' : 
                                                                ($request['status'] === 'Pending' ? 'warning' : 'info'); 
                                                        ?>">
                                                            <?php echo htmlspecialchars($request['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
    // Initialize charts when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Statistics Chart
        const monthlyCtx = document.getElementById('monthlyStats').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Tests Conducted',
                    data: [65, 59, 80, 81, 56, 55, 40, 45, 58, 62, 70, 75],
                    borderColor: '#4361ee',
                    tension: 0.4,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Category Distribution Chart
        const categoryCtx = document.getElementById('categoryDistribution').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($test_categories, 'category_name')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($test_categories, 'count')); ?>,
                    backgroundColor: [
                        '#4361ee',
                        '#2ec4b6',
                        '#ff9f1c',
                        '#e71d36',
                        '#4cc9f0'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });

    // Auto-refresh dashboard data every 30 seconds
    setInterval(() => {
        fetch('includes/fetch_dashboard_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update statistics
                    document.querySelector('#total-patients').textContent = data.total_patients;
                    document.querySelector('#pending-tests').textContent = data.pending_tests;
                    document.querySelector('#today-reports').textContent = data.today_reports;
                    document.querySelector('#monthly-revenue').textContent = data.monthly_revenue;
                }
            })
            .catch(error => console.error('Error updating dashboard:', error));
    }, 30000);
    </script>
</body>
</html>