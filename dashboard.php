<?php
require_once 'config.php';

// Include dashboard data provider
require_once 'includes/dashboard_data.php';

// Initialize dashboard data
$dashboardData = new DashboardDataProvider();
$stats = $dashboardData->getStats();
$recentOrders = $dashboardData->getRecentTestOrders(5);
$systemAlerts = $dashboardData->getSystemAlerts();

// Include AdminLTE header and sidebar
include 'includes/adminlte_template_header.php';
include 'includes/adminlte_sidebar.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-tachometer-alt mr-2 text-primary"></i>hi Laboratory Dashboard
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      
      <!-- Statistics Cards Row -->
      <div class="row">
        <!-- Total Patients -->
        <div class="col-lg-3 col-6">
          <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-user-injured"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Patients</span>
              <span class="info-box-number"><?php echo number_format($stats['total_patients']); ?></span>
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
              <span class="info-box-number"><?php echo number_format($stats['todays_tests']); ?></span>
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
              <span class="info-box-number"><?php echo number_format($stats['pending_results']); ?></span>
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
              <span class="info-box-number"><?php echo number_format($stats['total_doctors']); ?></span>
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
        <!-- Revenue Chart -->
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
                <a href="patients.php?action=add" class="btn btn-primary btn-block mb-2">
                  <i class="fas fa-user-plus mr-2"></i>Add New Patient
                </a>
                <a href="test-orders.php?action=create" class="btn btn-success btn-block mb-2">
                  <i class="fas fa-flask mr-2"></i>Create Test Order
                </a>
                <a href="results.php?action=add" class="btn btn-info btn-block mb-2">
                  <i class="fas fa-file-medical mr-2"></i>Enter Results
                </a>
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
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body p-0">
              <?php if (empty($recentOrders)): ?>
                <div class="text-center p-4">
                  <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                  <h5 class="text-muted">No Recent Test Orders</h5>
                  <p class="text-muted">Test orders will appear here when created.</p>
                  <a href="test-orders.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Create First Test Order
                  </a>
                </div>
              <?php else: ?>
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Patient</th>
                      <th>Test Type</th>
                      <th>Status</th>
                      <th>Date</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($order['patient_name']); ?></td>
                      <td><?php echo htmlspecialchars($order['test_type']); ?></td>
                      <td><?php echo $order['status_badge']; ?></td>
                      <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                      <td>
                        <a href="test-orders.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                          <i class="fas fa-eye"></i>
                        </a>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
            <?php if (!empty($recentOrders)): ?>
            <div class="card-footer clearfix">
              <a href="test-orders.php" class="btn btn-sm btn-info float-left">View All Orders</a>
              <a href="test-orders.php?action=create" class="btn btn-sm btn-secondary float-right">Create New Order</a>
            </div>
            <?php endif; ?>
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
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <?php foreach ($systemAlerts as $alert): ?>
                <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon <?php echo $alert['icon']; ?>"></i> <?php echo htmlspecialchars($alert['title']); ?>!</h5>
                  <?php echo htmlspecialchars($alert['message']); ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
$(document).ready(function() {
    // Initialize monthly chart
    initializeMonthlyChart();
    
    // Auto-refresh data every 5 minutes
    setInterval(function() {
        refreshDashboardData();
    }, 300000);
});

function initializeMonthlyChart() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    // Get monthly data via AJAX
    fetch('api/get_monthly_stats.php')
        .then(response => response.json())
        .then(data => {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Tests Performed',
                        data: data.values || [0, 0, 0, 0, 0, 0],
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
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
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.log('Chart data error:', error);
            // Show fallback chart with no data
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['No Data'],
                    datasets: [{
                        label: 'Tests Performed',
                        data: [0],
                        borderColor: '#6c757d',
                        backgroundColor: 'rgba(108, 117, 125, 0.1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
}

function refreshDashboardData() {
    // Refresh statistics via AJAX
    fetch('api/get_counts.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update counters (if needed for real-time updates)
                console.log('Dashboard data refreshed');
            }
        })
        .catch(error => {
            console.log('Refresh error:', error);
        });
}
</script>

<?php include 'includes/adminlte_template_footer.php'; ?>
