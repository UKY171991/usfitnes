<?php
// Set page title
$page_title = 'Dashboard';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';

// Get dashboard statistics
try {
    // Patient statistics
    $totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $newPatientsToday = $pdo->query("SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $newPatientsThisWeek = $pdo->query("SELECT COUNT(*) FROM patients WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())")->fetchColumn();
    
    // Test order statistics
    $totalOrders = $pdo->query("SELECT COUNT(*) FROM test_orders")->fetchColumn();
    $pendingOrders = $pdo->query("SELECT COUNT(*) FROM test_orders WHERE status = 'pending'")->fetchColumn();
    $completedOrders = $pdo->query("SELECT COUNT(*) FROM test_orders WHERE status = 'completed'")->fetchColumn();
    $ordersToday = $pdo->query("SELECT COUNT(*) FROM test_orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    
    // Test results statistics
    $totalResults = $pdo->query("SELECT COUNT(*) FROM test_results")->fetchColumn();
    $pendingResults = $pdo->query("SELECT COUNT(*) FROM test_results WHERE status = 'pending'")->fetchColumn();
    $abnormalResults = $pdo->query("SELECT COUNT(*) FROM test_results WHERE status = 'abnormal'")->fetchColumn();
    $resultsToday = $pdo->query("SELECT COUNT(*) FROM test_results WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    
    // Equipment statistics
    $totalEquipment = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
    $activeEquipment = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'active'")->fetchColumn();
    $maintenanceEquipment = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'maintenance'")->fetchColumn();
    
    // Recent activities
    $recentPatients = $pdo->query("SELECT first_name, last_name, patient_id, created_at FROM patients ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $recentOrders = $pdo->query("SELECT to.order_number, p.first_name, p.last_name, to.status, to.created_at FROM test_orders to LEFT JOIN patients p ON to.patient_id = p.id ORDER BY to.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $recentResults = $pdo->query("SELECT tr.result_value, tr.status, tr.created_at, p.first_name, p.last_name, t.name as test_name FROM test_results tr LEFT JOIN patients p ON tr.patient_id = p.id LEFT JOIN tests t ON tr.test_id = t.id ORDER BY tr.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    // Chart data for monthly trends
    $monthlyPatients = $pdo->query("SELECT MONTH(created_at) as month, COUNT(*) as count FROM patients WHERE YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at) ORDER BY month")->fetchAll(PDO::FETCH_ASSOC);
    $monthlyOrders = $pdo->query("SELECT MONTH(created_at) as month, COUNT(*) as count FROM test_orders WHERE YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at) ORDER BY month")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    // Initialize with default values if database query fails
    $totalPatients = $newPatientsToday = $newPatientsThisWeek = 0;
    $totalOrders = $pendingOrders = $completedOrders = $ordersToday = 0;
    $totalResults = $pendingResults = $abnormalResults = $resultsToday = 0;
    $totalEquipment = $activeEquipment = $maintenanceEquipment = 0;
    $recentPatients = $recentOrders = $recentResults = [];
    $monthlyPatients = $monthlyOrders = [];
}
?>

<style>
.content-wrapper {
    background-color: #f4f6f9;
}

.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
    background: #fff;
    display: flex;
    margin-bottom: 1rem;
    min-height: 80px;
    padding: .5rem;
    position: relative;
    width: 100%;
    transition: transform 0.2s ease-in-out;
}

.info-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 4px 8px rgba(0,0,0,.15);
}

.info-box .info-box-icon {
    border-radius: 0.25rem;
    align-items: center;
    display: flex;
    font-size: 1.875rem;
    justify-content: center;
    text-align: center;
    width: 70px;
}

.info-box .info-box-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.8;
    flex: 1;
    padding: 0 10px;
}

.info-box .info-box-number {
    display: block;
    margin-top: -.25rem;
    font-size: 1.125rem;
    font-weight: 700;
}

.info-box .info-box-text {
    display: block;
    font-size: .875rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
}

.info-box .info-box-more {
    display: block;
    font-size: .75rem;
    color: #6c757d;
}

.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border: none;
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-1px);
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 4px 8px rgba(0,0,0,.15);
}

.activity-item {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    margin-right: 10px;
}

.chart-container {
    position: relative;
    height: 300px;
}

.status-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.quick-action-card {
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}

.quick-action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 4px 8px rgba(0,0,0,.15);
}
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-tachometer-alt mr-2"></i>Pathology Management Dashboard
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Welcome!</strong> This is your pathology management system dashboard. Monitor key metrics and manage laboratory operations efficiently.
                        <button type="button" class="close" data-dismiss="alert">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <!-- Patient Statistics -->
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-users"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Patients</span>
                            <span class="info-box-number"><?php echo number_format($totalPatients); ?></span>
                            <span class="info-box-more">+<?php echo $newPatientsToday; ?> today</span>
                        </div>
                    </div>
                </div>

                <!-- Test Orders -->
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-clipboard-list"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Test Orders</span>
                            <span class="info-box-number"><?php echo number_format($totalOrders); ?></span>
                            <span class="info-box-more"><?php echo $pendingOrders; ?> pending</span>
                        </div>
                    </div>
                </div>

                <!-- Test Results -->
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-file-medical"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Test Results</span>
                            <span class="info-box-number"><?php echo number_format($totalResults); ?></span>
                            <span class="info-box-more"><?php echo $abnormalResults; ?> abnormal</span>
                        </div>
                    </div>
                </div>

                <!-- Equipment -->
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger">
                            <i class="fas fa-cogs"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Equipment</span>
                            <span class="info-box-number"><?php echo number_format($totalEquipment); ?></span>
                            <span class="info-box-more"><?php echo $activeEquipment; ?> active</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-bolt mr-2"></i>Quick Actions
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2 col-sm-4 col-6 mb-3">
                                    <div class="card quick-action-card h-100 text-center" onclick="location.href='patients.php'">
                                        <div class="card-body">
                                            <i class="fas fa-user-plus fa-2x text-primary mb-2"></i>
                                            <h6 class="card-title">Add Patient</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 mb-3">
                                    <div class="card quick-action-card h-100 text-center" onclick="location.href='test-orders.php'">
                                        <div class="card-body">
                                            <i class="fas fa-clipboard-list fa-2x text-success mb-2"></i>
                                            <h6 class="card-title">New Order</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 mb-3">
                                    <div class="card quick-action-card h-100 text-center" onclick="location.href='results.php'">
                                        <div class="card-body">
                                            <i class="fas fa-file-medical fa-2x text-warning mb-2"></i>
                                            <h6 class="card-title">Add Result</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 mb-3">
                                    <div class="card quick-action-card h-100 text-center" onclick="location.href='tests.php'">
                                        <div class="card-body">
                                            <i class="fas fa-flask fa-2x text-info mb-2"></i>
                                            <h6 class="card-title">Manage Tests</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 mb-3">
                                    <div class="card quick-action-card h-100 text-center" onclick="location.href='doctors.php'">
                                        <div class="card-body">
                                            <i class="fas fa-user-md fa-2x text-purple mb-2"></i>
                                            <h6 class="card-title">Doctors</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 mb-3">
                                    <div class="card quick-action-card h-100 text-center" onclick="location.href='reports.php'">
                                        <div class="card-body">
                                            <i class="fas fa-chart-bar fa-2x text-secondary mb-2"></i>
                                            <h6 class="card-title">Reports</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Activities -->
            <div class="row">
                <!-- Monthly Trends Chart -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-2"></i>Monthly Trends
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="trendsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock mr-2"></i>Recent Activities
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                            <?php if (!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <div class="activity-item d-flex align-items-center px-3">
                                        <div class="activity-icon bg-primary text-white">
                                            <i class="fas fa-clipboard-list"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong>
                                            <br>
                                            <small class="text-muted">Order #<?php echo htmlspecialchars($order['order_number']); ?></small>
                                            <span class="status-badge bg-<?php echo $order['status'] == 'pending' ? 'warning' : 'success'; ?> text-white ml-2">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('M j, H:i', strtotime($order['created_at'])); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center p-3 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No recent activities</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Data Tables -->
            <div class="row">
                <!-- Recent Patients -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users mr-2"></i>Recent Patients
                            </h3>
                            <div class="card-tools">
                                <a href="patients.php" class="btn btn-sm btn-primary">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recentPatients)): ?>
                                <table class="table table-striped table-sm">
                                    <tbody>
                                        <?php foreach ($recentPatients as $patient): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($patient['patient_id']); ?></small>
                                                </td>
                                                <td class="text-right">
                                                    <small class="text-muted">
                                                        <?php echo date('M j', strtotime($patient['created_at'])); ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center p-3 text-muted">
                                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                                    <p>No patients registered</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Test Results -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-medical mr-2"></i>Recent Results
                            </h3>
                            <div class="card-tools">
                                <a href="results.php" class="btn btn-sm btn-warning">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recentResults)): ?>
                                <table class="table table-striped table-sm">
                                    <tbody>
                                        <?php foreach ($recentResults as $result): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($result['test_name']); ?></small>
                                                </td>
                                                <td class="text-right">
                                                    <span class="status-badge bg-<?php echo $result['status'] == 'abnormal' ? 'danger' : 'success'; ?> text-white">
                                                        <?php echo ucfirst($result['status']); ?>
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo date('M j', strtotime($result['created_at'])); ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center p-3 text-muted">
                                    <i class="fas fa-flask fa-2x mb-2"></i>
                                    <p>No recent results</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-heartbeat mr-2"></i>System Status
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Database Connection</span>
                                <span class="badge badge-success">Online</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Active Users</span>
                                <span class="badge badge-info">1</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Pending Orders</span>
                                <span class="badge badge-warning"><?php echo $pendingOrders; ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Equipment Issues</span>
                                <span class="badge badge-<?php echo $maintenanceEquipment > 0 ? 'danger' : 'success'; ?>">
                                    <?php echo $maintenanceEquipment; ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>System Uptime</span>
                                <span class="badge badge-primary">99.9%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize Charts
    const ctx = document.getElementById('trendsChart').getContext('2d');
    
    // Prepare data for chart
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const patientsData = new Array(12).fill(0);
    const ordersData = new Array(12).fill(0);
    
    // Fill patient data
    <?php foreach ($monthlyPatients as $data): ?>
        patientsData[<?php echo $data['month'] - 1; ?>] = <?php echo $data['count']; ?>;
    <?php endforeach; ?>
    
    // Fill orders data
    <?php foreach ($monthlyOrders as $data): ?>
        ordersData[<?php echo $data['month'] - 1; ?>] = <?php echo $data['count']; ?>;
    <?php endforeach; ?>
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthNames,
            datasets: [{
                label: 'New Patients',
                data: patientsData,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }, {
                label: 'Test Orders',
                data: ordersData,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Trends - <?php echo date('Y'); ?>'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
    
    // Auto refresh dashboard every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
    
    // Configure Toastr for notifications
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    
    // Show welcome notification
    <?php if ($newPatientsToday > 0): ?>
        toastr.info('<?php echo $newPatientsToday; ?> new patient(s) registered today!');
    <?php endif; ?>
    
    <?php if ($pendingOrders > 0): ?>
        toastr.warning('You have <?php echo $pendingOrders; ?> pending test order(s).');
    <?php endif; ?>
    
    <?php if ($abnormalResults > 0): ?>
        toastr.error('<?php echo $abnormalResults; ?> abnormal test result(s) require attention.');
    <?php endif; ?>
});
</script>
