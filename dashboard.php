<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// For testing - create a dummy session if none exists
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['full_name'] = 'Admin User';
    $_SESSION['name'] = 'Admin User';
    $_SESSION['user_type'] = 'admin';
    $_SESSION['role'] = 'admin';
}

// Set page title
$page_title = 'Dashboard';

// Include header
include 'includes/header.php';
// Include sidebar with user info
include 'includes/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-tachometer-alt mr-2"></i>Laboratory Dashboard
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- Statistics Cards -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3 id="totalPatients">
                <i class="fas fa-spinner fa-spin"></i>
              </h3>
              <p>Total Patients</p>
            </div>
            <div class="icon">
              <i class="fas fa-user-injured"></i>
            </div>
            <a href="patients.php" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
        
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3 id="todaysTests">
                <i class="fas fa-spinner fa-spin"></i>
              </h3>
              <p>Today's Tests</p>
            </div>
            <div class="icon">
              <i class="fas fa-flask"></i>
            </div>
            <a href="test-orders.php" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
        
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3 id="pendingResults">
                <i class="fas fa-spinner fa-spin"></i>
              </h3>
              <p>Pending Results</p>
            </div>
            <div class="icon">
              <i class="fas fa-file-medical"></i>
            </div>
            <a href="results.php" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
        
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3 id="totalDoctors">
                <i class="fas fa-spinner fa-spin"></i>
              </h3>
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
      </div>

      <!-- Charts and Data -->
      <div class="row">
        <!-- Monthly Test Statistics -->
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-chart-line mr-1"></i>
                Monthly Test Statistics
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" onclick="refreshCharts()">
                  <i class="fas fa-sync-alt"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <canvas id="monthlyChart" height="100"></canvas>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-bolt mr-1"></i>
                Quick Actions
              </h3>
            </div>
            <div class="card-body">
              <div class="d-grid gap-2">
                <a href="patients.php" class="btn btn-primary btn-block">
                  <i class="fas fa-user-plus mr-2"></i>Add New Patient
                </a>
                <a href="test-orders.php" class="btn btn-success btn-block">
                  <i class="fas fa-flask mr-2"></i>New Test Order
                </a>
                <a href="results.php" class="btn btn-warning btn-block">
                  <i class="fas fa-file-medical mr-2"></i>Enter Results
                </a>
                <a href="reports.php" class="btn btn-info btn-block">
                  <i class="fas fa-chart-bar mr-2"></i>Generate Reports
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-history mr-1"></i>
                Recent Activity
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" onclick="refreshActivity()">
                  <i class="fas fa-sync-alt"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Time</th>
                      <th>Activity</th>
                      <th>User</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="recentActivity">
                    <tr>
                      <td colspan="4" class="text-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Loading recent activity...
                      </td>
                    </tr>
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

<?php include 'includes/footer.php'; ?>

<!-- Dashboard Specific Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Dashboard Chart and Stats
let monthlyChart = null;

// Initialize dashboard
$(document).ready(function() {
    console.log('Dashboard initializing...');
    loadDashboardStats();
    loadRecentActivity();
    initializeChart();
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
        loadDashboardStats();
        loadRecentActivity();
    }, 300000);
});

// Initialize Chart.js chart
function initializeChart() {
    const ctx = document.getElementById('monthlyChart');
    if (ctx) {
        try {
            monthlyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Tests Performed',
                        data: [65, 59, 80, 81, 56, 55, 40, 65, 72, 80, 95, 78],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Monthly Test Statistics'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            console.log('Chart initialized successfully');
        } catch (error) {
            console.log('Chart initialization failed:', error);
        }
    } else {
        console.log('Chart canvas not found');
    }
}

// Load dashboard statistics
function loadDashboardStats() {
    console.log('Loading dashboard stats...');
    
    // Set default values first
    $('#totalPatients').html('--');
    $('#todaysTests').html('--');
    $('#pendingResults').html('--');
    $('#totalDoctors').html('--');
    
    // Try to load from API if available
    if (typeof $ !== 'undefined') {
        // Load stats with error handling
        const statsEndpoints = {
            totalPatients: 'api/quick_stats.php?type=patients',
            todaysTests: 'api/quick_stats.php?type=tests_today', 
            pendingResults: 'api/quick_stats.php?type=pending_results',
            totalDoctors: 'api/quick_stats.php?type=doctors'
        };
        
        Object.keys(statsEndpoints).forEach(function(statKey) {
            const endpoint = statsEndpoints[statKey];
            $.ajax({
                url: endpoint,
                method: 'GET',
                dataType: 'json',
                timeout: 5000,
                success: function(response) {
                    if (response && response.success) {
                        $(`#${statKey}`).html(response.count || '0');
                    } else {
                        $(`#${statKey}`).html('0');
                    }
                },
                error: function() {
                    // Keep default value on error
                    console.log(`Failed to load ${statKey}`);
                }
            });
        });
    }
}

// Load recent activity
function loadRecentActivity() {
    console.log('Loading recent activity...');
    
    if (typeof $ !== 'undefined') {
        $.ajax({
            url: 'api/dashboard_api.php',
            method: 'GET',
            data: { action: 'recent_activity' },
            dataType: 'json',
            timeout: 5000,
            success: function(response) {
                if (response && response.success && response.data) {
                    let html = '';
                    response.data.forEach(function(activity) {
                        const statusClass = activity.status === 'completed' ? 'success' : 
                                          activity.status === 'pending' ? 'warning' : 'info';
                        
                        html += `
                            <tr>
                                <td><small>${activity.time || 'N/A'}</small></td>
                                <td>${activity.description || 'No description'}</td>
                                <td>${activity.user || 'System'}</td>
                                <td><span class="badge badge-${statusClass}">${activity.status || 'info'}</span></td>
                            </tr>
                        `;
                    });
                    $('#recentActivity').html(html);
                } else {
                    $('#recentActivity').html(`
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No recent activity found
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                $('#recentActivity').html(`
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            <i class="fas fa-info-circle mr-2"></i>
                            Recent activity will appear here
                        </td>
                    </tr>
                `);
            }
        });
    } else {
        // Fallback if jQuery not loaded
        document.getElementById('recentActivity').innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted">
                    <i class="fas fa-info-circle mr-2"></i>
                    Recent activity will appear here
                </td>
            </tr>
        `;
    }
}

// Refresh functions
function refreshCharts() {
    console.log('Refreshing charts...');
    if (typeof toastr !== 'undefined') {
        toastr.info('Refreshing charts...');
    }
    loadDashboardStats();
    if (monthlyChart) {
        monthlyChart.update();
    }
}

function refreshActivity() {
    console.log('Refreshing activity...');
    if (typeof toastr !== 'undefined') {
        toastr.info('Refreshing activity...');
    }
    loadRecentActivity();
}

// Update monthly chart
function updateMonthlyChart(data) {
    if (monthlyChart && data) {
        monthlyChart.data.datasets[0].data = data;
        monthlyChart.update();
    }
}
</script>
