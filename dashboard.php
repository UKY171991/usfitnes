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
    
    // Fetch user data
    $stmt = $db->query(
        "SELECT u.first_name, u.last_name, u.role, u.last_login, b.name as branch_name 
         FROM users u 
         LEFT JOIN branches b ON u.branch_id = b.branch_id 
         WHERE u.user_id = :user_id",
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

    // Fetch statistics
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
            SELECT 
                'test_request' as type,
                tr.request_id as id,
                p.first_name,
                p.last_name,
                tr.created_at,
                tr.status
            FROM test_requests tr
            JOIN patients p ON tr.patient_id = p.patient_id
            WHERE tr.branch_id = :branch_id
            UNION ALL
            SELECT 
                'test_result' as type,
                tr.result_id as id,
                p.first_name,
                p.last_name,
                tr.created_at,
                tr.status
            FROM test_results tr
            JOIN patients p ON tr.patient_id = p.patient_id
            WHERE tr.branch_id = :branch_id
        ) activities 
        ORDER BY created_at DESC LIMIT 10",
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
        .welcome-section {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .welcome-section h1 {
            font-size: 1.8rem;
            margin: 0;
            font-weight: 600;
        }
        .welcome-section p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
        }
        .stats-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: transform 0.3s ease;
            height: 100%;
        }
        .stats-card:hover {
            transform: translateY(-3px);
        }
        .stats-icon {
            font-size: 2rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-bottom: 1rem;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #4e73df;
        }
        .stats-label {
            color: #858796;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin: 0;
        }
        .activity-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-top: 2rem;
        }
        .activity-header {
            padding: 1.25rem;
            border-bottom: 1px solid #e3e6f0;
        }
        .activity-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .activity-item {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            align-items: center;
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
            flex-shrink: 0;
        }
        .activity-icon.request {
            background-color: rgba(78, 115, 223, 0.1);
            color: #4e73df;
        }
        .activity-icon.result {
            background-color: rgba(28, 200, 138, 0.1);
            color: #1cc88a;
        }
        .activity-content {
            flex-grow: 1;
        }
        .activity-title {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .activity-time {
            font-size: 0.75rem;
            color: #858796;
        }
        .chart-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-top: 2rem;
            padding: 1.25rem;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-pending {
            background-color: rgba(246, 194, 62, 0.1);
            color: #f6c23e;
        }
        .status-completed {
            background-color: rgba(28, 200, 138, 0.1);
            color: #1cc88a;
        }
    </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include('inc/top.php'); ?>
        <?php include('inc/sidebar.php'); ?>
        
        <main class="app-main">
            <div class="app-content">
                <div class="container-fluid p-4">
                    <!-- Welcome Section -->
                    <div class="welcome-section fade-in">
                        <h1>Welcome back, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h1>
                        <p>
                            <i class="bi bi-building"></i> <?php echo htmlspecialchars($user['branch_name']); ?> | 
                            <i class="bi bi-clock"></i> Last login: <?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'First login'; ?>
                        </p>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Stats Row -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4 fade-in" style="animation-delay: 0.1s">
                            <div class="stats-card p-4">
                                <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="stats-number"><?php echo number_format($stats['patients']); ?></div>
                                <p class="stats-label">Total Patients</p>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4 fade-in" style="animation-delay: 0.2s">
                            <div class="stats-card p-4">
                                <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-clipboard2-pulse"></i>
                                </div>
                                <div class="stats-number"><?php echo number_format($stats['pending_tests']); ?></div>
                                <p class="stats-label">Pending Tests</p>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4 fade-in" style="animation-delay: 0.3s">
                            <div class="stats-card p-4">
                                <div class="stats-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <div class="stats-number"><?php echo number_format($stats['today_reports']); ?></div>
                                <p class="stats-label">Today's Reports</p>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4 fade-in" style="animation-delay: 0.4s">
                            <div class="stats-card p-4">
                                <div class="stats-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-currency-rupee"></i>
                                </div>
                                <div class="stats-number">₹<?php echo number_format($stats['monthly_revenue'], 2); ?></div>
                                <p class="stats-label">Monthly Revenue</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Charts -->
                        <div class="col-xl-8 fade-in" style="animation-delay: 0.5s">
                            <div class="chart-card">
                                <h5 class="card-title mb-4">Monthly Statistics</h5>
                                <canvas id="monthlyChart" height="300"></canvas>
                            </div>
                        </div>

                        <!-- Recent Activities -->
                        <div class="col-xl-4 fade-in" style="animation-delay: 0.6s">
                            <div class="activity-card">
                                <div class="activity-header">
                                    <h5 class="card-title mb-0">Recent Activities</h5>
                                </div>
                                <div class="activity-list">
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon <?php echo $activity['type'] === 'test_request' ? 'request' : 'result'; ?>">
                                                <i class="bi bi-<?php echo $activity['type'] === 'test_request' ? 'clipboard2-pulse' : 'file-earmark-text'; ?>"></i>
                                            </div>
                                            <div class="activity-content">
                                                <h6 class="activity-title">
                                                    <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?>
                                                </h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="activity-time">
                                                        <i class="bi bi-clock"></i> 
                                                        <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                                    </span>
                                                    <span class="status-badge <?php echo $activity['status'] === 'pending' ? 'status-pending' : 'status-completed'; ?>">
                                                        <?php echo ucfirst($activity['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include('inc/js.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        // Monthly Statistics Chart
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Tests',
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
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Auto-update dashboard
        function updateDashboard() {
            fetch('includes/fetch_dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    // Update statistics
                    document.querySelectorAll('.stats-number').forEach((el, index) => {
                        const value = Object.values(data)[index];
                        el.textContent = index === 3 ? `₹${value.toLocaleString()}` : value.toLocaleString();
                    });
                })
                .catch(error => console.error('Error updating dashboard:', error));
        }

        // Update every 30 seconds
        setInterval(updateDashboard, 30000);
    </script>
</body>
</html>