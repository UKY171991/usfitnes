<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'Dashboard';
$breadcrumbs = [
    ['title' => 'Dashboard']
];
$additional_css = ['css/dashboard.css'];
$additional_js = ['js/dashboard.js'];

ob_start();
?>

<!-- Statistics Cards Row -->
<div class="row">
    <!-- Total Patients -->
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user-injured"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Patients</span>
                <span class="info-box-number" id="totalPatients">
                    <span class="loading-placeholder">Loading...</span>
                </span>
                <div class="progress">
                    <div class="progress-bar bg-info" id="patientsProgress" style="width: 0%"></div>
                </div>
                <span class="progress-description">
                    <a href="patients.php" class="text-info">View all patients</a>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Today's Tests -->
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-flask"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Today's Tests</span>
                <span class="info-box-number" id="todaysTests">
                    <span class="loading-placeholder">Loading...</span>
                </span>
                <div class="progress">
                    <div class="progress-bar bg-success" id="testsProgress" style="width: 0%"></div>
                </div>
                <span class="progress-description">
                    <a href="test-orders.php" class="text-success">View test orders</a>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Pending Results -->
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-file-medical"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pending Results</span>
                <span class="info-box-number" id="pendingResults">
                    <span class="loading-placeholder">Loading...</span>
                </span>
                <div class="progress">
                    <div class="progress-bar bg-warning" id="resultsProgress" style="width: 0%"></div>
                </div>
                <span class="progress-description">
                    <a href="results.php" class="text-warning">View results</a>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Monthly Revenue -->
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-rupee-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Monthly Revenue</span>
                <span class="info-box-number" id="monthlyRevenue">
                    <span class="loading-placeholder">Loading...</span>
                </span>
                <div class="progress">
                    <div class="progress-bar bg-danger" id="revenueProgress" style="width: 0%"></div>
                </div>
                <span class="progress-description">
                    <a href="reports.php" class="text-danger">View reports</a>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Test Orders Chart -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header border-transparent">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-1"></i>
                    Test Orders Trend
                </h3>
                <div class="card-tools">
                    <select class="form-control form-control-sm" id="chartPeriod" onchange="updateCharts()">
                        <option value="7">Last 7 days</option>
                        <option value="30" selected>Last 30 days</option>
                        <option value="90">Last 3 months</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <canvas id="ordersChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Test Types Distribution -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header border-transparent">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Test Distribution
                </h3>
            </div>
            <div class="card-body">
                <canvas id="testTypesChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities and Orders Row -->
<div class="row">
    <!-- Recent Test Orders -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock mr-1"></i>
                    Recent Test Orders
                </h3>
                <div class="card-tools">
                    <button class="btn btn-tool" onclick="refreshRecentOrders()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Patient</th>
                                <th>Test Type</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="recentOrdersTable">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="test-orders.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye mr-1"></i>
                    View All Orders
                </a>
            </div>
        </div>
    </div>
    
    <!-- Recent Activities -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-1"></i>
                    Recent Activities
                </h3>
                <div class="card-tools">
                    <button class="btn btn-tool" onclick="refreshActivities()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="timeline" id="activitiesTimeline">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Row -->
<div class="row">
    <!-- Equipment Status -->
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="activeEquipment">
                    <span class="loading-placeholder">0</span>
                </h3>
                <p>Active Equipment</p>
            </div>
            <div class="icon">
                <i class="fas fa-microscope"></i>
            </div>
            <a href="equipment.php" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Active Doctors -->
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="activeDoctors">
                    <span class="loading-placeholder">0</span>
                </h3>
                <p>Active Doctors</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-md"></i>
            </div>
            <a href="doctors.php" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <!-- System Users -->
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 id="systemUsers">
                    <span class="loading-placeholder">0</span>
                </h3>
                <p>System Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="users.php" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <!-- System Health -->
    <div class="col-lg-3 col-md-6">
        <div class="small-box" id="systemHealthBox">
            <div class="inner">
                <h3 id="systemHealth">
                    <span class="loading-placeholder">Good</span>
                </h3>
                <p>System Health</p>
            </div>
            <div class="icon">
                <i class="fas fa-heartbeat"></i>
            </div>
            <a href="system.php" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Calendar and Tasks Row -->
<div class="row">
    <!-- Calendar Widget -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar mr-1"></i>
                    Calendar
                </h3>
            </div>
            <div class="card-body p-0">
                <div id="calendar" class="fc"></div>
            </div>
        </div>
    </div>
    
    <!-- Tasks and Notifications -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tasks mr-1"></i>
                    Today's Tasks & Notifications
                </h3>
                <div class="card-tools">
                    <button class="btn btn-primary btn-sm" onclick="showAddTaskModal()">
                        <i class="fas fa-plus mr-1"></i> Add Task
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-check-circle text-success"></i> Completed Tasks</h6>
                        <ul class="todo-list" id="completedTasks">
                            <li class="text-center text-muted">No completed tasks</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-clock text-warning"></i> Pending Tasks</h6>
                        <ul class="todo-list" id="pendingTasks">
                            <li class="text-center text-muted">No pending tasks</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once 'includes/layout.php';
?>
