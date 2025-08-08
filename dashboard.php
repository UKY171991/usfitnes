<?php
$page_title = 'Dashboard';
$breadcrumbs = [
    ['title' => 'Home', 'url' => 'dashboard.php'],
    ['title' => 'Dashboard']
];
$additional_css = ['css/dashboard.css'];
$additional_js = ['js/dashboard.js'];

ob_start();
?>

<!-- Statistics Cards Row -->
<div class="row" id="statsCards">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="patientsCount">0</h3>
                <p>Total Patients</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="patients.php" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="doctorsCount">0</h3>
                <p>Total Doctors</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-md"></i>
            </div>
            <a href="doctors.php" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="testOrdersCount">0</h3>
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
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="equipmentCount">0</h3>
                            <p>Equipment</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <a href="equipment.php" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <!-- Monthly Statistics Chart -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                Monthly Statistics
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" onclick="refreshMonthlyChart()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-responsive">
                                <canvas id="monthlyChart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Test Types Distribution
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" onclick="refreshPieChart()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-responsive">
                                <canvas id="pieChart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities Row -->
            <div class="row">
                <!-- Recent Test Orders -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clipboard-list mr-1"></i>
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
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Test Type</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="recentOrdersTable">
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <i class="fas fa-spinner fa-spin"></i> Loading...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <a href="test-orders.php" class="btn btn-sm btn-primary">View All Orders</a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock mr-1"></i>
                                Recent Activities
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" onclick="refreshActivities()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="timeline timeline-inverse" id="activitiesTimeline">
                                <div class="text-center">
                                    <i class="fas fa-spinner fa-spin"></i> Loading...
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="#" class="btn btn-sm btn-primary">View All Activities</a>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once 'includes/adminlte3_template.php';
?>
