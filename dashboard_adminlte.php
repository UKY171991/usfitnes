<?php
// Set page title
$page_title = 'Dashboard - PathLab Pro';

// Include AdminLTE header
include 'includes/adminlte_header.php';
// Include AdminLTE sidebar
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
            <i class="fas fa-tachometer-alt mr-2 text-primary"></i>Laboratory Dashboard
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
      
      <!-- Statistics Cards Row -->
      <div class="row">
        <!-- Total Patients -->
        <div class="col-lg-3 col-6">
          <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-user-injured"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Patients</span>
              <span class="info-box-number" id="total-patients">1,250</span>
              <div class="progress">
                <div class="progress-bar bg-info" style="width: 70%"></div>
              </div>
              <span class="progress-description">
                <i class="fas fa-arrow-up text-success"></i> 12% increase from last month
              </span>
            </div>
          </div>
        </div>
        
        <!-- Pending Tests -->
        <div class="col-lg-3 col-6">
          <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-flask"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Pending Tests</span>
              <span class="info-box-number" id="pending-tests">45</span>
              <div class="progress">
                <div class="progress-bar bg-warning" style="width: 45%"></div>
              </div>
              <span class="progress-description">
                <i class="fas fa-clock text-warning"></i> Requires attention
              </span>
            </div>
          </div>
        </div>
        
        <!-- Completed Tests -->
        <div class="col-lg-3 col-6">
          <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Completed Tests</span>
              <span class="info-box-number" id="completed-tests">3,842</span>
              <div class="progress">
                <div class="progress-bar bg-success" style="width: 85%"></div>
              </div>
              <span class="progress-description">
                <i class="fas fa-arrow-up text-success"></i> 8% increase from last week
              </span>
            </div>
          </div>
        </div>
        
        <!-- Monthly Revenue -->
        <div class="col-lg-3 col-6">
          <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-dollar-sign"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Monthly Revenue</span>
              <span class="info-box-number" id="monthly-revenue">$25,680</span>
              <div class="progress">
                <div class="progress-bar bg-danger" style="width: 90%"></div>
              </div>
              <span class="progress-description">
                <i class="fas fa-arrow-up text-success"></i> 15% increase from last month
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
                Monthly Revenue Trend
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
                <canvas id="revenueChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Test Types Distribution -->
        <div class="col-md-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-chart-pie mr-1"></i>
                Test Types Distribution
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <canvas id="testTypesChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Recent Activities Row -->
      <div class="row">
        <!-- Recent Test Orders -->
        <div class="col-md-6">
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
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Patient</th>
                    <th>Test Type</th>
                    <th>Status</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>John Doe</td>
                    <td>Blood Test</td>
                    <td><span class="badge badge-warning">Pending</span></td>
                    <td><?php echo date('M d, Y'); ?></td>
                  </tr>
                  <tr>
                    <td>Jane Smith</td>
                    <td>Urine Test</td>
                    <td><span class="badge badge-success">Completed</span></td>
                    <td><?php echo date('M d, Y', strtotime('-1 day')); ?></td>
                  </tr>
                  <tr>
                    <td>Mike Johnson</td>
                    <td>X-Ray</td>
                    <td><span class="badge badge-info">In Progress</span></td>
                    <td><?php echo date('M d, Y', strtotime('-2 days')); ?></td>
                  </tr>
                  <tr>
                    <td>Sarah Wilson</td>
                    <td>MRI Scan</td>
                    <td><span class="badge badge-success">Completed</span></td>
                    <td><?php echo date('M d, Y', strtotime('-3 days')); ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="card-footer clearfix">
              <a href="test-orders.php" class="btn btn-sm btn-info float-left">View All Orders</a>
              <a href="test-orders.php?action=create" class="btn btn-sm btn-secondary float-right">Create New Order</a>
            </div>
          </div>
        </div>
        
        <!-- System Alerts -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                System Alerts
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-exclamation-triangle"></i> Equipment Maintenance!</h5>
                Centrifuge #3 requires scheduled maintenance in 2 days.
              </div>
              <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-info"></i> Inventory Alert!</h5>
                Blood collection tubes are running low. Current stock: 45 units.
              </div>
              <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-check"></i> Backup Complete!</h5>
                Daily database backup completed successfully at 2:00 AM.
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Quick Actions Row -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-bolt mr-1"></i>
                Quick Actions
              </h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3 col-6">
                  <a href="patients.php?action=add" class="btn btn-app bg-success">
                    <i class="fas fa-user-plus"></i> Add Patient
                  </a>
                </div>
                <div class="col-md-3 col-6">
                  <a href="test-orders.php?action=create" class="btn btn-app bg-warning">
                    <i class="fas fa-flask"></i> New Test Order
                  </a>
                </div>
                <div class="col-md-3 col-6">
                  <a href="results.php?action=add" class="btn btn-app bg-info">
                    <i class="fas fa-file-medical"></i> Enter Results
                  </a>
                </div>
                <div class="col-md-3 col-6">
                  <a href="reports.php" class="btn btn-app bg-primary">
                    <i class="fas fa-chart-bar"></i> Generate Report
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include 'includes/adminlte_footer.php'; ?>
