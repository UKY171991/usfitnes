
<?php
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
              <h3 id="todayTests">
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

<!-- Custom Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize dashboard
    loadDashboardStats();
    initializeCharts();
    loadRecentActivity();
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        loadDashboardStats();
        loadRecentActivity();
    }, 30000);
});

// Load dashboard statistics
function loadDashboardStats() {
    $.ajax({
        url: 'api/dashboard_api.php',
        method: 'GET',
        data: { action: 'stats' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                
                // Animate counter updates
                animateCounter('#totalPatients', stats.total_patients || 0);
                animateCounter('#todayTests', stats.today_tests || 0);
                animateCounter('#pendingResults', stats.pending_results || 0);
                animateCounter('#totalDoctors', stats.total_doctors || 0);
                
                // Update chart data if available
                if (stats.monthly_data) {
                    updateMonthlyChart(stats.monthly_data);
                }
            }
        },
        error: function() {
            // Show default values on error
            $('#totalPatients').html('--');
            $('#todayTests').html('--');
            $('#pendingResults').html('--');
            $('#totalDoctors').html('--');
        }
    });
}

// Animate counter
function animateCounter(selector, targetValue) {
    const element = $(selector);
    const currentValue = parseInt(element.text()) || 0;
    
    if (currentValue === targetValue) return;
    
    const increment = targetValue > currentValue ? 1 : -1;
    const duration = Math.abs(targetValue - currentValue) * 50;
    
    let current = currentValue;
    const timer = setInterval(function() {
        current += increment;
        element.html(current);
        
        if (current === targetValue) {
            clearInterval(timer);
        }
    }, duration / Math.abs(targetValue - currentValue));
}

// Initialize charts
let monthlyChart;

function initializeCharts() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    monthlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Tests Completed',
                data: [65, 59, 80, 81, 56, 55, 40, 65, 89, 95, 78, 85],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
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
}

// Update monthly chart
function updateMonthlyChart(data) {
    if (monthlyChart && data) {
        monthlyChart.data.datasets[0].data = data;
        monthlyChart.update();
    }
}

// Load recent activity
function loadRecentActivity() {
    $.ajax({
        url: 'api/dashboard_api.php',
        method: 'GET',
        data: { action: 'recent_activity' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                let html = '';
                response.data.forEach(function(activity) {
                    const statusClass = activity.status === 'completed' ? 'success' : 
                                      activity.status === 'pending' ? 'warning' : 'info';
                    
                    html += `
                        <tr>
                            <td><small>${activity.time}</small></td>
                            <td>${activity.description}</td>
                            <td>${activity.user}</td>
                            <td><span class="badge badge-${statusClass}">${activity.status}</span></td>
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
                    <td colspan="4" class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Error loading recent activity
                    </td>
                </tr>
            `);
        }
    });
}

// Refresh functions
function refreshCharts() {
    toastr.info('Refreshing charts...');
    loadDashboardStats();
}

function refreshActivity() {
    toastr.info('Refreshing activity...');
    loadRecentActivity();
}
</script>

<?php include 'includes/footer.php'; ?>
