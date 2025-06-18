<?php
session_start();

// Include database configuration
require_once 'config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
$user_type = $_SESSION['user_type'] ?? 'user';
$page_title = 'Reports';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PathLab Pro | Reports</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
  <!-- Chart.js -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="img/logo.svg" alt="PathLab Pro Logo" height="60" width="60">
  </div>

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="dashboard.php" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- User Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-user"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header"><?php echo htmlspecialchars($username); ?></span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-user-cog mr-2"></i> Profile
          </a>
          <div class="dropdown-divider"></div>
          <a href="logout.php" class="dropdown-item">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </a>
        </div>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="dashboard.php" class="brand-link">
      <img src="img/logo.svg" alt="PathLab Pro Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">PathLab Pro</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($username); ?>&background=random" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo htmlspecialchars($username); ?> <span class="badge badge-success"><?php echo htmlspecialchars(ucfirst($user_type)); ?></span></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="patients.php" class="nav-link">
              <i class="nav-icon fas fa-user-injured"></i>
              <p>
                Patients
                <span class="right badge badge-info">New</span>
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="test-orders.php" class="nav-link">
              <i class="nav-icon fas fa-clipboard-list"></i>
              <p>Test Orders</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="tests.php" class="nav-link">
              <i class="nav-icon fas fa-flask"></i>
              <p>Lab Tests</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="results.php" class="nav-link">
              <i class="nav-icon fas fa-file-medical"></i>
              <p>Test Results</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="doctors.php" class="nav-link">
              <i class="nav-icon fas fa-user-md"></i>
              <p>Doctors</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="reports.php" class="nav-link active">
              <i class="nav-icon fas fa-chart-line"></i>
              <p>Reports</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="equipment.php" class="nav-link">
              <i class="nav-icon fas fa-microscope"></i>
              <p>Equipment</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="settings.php" class="nav-link">
              <i class="nav-icon fas fa-cog"></i>
              <p>Settings</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="logout.php" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>Logout</p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Laboratory Reports</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Reports</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Report Filters -->
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title">Report Filters</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Report Type</label>
                  <select class="form-control" id="reportType">
                    <option value="test-summary">Test Summary</option>
                    <option value="revenue">Revenue Report</option>
                    <option value="patient-demographics">Patient Demographics</option>
                    <option value="doctor-referrals">Doctor Referrals</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Date Range</label>
                  <select class="form-control" id="dateRange">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this-week">This Week</option>
                    <option value="last-week">Last Week</option>
                    <option value="this-month" selected>This Month</option>
                    <option value="last-month">Last Month</option>
                    <option value="this-year">This Year</option>
                    <option value="custom">Custom Range</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4 custom-date-range" style="display: none;">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>From</label>
                      <input type="date" class="form-control" id="dateFrom">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>To</label>
                      <input type="date" class="form-control" id="dateTo">
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>&nbsp;</label>
                  <button type="button" class="btn btn-primary btn-block" id="generateReport">
                    <i class="fas fa-chart-bar mr-1"></i> Generate
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Report Results -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Report Results</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" id="exportPdf">
                <i class="fas fa-file-pdf"></i> Export PDF
              </button>
              <button type="button" class="btn btn-tool" id="exportCsv">
                <i class="fas fa-file-csv"></i> Export CSV
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-8">
                <canvas id="reportChart" style="min-height: 250px; height: 250px; max-height: 350px; max-width: 100%;"></canvas>
              </div>
              <div class="col-md-4">
                <div class="info-box mb-3 bg-info">
                  <span class="info-box-icon"><i class="fas fa-flask"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Total Tests</span>
                    <span class="info-box-number">328</span>
                  </div>
                </div>
                <div class="info-box mb-3 bg-success">
                  <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Revenue</span>
                    <span class="info-box-number">$12,458</span>
                  </div>
                </div>
                <div class="info-box mb-3 bg-warning">
                  <span class="info-box-icon"><i class="fas fa-user-injured"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">New Patients</span>
                    <span class="info-box-number">42</span>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="table-responsive mt-4">
              <table id="reportTable" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Test Name</th>
                    <th>Count</th>
                    <th>Revenue</th>
                    <th>Average TAT</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Complete Blood Count</td>
                    <td>86</td>
                    <td>$2,150</td>
                    <td>12 hours</td>
                  </tr>
                  <tr>
                    <td>Glucose Test</td>
                    <td>65</td>
                    <td>$975</td>
                    <td>4 hours</td>
                  </tr>
                  <tr>
                    <td>Liver Function Test</td>
                    <td>42</td>
                    <td>$1,890</td>
                    <td>24 hours</td>
                  </tr>
                  <tr>
                    <td>HbA1c</td>
                    <td>38</td>
                    <td>$1,140</td>
                    <td>12 hours</td>
                  </tr>
                  <tr>
                    <td>Lipid Profile</td>
                    <td>52</td>
                    <td>$2,080</td>
                    <td>12 hours</td>
                  </tr>
                  <tr>
                    <td>Thyroid Function Test</td>
                    <td>45</td>
                    <td>$4,223</td>
                    <td>24 hours</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <th>Total</th>
                    <th>328</th>
                    <th>$12,458</th>
                    <th>14.7 hours</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <strong>Copyright &copy; 2025 <a href="#">PathLab Pro</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.0
    </div>
  </footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
  // Initialize DataTable
  $('#reportTable').DataTable({
    "paging": true,
    "lengthChange": false,
    "searching": true,
    "ordering": true,
    "info": true,
    "autoWidth": false,
    "responsive": true
  });
  
  // Show custom date range when selected
  $('#dateRange').change(function() {
    if ($(this).val() === 'custom') {
      $('.custom-date-range').show();
    } else {
      $('.custom-date-range').hide();
    }
  });
  
  // Initialize chart
  const ctx = document.getElementById('reportChart').getContext('2d');
  const reportChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Complete Blood Count', 'Glucose Test', 'Liver Function Test', 'HbA1c', 'Lipid Profile', 'Thyroid Function Test'],
      datasets: [{
        label: 'Test Count',
        data: [86, 65, 42, 38, 52, 45],
        backgroundColor: 'rgba(60, 141, 188, 0.8)',
        borderColor: 'rgba(60, 141, 188, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
  
  // Handle report generation
  $('#generateReport').on('click', function() {
    const reportType = $('#reportType').val();
    const dateRange = $('#dateRange').val();
    let dateFrom = $('#dateFrom').val();
    let dateTo = $('#dateTo').val();
    
    console.log(`Generating ${reportType} report for ${dateRange}`);
    
    // This is just a demo - in a real app, we would fetch data from the server
    
    // Update chart based on report type
    if (reportType === 'revenue') {
      reportChart.data.datasets[0].label = 'Revenue ($)';
      reportChart.data.datasets[0].data = [2150, 975, 1890, 1140, 2080, 4223];
      reportChart.data.datasets[0].backgroundColor = 'rgba(40, 167, 69, 0.8)';
      reportChart.data.datasets[0].borderColor = 'rgba(40, 167, 69, 1)';
    } else if (reportType === 'test-summary') {
      reportChart.data.datasets[0].label = 'Test Count';
      reportChart.data.datasets[0].data = [86, 65, 42, 38, 52, 45];
      reportChart.data.datasets[0].backgroundColor = 'rgba(60, 141, 188, 0.8)';
      reportChart.data.datasets[0].borderColor = 'rgba(60, 141, 188, 1)';
    } else if (reportType === 'patient-demographics') {
      reportChart.config.type = 'pie';
      reportChart.data.labels = ['Male', 'Female', 'Other'];
      reportChart.data.datasets[0].label = 'Patients';
      reportChart.data.datasets[0].data = [126, 192, 10];
      reportChart.data.datasets[0].backgroundColor = [
        'rgba(60, 141, 188, 0.8)',
        'rgba(210, 214, 222, 0.8)',
        'rgba(255, 193, 7, 0.8)'
      ];
    } else {
      reportChart.config.type = 'bar';
      reportChart.data.labels = ['Dr. Smith', 'Dr. Johnson', 'Dr. Williams', 'Dr. Brown', 'Dr. Jones', 'Others'];
      reportChart.data.datasets[0].label = 'Referrals';
      reportChart.data.datasets[0].data = [32, 24, 18, 15, 12, 27];
      reportChart.data.datasets[0].backgroundColor = 'rgba(156, 39, 176, 0.8)';
      reportChart.data.datasets[0].borderColor = 'rgba(156, 39, 176, 1)';
    }
    
    reportChart.update();
    
    // Show a notification
    alert(`${reportType.replace('-', ' ')} report generated successfully for ${dateRange}`);
  });
  
  // Handle export buttons
  $('#exportPdf').on('click', function() {
    alert('PDF export functionality would be implemented here');
  });
  
  $('#exportCsv').on('click', function() {
    alert('CSV export functionality would be implemented here');
  });
});
</script>
</body>
</html>
