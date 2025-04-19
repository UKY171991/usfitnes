<?php
require_once 'config.php';
require_once 'db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
    'gc_maxlifetime' => SESSION_LIFETIME
]);

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Restrict access to Admin only with proper role check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

// Initialize error variable
$error = '';

// Get user data
try {
    $db = Database::getInstance();
    
    // Fetch user data with prepared statement
    $stmt = $db->query(
        "SELECT u.name, u.role, u.last_login FROM users u WHERE u.user_id = :user_id",
        ['user_id' => $_SESSION['user_id']]
    );
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('User not found');
    }

    // Get branch_id from session
    $branch_id = isset($_SESSION['branch_id']) ? $_SESSION['branch_id'] : null;
    if (!$branch_id) {
        throw new Exception('Branch not selected');
    }

    // Fetch branch-specific stats
    $stats = [];
    
    // Total Patients
    $stmt = $db->query(
        "SELECT COUNT(*) FROM patients WHERE branch_id = :branch_id",
        ['branch_id' => $branch_id]
    );
    $stats['patients'] = $stmt->fetchColumn();

    // Pending Tests
    $stmt = $db->query(
        "SELECT COUNT(*) FROM test_requests WHERE branch_id = :branch_id AND status = 'pending'",
        ['branch_id' => $branch_id]
    );
    $stats['pending_tests'] = $stmt->fetchColumn();

    // Today's Reports
    $stmt = $db->query(
        "SELECT COUNT(*) FROM test_results WHERE branch_id = :branch_id AND DATE(created_at) = CURDATE()",
        ['branch_id' => $branch_id]
    );
    $stats['today_reports'] = $stmt->fetchColumn();

    // Monthly Revenue
    $stmt = $db->query(
        "SELECT COALESCE(SUM(amount), 0) FROM payments 
        WHERE branch_id = :branch_id AND MONTH(payment_date) = MONTH(CURRENT_DATE())",
        ['branch_id' => $branch_id]
    );
    $stats['monthly_revenue'] = $stmt->fetchColumn();

    // Recent Activities
    $stmt = $db->query(
        "SELECT * FROM (
            SELECT 'test_request' as type, request_id as id, patient_name, created_at 
            FROM test_requests WHERE branch_id = :branch_id
            UNION ALL
            SELECT 'test_result' as type, result_id as id, patient_name, created_at 
            FROM test_results WHERE branch_id = :branch_id
        ) activities 
        ORDER BY created_at DESC LIMIT 5",
        ['branch_id' => $branch_id]
    );
    $recent_activities = $stmt->fetchAll();

    // Monthly Test Statistics for Chart
    $stmt = $db->query(
        "SELECT 
            MONTH(created_at) as month,
            COUNT(*) as count
        FROM test_requests 
        WHERE branch_id = :branch_id 
        AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
        GROUP BY MONTH(created_at)
        ORDER BY month",
        ['branch_id' => $branch_id]
    );
    $monthly_stats = array_fill(0, 6, 0);
    while ($row = $stmt->fetch()) {
        $monthly_stats[$row['month'] - 1] = (int)$row['count'];
    }

    // Test Categories Distribution for Chart
    $stmt = $db->query(
        "SELECT 
            tc.name,
            COUNT(*) as count
        FROM test_requests tr
        JOIN test_categories tc ON tr.category_id = tc.category_id
        WHERE tr.branch_id = :branch_id
        GROUP BY tc.name
        ORDER BY count DESC
        LIMIT 5",
        ['branch_id' => $branch_id]
    );
    $category_stats = [];
    $category_labels = [];
    while ($row = $stmt->fetch()) {
        $category_labels[] = $row['name'];
        $category_stats[] = (int)$row['count'];
    }

} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $error = "Failed to load dashboard data. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    <?php include('inc/head.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css" rel="stylesheet">
    <style>
        .stats-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .stats-card .card-body {
            padding: 1.5rem;
        }

        .stats-icon {
            font-size: 2rem;
            opacity: 0.8;
        }

        .stats-number {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        .stats-label {
            color: #6c757d;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .activity-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.3s ease;
        }

        .activity-item:hover {
            background-color: #f8f9fa;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        .activity-icon.test-request {
            background-color: rgba(78, 115, 223, 0.1);
            color: #4e73df;
        }

        .activity-icon.test-result {
            background-color: rgba(28, 200, 138, 0.1);
            color: #1cc88a;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
    </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include('inc/top.php'); ?>
        <?php include('inc/sidebar.php'); ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <!-- Welcome Section -->
                <div class="welcome-section mb-4 fade-in">
                    <h1 class="h3 mb-0 text-gray-800">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                    <p class="text-muted">Last login: <?php echo date('M d, Y H:i', strtotime($user['last_login'])); ?></p>
                </div>

                <!-- Stats Row -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4 fade-in" style="animation-delay: 0.1s">
                        <div class="card stats-card primary h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <p class="stats-label">Total Patients</p>
                                        <div class="stats-number"><?php echo number_format($stats['patients']); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people stats-icon text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4 fade-in" style="animation-delay: 0.2s">
                        <div class="card stats-card success h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <p class="stats-label">Pending Tests</p>
                                        <div class="stats-number"><?php echo number_format($stats['pending_tests']); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-clipboard2-pulse stats-icon text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4 fade-in" style="animation-delay: 0.3s">
                        <div class="card stats-card warning h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <p class="stats-label">Today's Reports</p>
                                        <div class="stats-number"><?php echo number_format($stats['today_reports']); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-file-earmark-text stats-icon text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4 fade-in" style="animation-delay: 0.4s">
                        <div class="card stats-card info h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <p class="stats-label">Monthly Revenue</p>
                                        <div class="stats-number">₹<?php echo number_format($stats['monthly_revenue'], 2); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-currency-rupee stats-icon text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-xl-8 mb-4 fade-in" style="animation-delay: 0.5s">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Monthly Test Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="monthlyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 mb-4 fade-in" style="animation-delay: 0.6s">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Test Categories Distribution</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="categoryChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row">
                    <div class="col-12 fade-in" style="animation-delay: 0.7s">
                        <div class="card activity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Activities</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="activity-item d-flex align-items-center">
                                        <div class="activity-icon <?php echo $activity['type'] === 'test_request' ? 'test-request' : 'test-result'; ?>">
                                            <i class="bi bi-<?php echo $activity['type'] === 'test_request' ? 'clipboard2-pulse' : 'file-earmark-text'; ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($activity['patient_name']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo ucfirst(str_replace('_', ' ', $activity['type'])); ?> - 
                                                <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                                            </small>
                                        </div>
                                        <a href="<?php echo $activity['type'] === 'test_request' ? 'test_requests.php' : 'test_results.php'; ?>" class="btn btn-sm btn-link">
                                            View Details
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include('inc/footer.php'); ?>
        </div>
    </div>

    <?php include('inc/js.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        // Monthly Test Statistics Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Tests Conducted',
                    data: <?php echo json_encode($monthly_stats ?? []); ?>,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    tension: 0.4,
                    fill: true
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

        // Test Categories Distribution Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($category_labels ?? []); ?>,
                datasets: [{
                    data: <?php echo json_encode($category_stats ?? []); ?>,
                    backgroundColor: [
                        '#4e73df',
                        '#1cc88a',
                        '#36b9cc',
                        '#f6c23e',
                        '#e74a3b'
                    ],
                    hoverOffset: 4
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

        // Real-time updates
        function updateDashboard() {
            fetch('includes/fetch_dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    // Update stats cards
                    document.querySelector('.stats-number:nth-child(2)').textContent = data.patients;
                    document.querySelector('.stats-number:nth-child(3)').textContent = data.pending_tests;
                    document.querySelector('.stats-number:nth-child(4)').textContent = data.today_reports;
                    document.querySelector('.stats-number:nth-child(5)').textContent = '₹' + data.monthly_revenue;

                    // Update charts
                    monthlyChart.data.datasets[0].data = data.monthly_stats;
                    monthlyChart.update();

                    categoryChart.data.datasets[0].data = data.category_stats;
                    categoryChart.update();
                })
                .catch(error => console.error('Error fetching dashboard stats:', error));
        }

        // Update dashboard every 30 seconds
        setInterval(updateDashboard, 30000);

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>