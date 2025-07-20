<?php
// Set page title
$page_title = 'Dashboard';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = sanitizeInput($_POST['action']);
    
    try {
        switch ($action) {
            case 'stats':
                $stats = [
                    'patients' => [
                        'total' => $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
                        'active' => $pdo->query("SELECT COUNT(*) FROM patients WHERE status = 'active'")->fetchColumn(),
                        'today' => $pdo->query("SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
                        'this_month' => $pdo->query("SELECT COUNT(*) FROM patients WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn()
                    ],
                    'test_orders' => [
                        'total' => $pdo->query("SELECT COUNT(*) FROM test_orders")->fetchColumn(),
                        'pending' => $pdo->query("SELECT COUNT(*) FROM test_orders WHERE status = 'pending'")->fetchColumn(),
                        'completed' => $pdo->query("SELECT COUNT(*) FROM test_orders WHERE status = 'completed'")->fetchColumn(),
                        'today' => $pdo->query("SELECT COUNT(*) FROM test_orders WHERE DATE(created_at) = CURDATE()")->fetchColumn()
                    ],
                    'revenue' => [
                        'today' => $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM test_orders WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
                        'this_month' => $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM test_orders WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn(),
                        'pending_amount' => $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM test_orders WHERE status = 'pending'")->fetchColumn()
                    ],
                    'equipment' => [
                        'total' => $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn(),
                        'active' => $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'active'")->fetchColumn(),
                        'maintenance_due' => $pdo->query("SELECT COUNT(*) FROM equipment WHERE next_maintenance_date <= CURDATE() AND status = 'active'")->fetchColumn()
                    ]
                ];
                jsonResponse(true, 'Statistics retrieved successfully', $stats);
                break;
                
            case 'recent_patients':
                $limit = (int)($_POST['limit'] ?? 10);
                $stmt = $pdo->prepare("
                    SELECT p.*, 
                           TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age
                    FROM patients p 
                    ORDER BY p.created_at DESC 
                    LIMIT ?
                ");
                $stmt->execute([$limit]);
                $patients = $stmt->fetchAll();
                jsonResponse(true, 'Recent patients retrieved successfully', $patients);
                break;
                
            case 'recent_orders':
                $limit = (int)($_POST['limit'] ?? 10);
                $stmt = $pdo->prepare("
                    SELECT to.*, 
                           CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                           p.patient_id as patient_code
                    FROM test_orders to
                    JOIN patients p ON to.patient_id = p.id
                    ORDER BY to.created_at DESC 
                    LIMIT ?
                ");
                $stmt->execute([$limit]);
                $orders = $stmt->fetchAll();
                jsonResponse(true, 'Recent orders retrieved successfully', $orders);
                break;
                
            case 'pending_results':
                $limit = (int)($_POST['limit'] ?? 10);
                $stmt = $pdo->prepare("
                    SELECT to.*, 
                           CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                           p.patient_id as patient_code,
                           DATEDIFF(CURDATE(), to.created_at) as days_pending
                    FROM test_orders to
                    JOIN patients p ON to.patient_id = p.id
                    WHERE to.status IN ('pending', 'in_progress')
                    ORDER BY to.created_at ASC
                    LIMIT ?
                ");
                $stmt->execute([$limit]);
                $pending = $stmt->fetchAll();
                jsonResponse(true, 'Pending results retrieved successfully', $pending);
                break;
                
            case 'charts_data':
                // Monthly revenue data for last 6 months
                $revenueData = [];
                for ($i = 5; $i >= 0; $i--) {
                    $date = date('Y-m', strtotime("-$i months"));
                    $stmt = $pdo->prepare("
                        SELECT COALESCE(SUM(total_amount), 0) as revenue
                        FROM test_orders 
                        WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
                    ");
                    $stmt->execute([$date]);
                    $revenueData[] = [
                        'month' => date('M Y', strtotime("-$i months")),
                        'revenue' => (float)$stmt->fetchColumn()
                    ];
                }
                
                // Daily orders for last 7 days
                $ordersData = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) as orders
                        FROM test_orders 
                        WHERE DATE(created_at) = ?
                    ");
                    $stmt->execute([$date]);
                    $ordersData[] = [
                        'date' => date('M j', strtotime("-$i days")),
                        'orders' => (int)$stmt->fetchColumn()
                    ];
                }
                
                // Test type distribution
                $testDistribution = $pdo->query("
                    SELECT tt.name, COUNT(toi.id) as count
                    FROM test_order_items toi
                    JOIN test_types tt ON toi.test_type_id = tt.id
                    WHERE toi.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY tt.id, tt.name
                    ORDER BY count DESC
                    LIMIT 10
                ")->fetchAll();
                
                jsonResponse(true, 'Charts data retrieved successfully', [
                    'revenue' => $revenueData,
                    'orders' => $ordersData,
                    'test_distribution' => $testDistribution
                ]);
                break;
                
            case 'quick_search':
                $query = sanitizeInput($_POST['query'] ?? '');
                if (strlen($query) < 2) {
                    jsonResponse(false, 'Search query must be at least 2 characters');
                }
                
                $searchTerm = "%$query%";
                
                // Search patients
                $patientStmt = $pdo->prepare("
                    SELECT 'patient' as type, id, patient_id as code, 
                           CONCAT(first_name, ' ', last_name) as name, phone
                    FROM patients 
                    WHERE patient_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR phone LIKE ?
                    LIMIT 5
                ");
                $patientStmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
                $patients = $patientStmt->fetchAll();
                
                // Search test orders
                $orderStmt = $pdo->prepare("
                    SELECT 'order' as type, to.id, to.order_number as code,
                           CONCAT(p.first_name, ' ', p.last_name) as name, to.status
                    FROM test_orders to
                    JOIN patients p ON to.patient_id = p.id
                    WHERE to.order_number LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ?
                    LIMIT 5
                ");
                $orderStmt->execute([$searchTerm, $searchTerm, $searchTerm]);
                $orders = $orderStmt->fetchAll();
                
                $results = array_merge($patients, $orders);
                jsonResponse(true, 'Search completed successfully', $results);
                break;
                
            case 'notifications':
                $notifications = [];
                
                // Equipment maintenance due
                $maintenanceStmt = $pdo->prepare("
                    SELECT name, next_maintenance_date
                    FROM equipment 
                    WHERE next_maintenance_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) 
                    AND status = 'active'
                    ORDER BY next_maintenance_date ASC
                    LIMIT 5
                ");
                $maintenanceStmt->execute();
                $maintenance = $maintenanceStmt->fetchAll();
                
                foreach ($maintenance as $item) {
                    $days = (strtotime($item['next_maintenance_date']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
                    $notifications[] = [
                        'type' => 'warning',
                        'icon' => 'fas fa-wrench',
                        'title' => 'Maintenance Due',
                        'message' => $item['name'] . ' maintenance ' . ($days <= 0 ? 'overdue' : 'due in ' . $days . ' days'),
                        'time' => $item['next_maintenance_date']
                    ];
                }
                
                // Pending orders older than 2 days
                $pendingStmt = $pdo->prepare("
                    SELECT to.order_number, CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                           DATEDIFF(CURDATE(), to.created_at) as days_pending
                    FROM test_orders to
                    JOIN patients p ON to.patient_id = p.id
                    WHERE to.status = 'pending' AND DATEDIFF(CURDATE(), to.created_at) >= 2
                    ORDER BY to.created_at ASC
                    LIMIT 5
                ");
                $pendingStmt->execute();
                $pending = $pendingStmt->fetchAll();
                
                foreach ($pending as $order) {
                    $notifications[] = [
                        'type' => 'danger',
                        'icon' => 'fas fa-clock',
                        'title' => 'Pending Order',
                        'message' => 'Order ' . $order['order_number'] . ' for ' . $order['patient_name'] . ' pending for ' . $order['days_pending'] . ' days',
                        'time' => date('Y-m-d')
                    ];
                }
                
                jsonResponse(true, 'Notifications retrieved successfully', $notifications);
                break;
                
            default:
                jsonResponse(false, 'Invalid action specified');
        }
        
    } catch (Exception $e) {
        error_log("Dashboard AJAX Error: " . $e->getMessage());
        jsonResponse(false, 'An error occurred: ' . $e->getMessage());
    }
}
?>

<style>
.content-wrapper {
    background-color: #f4f6f9;
}

.small-box {
    border-radius: 10px;
    position: relative;
    display: block;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    transition: all 0.3s cubic-bezier(.25,.8,.25,1);
    overflow: hidden;
}

.small-box:hover {
    box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
    transform: translateY(-2px);
}

.small-box .inner {
    padding: 10px 15px;
}

.small-box .inner h3 {
    font-size: 2.2rem;
    font-weight: bold;
    margin: 0 0 10px 0;
    white-space: nowrap;
    padding: 0;
    color: #fff;
    animation: countUp 0.8s ease-out;
}

@keyframes countUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.small-box .inner p {
    font-size: 1rem;
    color: rgba(255,255,255,0.9);
    margin: 0;
}

.small-box .icon {
    transition: all .3s linear;
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 0;
    font-size: 70px;
    color: rgba(0,0,0,0.15);
}

.small-box:hover .icon {
    font-size: 80px;
    transform: rotate(10deg);
}

.small-box .small-box-footer {
    position: relative;
    text-align: center;
    padding: 3px 0;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    z-index: 10;
    background: rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.small-box .small-box-footer:hover {
    color: #fff;
    background: rgba(0,0,0,0.2);
    text-decoration: none;
}

.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    transition: all 0.3s cubic-bezier(.25,.8,.25,1);
}

.card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

.card-header {
    border-bottom: 2px solid #007bff;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border-radius: 10px 10px 0 0 !important;
}

.card-title {
    margin: 0;
    font-weight: 600;
}

.recent-item {
    border-bottom: 1px solid #eee;
    padding: 15px 0;
    transition: background-color 0.2s ease;
}

.recent-item:hover {
    background-color: #f8f9fa;
    border-radius: 5px;
    margin: 0 -15px;
    padding: 15px;
}

.recent-item:last-child {
    border-bottom: none;
}

.status-badge {
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%);
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
}

.notification-item {
    border-left: 4px solid #007bff;
    background: #f8f9fa;
    padding: 10px 15px;
    margin-bottom: 10px;
    border-radius: 0 5px 5px 0;
    transition: all 0.2s ease;
}

.notification-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.notification-item.warning {
    border-left-color: #ffc107;
}

.notification-item.danger {
    border-left-color: #dc3545;
}

.quick-search-results {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-top: none;
    background: white;
    border-radius: 0 0 5px 5px;
}

.search-result-item {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.search-result-item:hover {
    background-color: #f8f9fa;
}

.search-result-item:last-child {
    border-bottom: none;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.welcome-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}

.weather-widget {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <!-- Welcome Section -->
            <div class="welcome-section fade-in">
                <h1 class="display-4 mb-3">
                    <i class="fas fa-chart-line mr-3"></i>
                    Welcome to Pathology Management Dashboard
                </h1>
                <p class="lead mb-0">
                    Monitor your laboratory operations, track performance, and manage patients efficiently
                </p>
                <small class="text-light">
                    <i class="fas fa-calendar-day mr-1"></i>
                    Today is <?php echo date('l, F j, Y'); ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Quick Search -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body py-2">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-primary text-white">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="quickSearch" placeholder="Quick search patients, orders..." autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="searchResults" class="quick-search-results" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-sync-alt mr-2 text-primary"></i>
                                    <small class="text-muted">Last updated: <span id="lastUpdated">Now</span></small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" id="refreshDashboard">
                                    <i class="fas fa-redo-alt mr-1"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-info">
                        <div class="inner">
                            <h3 id="totalPatients">0</h3>
                            <p>Total Patients</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="patients_dynamic.php" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-success">
                        <div class="inner">
                            <h3 id="totalOrders">0</h3>
                            <p>Test Orders</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <a href="test-orders.php" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-warning">
                        <div class="inner">
                            <h3 id="pendingOrders">0</h3>
                            <p>Pending Results</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <a href="test-orders.php?status=pending" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-danger">
                        <div class="inner">
                            <h3 id="todayRevenue">₹0</h3>
                            <p>Today's Revenue</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <a href="reports.php" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Charts and Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-area mr-2"></i>Revenue Trend (Last 6 Months)
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-2"></i>Test Distribution
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="testDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities and Notifications -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-plus mr-2"></i>Recent Patients
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" id="refreshPatients">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <div id="recentPatients">
                                <!-- Content will be loaded via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-flask mr-2"></i>Recent Orders
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" id="refreshOrders">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <div id="recentOrders">
                                <!-- Content will be loaded via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-bell mr-2"></i>Notifications
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-danger" id="notificationCount">0</span>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <div id="notifications">
                                <!-- Content will be loaded via AJAX -->
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
let revenueChart, testDistributionChart;
let searchTimeout;

$(document).ready(function() {
    // Configure Toastr
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
        "timeOut": "3000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    
    // Initialize dashboard
    initializeDashboard();
    
    // Event handlers
    $('#refreshDashboard').click(function() {
        refreshAllData();
        toastr.info('Refreshing dashboard data...');
    });
    
    $('#refreshPatients').click(function() {
        loadRecentPatients();
    });
    
    $('#refreshOrders').click(function() {
        loadRecentOrders();
    });
    
    // Quick search functionality
    $('#quickSearch').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(function() {
                performQuickSearch(query);
            }, 300);
        } else {
            $('#searchResults').hide();
        }
    });
    
    $('#clearSearch').click(function() {
        $('#quickSearch').val('');
        $('#searchResults').hide();
    });
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
        refreshAllData();
    }, 300000);
    
    // Welcome message
    setTimeout(function() {
        toastr.success('Dashboard loaded successfully!', 'Welcome');
    }, 1000);
});

function initializeDashboard() {
    showLoading();
    
    Promise.all([
        loadStatistics(),
        loadChartsData(),
        loadRecentPatients(),
        loadRecentOrders(),
        loadNotifications()
    ]).then(function() {
        hideLoading();
        updateLastUpdated();
        $('.fade-in').addClass('fade-in');
    }).catch(function(error) {
        hideLoading();
        console.error('Dashboard initialization error:', error);
        toastr.error('Failed to load dashboard data');
    });
}

function loadStatistics() {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: 'dashboard_dynamic.php',
            type: 'POST',
            data: { action: 'stats' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    
                    // Animate counter updates
                    animateValue('totalPatients', 0, stats.patients.total, 1000);
                    animateValue('totalOrders', 0, stats.test_orders.total, 1000);
                    animateValue('pendingOrders', 0, stats.test_orders.pending, 1000);
                    
                    // Format revenue
                    $('#todayRevenue').text('₹' + formatNumber(stats.revenue.today));
                    
                    resolve(stats);
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

function loadChartsData() {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: 'dashboard_dynamic.php',
            type: 'POST',
            data: { action: 'charts_data' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    createRevenueChart(response.data.revenue);
                    createTestDistributionChart(response.data.test_distribution);
                    resolve(response.data);
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

function loadRecentPatients() {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: 'dashboard_dynamic.php',
            type: 'POST',
            data: { action: 'recent_patients', limit: 8 },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const patients = response.data;
                    let html = '';
                    
                    if (patients.length > 0) {
                        patients.forEach(function(patient) {
                            const age = patient.age ? patient.age + ' years' : 'Age not specified';
                            const statusBadge = patient.status === 'active' 
                                ? '<span class="badge badge-success">Active</span>'
                                : '<span class="badge badge-secondary">Inactive</span>';
                            
                            html += `
                                <div class="recent-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <span class="badge badge-info mr-2">${patient.patient_id}</span>
                                                ${patient.first_name} ${patient.last_name}
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-phone mr-1"></i>${patient.phone} | ${age}
                                            </small>
                                        </div>
                                        <div class="text-right">
                                            ${statusBadge}
                                            <br>
                                            <small class="text-muted">${formatDateTime(patient.created_at)}</small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html = '<div class="text-center text-muted py-3">No recent patients found</div>';
                    }
                    
                    $('#recentPatients').html(html);
                    resolve(patients);
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

function loadRecentOrders() {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: 'dashboard_dynamic.php',
            type: 'POST',
            data: { action: 'recent_orders', limit: 8 },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const orders = response.data;
                    let html = '';
                    
                    if (orders.length > 0) {
                        orders.forEach(function(order) {
                            const statusColor = getStatusColor(order.status);
                            const amount = order.total_amount ? '₹' + formatNumber(order.total_amount) : 'N/A';
                            
                            html += `
                                <div class="recent-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <span class="badge badge-primary mr-2">${order.order_number}</span>
                                                ${order.patient_name}
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-user mr-1"></i>${order.patient_code} | ${amount}
                                            </small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-${statusColor}">${order.status.replace('_', ' ').toUpperCase()}</span>
                                            <br>
                                            <small class="text-muted">${formatDateTime(order.created_at)}</small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html = '<div class="text-center text-muted py-3">No recent orders found</div>';
                    }
                    
                    $('#recentOrders').html(html);
                    resolve(orders);
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

function loadNotifications() {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: 'dashboard_dynamic.php',
            type: 'POST',
            data: { action: 'notifications' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const notifications = response.data;
                    let html = '';
                    
                    if (notifications.length > 0) {
                        notifications.forEach(function(notification) {
                            html += `
                                <div class="notification-item ${notification.type}">
                                    <div class="d-flex align-items-start">
                                        <i class="${notification.icon} mr-2 mt-1"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">${notification.title}</h6>
                                            <p class="mb-1 small">${notification.message}</p>
                                            <small class="text-muted">${formatDate(notification.time)}</small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        $('#notificationCount').text(notifications.length);
                    } else {
                        html = '<div class="text-center text-muted py-3">No notifications</div>';
                        $('#notificationCount').text('0');
                    }
                    
                    $('#notifications').html(html);
                    resolve(notifications);
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

function performQuickSearch(query) {
    $.ajax({
        url: 'dashboard_dynamic.php',
        type: 'POST',
        data: { action: 'quick_search', query: query },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                let html = '';
                response.data.forEach(function(item) {
                    const icon = item.type === 'patient' ? 'fas fa-user' : 'fas fa-clipboard-list';
                    const badge = item.type === 'patient' ? 'badge-info' : 'badge-primary';
                    
                    html += `
                        <div class="search-result-item" onclick="handleSearchResult('${item.type}', ${item.id})">
                            <div class="d-flex align-items-center">
                                <i class="${icon} mr-2"></i>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">${item.name}</div>
                                    <small class="text-muted">
                                        <span class="badge ${badge}">${item.code}</span>
                                        ${item.type === 'order' ? ' - ' + item.status : ''}
                                    </small>
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('#searchResults').html(html).show();
            } else {
                $('#searchResults').html('<div class="text-center text-muted py-2">No results found</div>').show();
            }
        },
        error: function() {
            $('#searchResults').html('<div class="text-center text-danger py-2">Search failed</div>').show();
        }
    });
}

function handleSearchResult(type, id) {
    $('#searchResults').hide();
    $('#quickSearch').val('');
    
    if (type === 'patient') {
        window.location.href = 'patients_dynamic.php#patient-' + id;
        toastr.info('Redirecting to patient details...');
    } else if (type === 'order') {
        window.location.href = 'test-orders.php#order-' + id;
        toastr.info('Redirecting to order details...');
    }
}

function createRevenueChart(data) {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    if (revenueChart) {
        revenueChart.destroy();
    }
    
    revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.month),
            datasets: [{
                label: 'Revenue (₹)',
                data: data.map(item => item.revenue),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
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
                    ticks: {
                        callback: function(value) {
                            return '₹' + formatNumber(value);
                        }
                    }
                }
            },
            elements: {
                point: {
                    hoverBackgroundColor: '#007bff'
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });
}

function createTestDistributionChart(data) {
    const ctx = document.getElementById('testDistributionChart').getContext('2d');
    
    if (testDistributionChart) {
        testDistributionChart.destroy();
    }
    
    const colors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
    ];
    
    testDistributionChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(item => item.name),
            datasets: [{
                data: data.map(item => item.count),
                backgroundColor: colors.slice(0, data.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });
}

function refreshAllData() {
    showLoading();
    
    Promise.all([
        loadStatistics(),
        loadRecentPatients(),
        loadRecentOrders(),
        loadNotifications()
    ]).then(function() {
        hideLoading();
        updateLastUpdated();
        toastr.success('Dashboard refreshed successfully!');
    }).catch(function(error) {
        hideLoading();
        console.error('Refresh error:', error);
        toastr.error('Failed to refresh dashboard');
    });
}

function updateLastUpdated() {
    $('#lastUpdated').text(new Date().toLocaleTimeString());
}

function animateValue(id, start, end, duration) {
    const element = document.getElementById(id);
    const range = end - start;
    const current = start;
    const increment = end > start ? 1 : -1;
    const stepTime = Math.abs(Math.floor(duration / range));
    
    const timer = setInterval(function() {
        start += increment;
        element.textContent = formatNumber(start);
        
        if (start === end) {
            clearInterval(timer);
        }
    }, stepTime);
}

function formatNumber(num) {
    return new Intl.NumberFormat('en-IN').format(num);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-IN');
}

function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString('en-IN');
}

function getStatusColor(status) {
    const statusColors = {
        'pending': 'warning',
        'in_progress': 'info',
        'completed': 'success',
        'cancelled': 'danger',
        'active': 'success',
        'inactive': 'secondary'
    };
    return statusColors[status] || 'secondary';
}

function showLoading() {
    $('#loadingOverlay').show();
}

function hideLoading() {
    $('#loadingOverlay').hide();
}

// Hide search results when clicking outside
$(document).click(function(e) {
    if (!$(e.target).closest('#quickSearch, #searchResults').length) {
        $('#searchResults').hide();
    }
});

// Keyboard shortcuts
$(document).keydown(function(e) {
    if (e.ctrlKey) {
        switch(e.which) {
            case 82: // Ctrl+R
                e.preventDefault();
                refreshAllData();
                break;
            case 70: // Ctrl+F
                e.preventDefault();
                $('#quickSearch').focus();
                break;
        }
    }
});
</script>
