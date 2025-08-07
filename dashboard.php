<?php
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
            <span class="info-box-icon bg-info"><i class="fas fa-user-injured"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Patients</span>
                <span class="info-box-number" id="totalPatients">0</span>
                <div class="progress">
                    <div class="progress-bar bg-info" style="width: 70%"></div>
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
            <span class="info-box-icon bg-success"><i class="fas fa-flask"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Today's Tests</span>
                <span class="info-box-number" id="todaysTests">0</span>
                <div class="progress">
                    <div class="progress-bar bg-success" style="width: 85%"></div>
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
            <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pending Results</span>
                <span class="info-box-number" id="pendingResults">0</span>
                <div class="progress">
                    <div class="progress-bar bg-warning" style="width: 45%"></div>
                </div>
                <span class="progress-description">
                    <a href="results.php" class="text-warning">Process results</a>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Total Doctors -->
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-user-md"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Doctors</span>
                <span class="info-box-number" id="totalDoctors">0</span>
                <div class="progress">
                    <div class="progress-bar bg-danger" style="width: 90%"></div>
                </div>
                <span class="progress-description">
                    <a href="doctors.php" class="text-danger">Manage doctors</a>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Monthly Chart -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-1"></i>
                    Monthly Test Statistics
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="monthlyChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt mr-1"></i>
                    Quick Actions
                </h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-block mb-2" onclick="showAddPatientModal()">
                        <i class="fas fa-user-plus mr-2"></i>Add New Patient
                    </button>
                    <button class="btn btn-success btn-block mb-2" onclick="showAddTestOrderModal()">
                        <i class="fas fa-flask mr-2"></i>Create Test Order
                    </button>
                    <button class="btn btn-info btn-block mb-2" onclick="showAddDoctorModal()">
                        <i class="fas fa-user-md mr-2"></i>Add Doctor
                    </button>
                    <a href="reports.php" class="btn btn-warning btn-block">
                        <i class="fas fa-chart-bar mr-2"></i>Generate Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities Row -->
<div class="row">
    <!-- Recent Test Orders -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    Recent Test Orders
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" onclick="refreshRecentOrders()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="recentOrdersContainer">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                        <p class="text-muted">Loading recent orders...</p>
                    </div>
                </div>
            </div>
            <div class="card-footer clearfix">
                <a href="test-orders.php" class="btn btn-sm btn-info float-left">View All Orders</a>
                <button class="btn btn-sm btn-secondary float-right" onclick="showAddTestOrderModal()">Create New Order</button>
            </div>
        </div>
    </div>
    
    <!-- System Alerts -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bell mr-1"></i>
                    System Alerts
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" onclick="refreshAlerts()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" id="systemAlertsContainer">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                    <p class="text-muted">Loading alerts...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once 'includes/adminlte3_template.php';
?>