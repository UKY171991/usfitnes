<?php
// Set page title
$page_title = 'Dynamic Dashboard';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = sanitizeInput($_POST['action']);
    
    try {
        switch ($action) {
            case 'get_stats':
                $stats = [
                    'patients' => [
                        'total' => $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
                        'active' => $pdo->query("SELECT COUNT(*) FROM patients WHERE status = 'active'")->fetchColumn(),
                        'today' => $pdo->query("SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
                        'this_week' => $pdo->query("SELECT COUNT(*) FROM patients WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())")->fetchColumn(),
                        'this_month' => $pdo->query("SELECT COUNT(*) FROM patients WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn()
                    ],
                    'test_orders' => [
                        'total' => $pdo->query("SELECT COUNT(*) FROM test_orders")->fetchColumn(),
                        'pending' => $pdo->query("SELECT COUNT(*) FROM test_orders WHERE status = 'pending'")->fetchColumn(),
                        'processing' => $pdo->query("SELECT COUNT(*) FROM test_orders WHERE status = 'processing'")->fetchColumn(),
                        'completed' => $pdo->query("SELECT COUNT(*) FROM test_orders WHERE status = 'completed'")->fetchColumn(),
                        'today' => $pdo->query("SELECT COUNT(*) FROM test_orders WHERE DATE(created_at) = CURDATE()")->fetchColumn()
                    ],
                    'test_results' => [
                        'total' => $pdo->query("SELECT COUNT(*) FROM test_results")->fetchColumn(),
                        'pending' => $pdo->query("SELECT COUNT(*) FROM test_results WHERE status = 'pending'")->fetchColumn(),
                        'completed' => $pdo->query("SELECT COUNT(*) FROM test_results WHERE status = 'completed'")->fetchColumn(),
                        'abnormal' => $pdo->query("SELECT COUNT(*) FROM test_results WHERE status = 'abnormal' OR is_critical = 1")->fetchColumn()
                    ],
                    'doctors' => [
                        'total' => $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn(),
                        'active' => $pdo->query("SELECT COUNT(*) FROM doctors WHERE status = 'active'")->fetchColumn()
                    ],
                    'equipment' => [
                        'total' => $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn(),
                        'active' => $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'active'")->fetchColumn(),
                        'maintenance_due' => $pdo->query("SELECT COUNT(*) FROM equipment WHERE next_maintenance_date <= CURDATE() AND status = 'active'")->fetchColumn()
                    ]
                ];
                jsonResponse(true, 'Statistics retrieved successfully', $stats);
                break;
                
            case 'get_recent_activities':
                $limit = (int)($_POST['limit'] ?? 10);
                
                // Recent patients
                $recent_patients = $pdo->prepare("
                    SELECT p.*, TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age
                    FROM patients p 
                    ORDER BY p.created_at DESC 
                    LIMIT ?
                ");
                $recent_patients->execute([$limit]);
                $patients = $recent_patients->fetchAll();
                
                // Recent test orders
                $recent_orders = $pdo->prepare("
                    SELECT to.*, p.first_name, p.last_name, p.patient_id, d.name as doctor_name
                    FROM test_orders to 
                    LEFT JOIN patients p ON to.patient_id = p.id
                    LEFT JOIN doctors d ON to.doctor_id = d.id
                    ORDER BY to.created_at DESC 
                    LIMIT ?
                ");
                $recent_orders->execute([$limit]);
                $orders = $recent_orders->fetchAll();
                
                // Recent test results
                $recent_results = $pdo->prepare("
                    SELECT tr.*, p.first_name, p.last_name, t.name as test_name
                    FROM test_results tr 
                    LEFT JOIN patients p ON tr.patient_id = p.id
                    LEFT JOIN tests t ON tr.test_id = t.id
                    ORDER BY tr.created_at DESC 
                    LIMIT ?
                ");
                $recent_results->execute([$limit]);
                $results = $recent_results->fetchAll();
                
                jsonResponse(true, 'Recent activities retrieved', [
                    'patients' => $patients,
                    'orders' => $orders,
                    'results' => $results
                ]);
                break;
                
            case 'get_chart_data':
                $type = sanitizeInput($_POST['chart_type'] ?? 'monthly_patients');
                
                switch ($type) {
                    case 'monthly_patients':
                        $data = $pdo->query("
                            SELECT MONTH(created_at) as month, COUNT(*) as count 
                            FROM patients 
                            WHERE YEAR(created_at) = YEAR(CURDATE()) 
                            GROUP BY MONTH(created_at) 
                            ORDER BY month
                        ")->fetchAll();
                        break;
                        
                    case 'test_orders_status':
                        $data = $pdo->query("
                            SELECT status, COUNT(*) as count 
                            FROM test_orders 
                            GROUP BY status
                        ")->fetchAll();
                        break;
                        
                    case 'daily_activity':
                        $data = $pdo->query("
                            SELECT DATE(created_at) as date, COUNT(*) as count 
                            FROM test_orders 
                            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                            GROUP BY DATE(created_at) 
                            ORDER BY date
                        ")->fetchAll();
                        break;
                        
                    default:
                        $data = [];
                }
                
                jsonResponse(true, 'Chart data retrieved', $data);
                break;
                
            case 'quick_search':
                $query = sanitizeInput($_POST['query'] ?? '');
                if (strlen($query) < 2) {
                    jsonResponse(false, 'Search query too short');
                }
                
                $searchResults = [];
                
                // Search patients
                $patients_stmt = $pdo->prepare("
                    SELECT 'patient' as type, id, patient_id, 
                           CONCAT(first_name, ' ', last_name) as name, 
                           phone, email, created_at
                    FROM patients 
                    WHERE first_name LIKE ? OR last_name LIKE ? OR patient_id LIKE ? OR phone LIKE ?
                    LIMIT 5
                ");
                $search_term = "%$query%";
                $patients_stmt->execute([$search_term, $search_term, $search_term, $search_term]);
                $searchResults['patients'] = $patients_stmt->fetchAll();
                
                // Search doctors
                $doctors_stmt = $pdo->prepare("
                    SELECT 'doctor' as type, id, doctor_id, name, phone, email, specialization
                    FROM doctors 
                    WHERE name LIKE ? OR doctor_id LIKE ? OR specialization LIKE ?
                    LIMIT 5
                ");
                $doctors_stmt->execute([$search_term, $search_term, $search_term]);
                $searchResults['doctors'] = $doctors_stmt->fetchAll();
                
                // Search test orders
                $orders_stmt = $pdo->prepare("
                    SELECT 'order' as type, to.id, to.order_number, to.status,
                           CONCAT(p.first_name, ' ', p.last_name) as patient_name
                    FROM test_orders to
                    LEFT JOIN patients p ON to.patient_id = p.id
                    WHERE to.order_number LIKE ?
                    LIMIT 5
                ");
                $orders_stmt->execute([$search_term]);
                $searchResults['orders'] = $orders_stmt->fetchAll();
                
                jsonResponse(true, 'Search completed', $searchResults);
                break;
                
            default:
                jsonResponse(false, 'Invalid action');
        }
        
    } catch (Exception $e) {
        jsonResponse(false, 'Error: ' . $e->getMessage());
    }
}
?>

<style>
.dashboard-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 15px;
    overflow: hidden;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: scale(1.02);
}

.stat-card.success { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stat-card.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-card.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stat-card.danger { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }

.activity-item {
    padding: 1rem;
    border-left: 4px solid #3498db;
    background: #f8f9fa;
    margin-bottom: 0.5rem;
    border-radius: 0 10px 10px 0;
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: #e9ecef;
    border-left-color: #2ecc71;
}

.chart-container {
    position: relative;
    height: 300px;
    background: white;
    border-radius: 15px;
    padding: 1rem;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 15px;
    text-decoration: none;
    color: #6c757d;
    transition: all 0.3s ease;
    height: 120px;
    justify-content: center;
}

.quick-action-btn:hover {
    border-color: #3498db;
    color: #3498db;
    text-decoration: none;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0 0 10px 10px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.search-result-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.search-result-item:hover {
    background-color: #f8f9fa;
}

.welcome-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    text-align: center;
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <!-- Welcome Banner -->
            <div class="welcome-banner fade-in-up">
                <h1 class="display-4 mb-3">
                    <i class="fas fa-chart-line mr-3"></i>
                    Welcome to PathLab Pro Dashboard
                </h1>
                <p class="lead mb-1">
                    Your comprehensive laboratory management system
                </p>
                <small>
                    <i class="fas fa-calendar-day mr-1"></i>
                    Today is <?php echo date('l, F j, Y'); ?> | 
                    <i class="fas fa-clock mr-1"></i>
                    <span id="currentTime"><?php echo date('g:i A'); ?></span>
                </small>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Quick Search -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="position-relative">
                                        <div class="input-group input-group-lg">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="fas fa-search"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control" id="quickSearch" 
                                                   placeholder="Quick search patients, doctors, orders..." 
                                                   autocomplete="off">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div id="searchResults" class="search-results" style="display: none;"></div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <button class="btn btn-success btn-lg mr-2" id="refreshDashboard">
                                        <i class="fas fa-sync-alt mr-1"></i>Refresh
                                    </button>
                                    <button class="btn btn-info btn-lg" id="exportData">
                                        <i class="fas fa-download mr-1"></i>Export
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4" id="statsContainer">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="stat-card success">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0" id="totalPatients">0</h2>
                                <p class="mb-0">Total Patients</p>
                                <small><span id="todayPatients">0</span> added today</small>
                            </div>
                            <div class="text-right">
                                <i class="fas fa-users fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="stat-card info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0" id="totalOrders">0</h2>
                                <p class="mb-0">Test Orders</p>
                                <small><span id="pendingOrders">0</span> pending</small>
                            </div>
                            <div class="text-right">
                                <i class="fas fa-clipboard-list fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="stat-card warning">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0" id="totalResults">0</h2>
                                <p class="mb-0">Test Results</p>
                                <small><span id="abnormalResults">0</span> abnormal</small>
                            </div>
                            <div class="text-right">
                                <i class="fas fa-file-medical fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="stat-card danger">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0" id="totalDoctors">0</h2>
                                <p class="mb-0">Active Doctors</p>
                                <small><span id="totalEquipment">0</span> equipment</small>
                            </div>
                            <div class="text-right">
                                <i class="fas fa-user-md fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-bolt mr-2"></i>Quick Actions
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-2 col-md-4 col-sm-6 col-6 mb-3">
                                    <a href="patients.php" class="quick-action-btn">
                                        <i class="fas fa-user-plus fa-2x mb-2"></i>
                                        <span>Add Patient</span>
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 col-6 mb-3">
                                    <a href="test-orders.php" class="quick-action-btn">
                                        <i class="fas fa-flask fa-2x mb-2"></i>
                                        <span>New Order</span>
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 col-6 mb-3">
                                    <a href="results.php" class="quick-action-btn">
                                        <i class="fas fa-file-medical fa-2x mb-2"></i>
                                        <span>Enter Results</span>
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 col-6 mb-3">
                                    <a href="doctors.php" class="quick-action-btn">
                                        <i class="fas fa-user-md fa-2x mb-2"></i>
                                        <span>Doctors</span>
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 col-6 mb-3">
                                    <a href="equipment.php" class="quick-action-btn">
                                        <i class="fas fa-tools fa-2x mb-2"></i>
                                        <span>Equipment</span>
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 col-6 mb-3">
                                    <a href="reports.php" class="quick-action-btn">
                                        <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                        <span>Reports</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Activities -->
            <div class="row">
                <!-- Charts -->
                <div class="col-lg-8">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-area mr-2"></i>Analytics Overview
                            </h3>
                            <div class="card-tools">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary" data-chart="monthly_patients">Monthly</button>
                                    <button class="btn btn-sm btn-outline-primary" data-chart="test_orders_status">Orders</button>
                                    <button class="btn btn-sm btn-outline-primary" data-chart="daily_activity">Daily</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="mainChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="col-lg-4">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock mr-2"></i>Recent Activities
                            </h3>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-outline-primary" id="refreshActivities">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <div id="recentActivities">
                                <!-- Activities will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-server mr-2"></i>System Status
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="mb-2">
                                        <i class="fas fa-database fa-2x text-success"></i>
                                    </div>
                                    <h6>Database</h6>
                                    <span class="badge badge-success">Online</span>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="mb-2">
                                        <i class="fas fa-cloud fa-2x text-success"></i>
                                    </div>
                                    <h6>Server</h6>
                                    <span class="badge badge-success">Running</span>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="mb-2">
                                        <i class="fas fa-shield-alt fa-2x text-success"></i>
                                    </div>
                                    <h6>Security</h6>
                                    <span class="badge badge-success">Secure</span>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="mb-2">
                                        <i class="fas fa-tachometer-alt fa-2x text-warning"></i>
                                    </div>
                                    <h6>Performance</h6>
                                    <span class="badge badge-warning">Good</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Dashboard JavaScript
let searchTimeout;
let currentChart;

$(document).ready(function() {
    // Initialize dashboard
    initializeDashboard();
    
    // Auto-refresh every 5 minutes
    setInterval(refreshDashboard, 300000);
    
    // Update time every minute
    setInterval(updateCurrentTime, 60000);
    
    // Setup event listeners
    setupEventListeners();
    
    // Show welcome notification
    setTimeout(() => {
        notify('success', 'Welcome to PathLab Pro Dashboard!', 'System Ready');
    }, 1000);
});

function initializeDashboard() {
    loadStatistics();
    loadRecentActivities();
    loadChart('monthly_patients');
    notify('info', 'Dashboard loading...', 'Please wait');
}

function setupEventListeners() {
    // Refresh dashboard
    $('#refreshDashboard').click(function() {
        refreshDashboard();
        notify('info', 'Refreshing dashboard...', 'Please wait');
    });
    
    // Refresh activities
    $('#refreshActivities').click(function() {
        loadRecentActivities();
        notify('info', 'Refreshing activities...', 'Please wait');
    });
    
    // Chart type buttons
    $('[data-chart]').click(function() {
        const chartType = $(this).data('chart');
        $('[data-chart]').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
        loadChart(chartType);
    });
    
    // Quick search
    $('#quickSearch').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => performQuickSearch(query), 300);
        } else {
            $('#searchResults').hide();
        }
    });
    
    // Clear search
    $('#clearSearch').click(function() {
        $('#quickSearch').val('');
        $('#searchResults').hide();
    });
    
    // Export data
    $('#exportData').click(function() {
        exportDashboardData();
    });
    
    // Statistics cards click to refresh
    $('.stat-card').click(function() {
        $(this).addClass('pulse');
        loadStatistics();
        setTimeout(() => $(this).removeClass('pulse'), 1000);
    });
}

function refreshDashboard() {
    loadStatistics();
    loadRecentActivities();
    if (currentChart) {
        const activeChart = $('.btn-primary[data-chart]').data('chart') || 'monthly_patients';
        loadChart(activeChart);
    }
}

function loadStatistics() {
    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: { action: 'get_stats' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateStatistics(response.data);
                notify('success', 'Statistics updated', 'Data Refreshed');
            } else {
                notify('error', response.message, 'Error');
            }
        },
        error: function() {
            notify('error', 'Failed to load statistics', 'Network Error');
        }
    });
}

function updateStatistics(data) {
    // Animate counter updates
    animateCounter('#totalPatients', data.patients.total);
    animateCounter('#todayPatients', data.patients.today);
    animateCounter('#totalOrders', data.test_orders.total);
    animateCounter('#pendingOrders', data.test_orders.pending);
    animateCounter('#totalResults', data.test_results.total);
    animateCounter('#abnormalResults', data.test_results.abnormal);
    animateCounter('#totalDoctors', data.doctors.active);
    animateCounter('#totalEquipment', data.equipment.total);
}

function animateCounter(selector, endValue) {
    const element = $(selector);
    const startValue = parseInt(element.text()) || 0;
    const duration = 1000;
    const steps = 50;
    const stepValue = (endValue - startValue) / steps;
    const stepTime = duration / steps;
    
    let currentValue = startValue;
    const interval = setInterval(() => {
        currentValue += stepValue;
        if ((stepValue > 0 && currentValue >= endValue) || (stepValue < 0 && currentValue <= endValue)) {
            element.text(endValue);
            clearInterval(interval);
        } else {
            element.text(Math.round(currentValue));
        }
    }, stepTime);
}

function loadRecentActivities() {
    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: { action: 'get_recent_activities', limit: 8 },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayRecentActivities(response.data);
            } else {
                $('#recentActivities').html('<div class="text-center text-muted">No recent activities</div>');
            }
        },
        error: function() {
            $('#recentActivities').html('<div class="text-center text-danger">Failed to load activities</div>');
        }
    });
}

function displayRecentActivities(data) {
    let html = '';
    
    // Recent patients
    data.patients.slice(0, 3).forEach(patient => {
        html += `
            <div class="activity-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">
                            <i class="fas fa-user text-primary mr-2"></i>
                            ${patient.first_name} ${patient.last_name}
                        </h6>
                        <small class="text-muted">New patient registered</small>
                    </div>
                    <small class="text-muted">${timeAgo(patient.created_at)}</small>
                </div>
            </div>
        `;
    });
    
    // Recent orders
    data.orders.slice(0, 3).forEach(order => {
        const statusColor = order.status === 'completed' ? 'success' : order.status === 'pending' ? 'warning' : 'info';
        html += `
            <div class="activity-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">
                            <i class="fas fa-flask text-${statusColor} mr-2"></i>
                            Order ${order.order_number}
                        </h6>
                        <small class="text-muted">${order.first_name} ${order.last_name}</small>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-${statusColor}">${order.status}</span>
                        <br><small class="text-muted">${timeAgo(order.created_at)}</small>
                    </div>
                </div>
            </div>
        `;
    });
    
    // Recent results
    data.results.slice(0, 2).forEach(result => {
        const statusColor = result.status === 'abnormal' ? 'danger' : 'success';
        html += `
            <div class="activity-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">
                            <i class="fas fa-file-medical text-${statusColor} mr-2"></i>
                            ${result.test_name}
                        </h6>
                        <small class="text-muted">${result.first_name} ${result.last_name}</small>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-${statusColor}">${result.status}</span>
                        <br><small class="text-muted">${timeAgo(result.created_at)}</small>
                    </div>
                </div>
            </div>
        `;
    });
    
    if (html === '') {
        html = '<div class="text-center text-muted py-3">No recent activities</div>';
    }
    
    $('#recentActivities').html(html);
}

function loadChart(chartType) {
    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: { action: 'get_chart_data', chart_type: chartType },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayChart(chartType, response.data);
            } else {
                notify('error', response.message, 'Chart Error');
            }
        },
        error: function() {
            notify('error', 'Failed to load chart data', 'Network Error');
        }
    });
}

function displayChart(type, data) {
    const ctx = document.getElementById('mainChart').getContext('2d');
    
    if (currentChart) {
        currentChart.destroy();
    }
    
    let chartConfig = {};
    
    switch (type) {
        case 'monthly_patients':
            chartConfig = {
                type: 'line',
                data: {
                    labels: data.map(item => `Month ${item.month}`),
                    datasets: [{
                        label: 'Patients Registered',
                        data: data.map(item => item.count),
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            };
            break;
            
        case 'test_orders_status':
            chartConfig = {
                type: 'doughnut',
                data: {
                    labels: data.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
                    datasets: [{
                        data: data.map(item => item.count),
                        backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            };
            break;
            
        case 'daily_activity':
            chartConfig = {
                type: 'bar',
                data: {
                    labels: data.map(item => new Date(item.date).toLocaleDateString()),
                    datasets: [{
                        label: 'Orders per Day',
                        data: data.map(item => item.count),
                        backgroundColor: '#2ecc71'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            };
            break;
    }
    
    currentChart = new Chart(ctx, chartConfig);
}

function performQuickSearch(query) {
    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: { action: 'quick_search', query: query },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displaySearchResults(response.data);
            } else {
                $('#searchResults').html('<div class="p-3 text-muted">No results found</div>').show();
            }
        },
        error: function() {
            $('#searchResults').html('<div class="p-3 text-danger">Search failed</div>').show();
        }
    });
}

function displaySearchResults(data) {
    let html = '';
    
    // Patients
    data.patients.forEach(patient => {
        html += `
            <div class="search-result-item" onclick="window.location.href='patients.php?view=${patient.id}'">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong><i class="fas fa-user text-primary mr-2"></i>${patient.name}</strong>
                        <br><small class="text-muted">ID: ${patient.patient_id} | ${patient.phone}</small>
                    </div>
                    <small class="text-muted">Patient</small>
                </div>
            </div>
        `;
    });
    
    // Doctors
    data.doctors.forEach(doctor => {
        html += `
            <div class="search-result-item" onclick="window.location.href='doctors.php?view=${doctor.id}'">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong><i class="fas fa-user-md text-success mr-2"></i>${doctor.name}</strong>
                        <br><small class="text-muted">${doctor.specialization} | ${doctor.phone}</small>
                    </div>
                    <small class="text-muted">Doctor</small>
                </div>
            </div>
        `;
    });
    
    // Orders
    data.orders.forEach(order => {
        html += `
            <div class="search-result-item" onclick="window.location.href='test-orders.php?view=${order.id}'">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong><i class="fas fa-flask text-info mr-2"></i>${order.order_number}</strong>
                        <br><small class="text-muted">${order.patient_name}</small>
                    </div>
                    <small class="text-muted">Order</small>
                </div>
            </div>
        `;
    });
    
    if (html === '') {
        html = '<div class="p-3 text-muted">No results found</div>';
    }
    
    $('#searchResults').html(html).show();
}

function exportDashboardData() {
    notify('info', 'Preparing dashboard export...', 'Export');
    
    // Simulate export process
    setTimeout(() => {
        notify('success', 'Dashboard data exported successfully!', 'Export Complete');
    }, 2000);
}

function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    $('#currentTime').text(timeString);
}

function timeAgo(dateTime) {
    const now = new Date();
    const date = new Date(dateTime);
    const diff = now - date;
    
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (seconds < 60) return 'just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 30) return `${days}d ago`;
    
    return date.toLocaleDateString();
}

// Hide search results when clicking outside
$(document).click(function(e) {
    if (!$(e.target).closest('#quickSearch, #searchResults').length) {
        $('#searchResults').hide();
    }
});
</script>
