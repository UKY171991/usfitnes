<?php
// Set page title
$page_title = 'Dashboard';

// Include header
include 'includes/header.php';
// Include sidebar with user info
include 'includes/sidebar.php';
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Laboratory Dashboard</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Lab Dashboard</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3 id="totalPatients">0</h3>
                <p>Total Patients</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-injured"></i>
              </div>
              <a href="patients.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3 id="todayTests">0</h3>
                <p>Today's Tests</p>
              </div>              <div class="icon">
                <i class="fas fa-flask"></i>
              </div>
              <a href="test-orders.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">                <h3 id="pendingResults">0</h3>
                <p>Pending Results</p>
              </div>
              <div class="icon">
                <i class="fas fa-file-medical"></i>
              </div>
              <a href="results.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3 id="totalDoctors">0</h3>
                <p>Total Doctors</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-md"></i>
              </div>
              <a href="doctors.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->

        <!-- Main row -->
        <div class="row">
          <!-- Left col -->
          <section class="col-lg-7 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-chart-pie mr-1"></i>
                  Test Statistics
                </h3>
                <div class="card-tools">
                  <ul class="nav nav-pills ml-auto">
                    <li class="nav-item">
                      <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Monthly</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#sales-chart" data-toggle="tab">Weekly</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="card-body">
                <div class="tab-content p-0">
                  <div class="chart tab-pane active" id="revenue-chart" style="position: relative; height: 300px;">
                    <canvas id="revenue-chart-canvas" height="300" style="height: 300px;"></canvas>
                  </div>
                  <div class="chart tab-pane" id="sales-chart" style="position: relative; height: 300px;">
                    <canvas id="sales-chart-canvas" height="300" style="height: 300px;"></canvas>
                  </div>
                </div>
              </div>
            </div>
            <!-- /.card -->
          </section>
          <!-- /.Left col -->

          <!-- right col (We are only adding the ID to make the widgets sortable)-->
          <section class="col-lg-5 connectedSortable">
            <!-- Calendar -->
            <div class="card bg-gradient-success">
              <div class="card-header border-0">
                <h3 class="card-title">
                  <i class="far fa-calendar-alt"></i>
                  Calendar
                </h3>
                <div class="card-tools">
                  <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" data-offset="-52">
                      <i class="fas fa-bars"></i>
                    </button>
                    <div class="dropdown-menu" role="menu">
                      <a href="#" class="dropdown-item">Add new event</a>
                      <a href="#" class="dropdown-item">Clear events</a>
                      <div class="dropdown-divider"></div>
                      <a href="#" class="dropdown-item">View calendar</a>
                    </div>
                  </div>
                  <button type="button" class="btn btn-success btn-sm" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-success btn-sm" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body pt-0">
                <div id="calendar" style="width: 100%"></div>
              </div>
            </div>
            <!-- /.card -->
          </section>
          <!-- right col -->
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

<?php
// Additional scripts specific to the dashboard page
$additional_scripts = <<<EOT
<script>
$(document).ready(function() {
    // Load dashboard statistics via AJAX
    loadDashboardStats();
    // Set up refresh interval (every 30 seconds)
    setInterval(loadDashboardStats, 30000);
});

function loadDashboardStats() {
    // Show loading state
    $('#totalPatients, #todayTests, #pendingResults, #totalDoctors').html('<i class="fas fa-spinner fa-spin"></i>');
    
    $.ajax({
        url: 'api/dashboard_api.php',
        method: 'GET',
        data: { action: 'stats' },
        dataType: 'json',
        timeout: 10000, // 10 second timeout
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                // Update stat boxes with numbers and animation
                $('#totalPatients').html(stats.total_patients || 0);
                $('#todayTests').html(stats.today_tests || 0);
                $('#pendingResults').html(stats.pending_results || 0);
                $('#totalDoctors').html(stats.total_doctors || 0);
                
                // Update additional stats if they exist
                if (stats.monthly_revenue !== undefined) {
                    updateRevenueDisplay(stats.monthly_revenue);
                }
            } else {
                console.error('Dashboard API error:', response.message);
                $('#totalPatients, #todayTests, #pendingResults, #totalDoctors').html('--');
            }
        },
        error: function(xhr, status, error) {
            console.error('Dashboard API request failed:', status, error);
            // Show default values instead of error
            $('#totalPatients').html('0');
            $('#todayTests').html('0');
            $('#pendingResults').html('0');
            $('#totalDoctors').html('0');
        }
    });
}
function updateRevenueDisplay(revenue) {
    if ($('#monthlyRevenue').length) {
        $('#monthlyRevenue').html('$' + Number(revenue).toLocaleString());
    }
}
$(function () {
  'use strict';
    // Initialize charts after DOM is ready
  initializeCharts();
});

function initializeCharts() {
  // Check if Chart.js is loaded
  if (typeof Chart === 'undefined') {
    console.error('Chart.js not loaded');
    return;
  }
  
  var ticksStyle = {
    fontColor: '#495057',
    fontStyle: 'bold'
  };
  var mode = 'index';
  var intersect = true;
  
  // Revenue Chart (Monthly)
  var revenueCanvas = document.getElementById('revenue-chart-canvas');
  if (revenueCanvas) {
    try {
      new Chart(revenueCanvas, {
        type: 'line',
        data: {
          labels: ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
          datasets: [{
            backgroundColor: 'rgba(40, 167, 69, 0.2)',
            borderColor: '#28a745',
            pointBackgroundColor: '#28a745',
            pointBorderColor: '#28a745',
            pointRadius: 4,
            data: [230, 210, 250, 220, 280, 270, 250, 290, 300, 320, 300, 340]
          }]
        },
        options: {
          maintainAspectRatio: false,
          tooltips: {
            mode: mode,
            intersect: intersect
          },
          hover: {
            mode: mode,
            intersect: intersect
          },
          legend: {
            display: false
          },
          scales: {
            yAxes: [{
              gridLines: {
                display: true,
                lineWidth: '4px',
                color: 'rgba(0, 0, 0, .2)',
                zeroLineColor: 'transparent'
              },
              ticks: $.extend({
                beginAtZero: true,
                callback: function (value) {
                  return value + ' Tests'
                }
              }, ticksStyle)
            }],
            xAxes: [{
              display: true,
              gridLines: {
                display: false
              },
              ticks: ticksStyle
            }]
          }
        }
      });
    } catch (e) {
      console.error('Error creating revenue chart:', e);
    }
  }
  
  // Sales Chart (Weekly)
  var salesCanvas = document.getElementById('sales-chart-canvas');
  if (salesCanvas) {
    try {
      new Chart(salesCanvas, {
        type: 'bar',
        data: {
          labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
          datasets: [{
            backgroundColor: '#007bff',
            borderColor: '#007bff',
            data: [100, 200, 150, 180]
          }]
        },
        options: {
          maintainAspectRatio: false,
          tooltips: {
            mode: mode,
            intersect: intersect
          },
          hover: {
            mode: mode,
            intersect: intersect
          },
          legend: {
            display: false
          },
          scales: {
            yAxes: [{
              gridLines: {
                display: true,
                lineWidth: '4px',
                color: 'rgba(0, 0, 0, .2)',
                zeroLineColor: 'transparent'
              },
              ticks: $.extend({
                beginAtZero: true,
                callback: function (value) {
                  return value + ' Tests'
                }
              }, ticksStyle)
            }],
            xAxes: [{
              display: true,
              gridLines: {
                display: false
              },
              ticks: ticksStyle
            }]
          }
        }
      });
    } catch (e) {
      console.error('Error creating sales chart:', e);
    }
  }
}

// Calendar initialization
$(function () {
  if ($('#calendar').length) {
    $('#calendar').datetimepicker({
      format: 'L',
      inline: true
    });
  }
})
</script>
EOT;
// Include footer with all the necessary scripts
include 'includes/footer.php';
?>
