<?php
require_once 'config.php';
require_once 'includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Statistics';
$pageIcon = 'fas fa-chart-bar';
$breadcrumbs = ['Reports', 'Statistics'];

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
            <i class="<?php echo $pageIcon; ?> mr-2 text-primary"></i><?php echo $pageTitle; ?>
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <?php foreach($breadcrumbs as $index => $crumb): ?>
              <?php if($index === count($breadcrumbs) - 1): ?>
                <li class="breadcrumb-item active"><?php echo $crumb; ?></li>
              <?php else: ?>
                <li class="breadcrumb-item"><?php echo $crumb; ?></li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-chart-bar mr-2"></i>Laboratory Statistics
              </h3>
            </div>
            <div class="card-body">
              <div class="row">
                <!-- Overall Statistics -->
                <div class="col-md-6">
                  <div class="card card-outline card-primary">
                    <div class="card-header">
                      <h3 class="card-title">Overall Statistics</h3>
                    </div>
                    <div class="card-body">
                      <table class="table table-striped">
                        <tbody>
                          <tr>
                            <td><i class="fas fa-users text-info"></i> Total Patients</td>
                            <td><strong>0</strong></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-flask text-success"></i> Total Tests</td>
                            <td><strong>0</strong></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-user-md text-primary"></i> Total Doctors</td>
                            <td><strong>0</strong></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-tools text-warning"></i> Total Equipment</td>
                            <td><strong>0</strong></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-clock text-danger"></i> Pending Results</td>
                            <td><strong>0</strong></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                
                <!-- Time-based Statistics -->
                <div class="col-md-6">
                  <div class="card card-outline card-success">
                    <div class="card-header">
                      <h3 class="card-title">Time-based Statistics</h3>
                    </div>
                    <div class="card-body">
                      <table class="table table-striped">
                        <tbody>
                          <tr>
                            <td><i class="fas fa-calendar-day text-info"></i> Today's Tests</td>
                            <td><strong>0</strong></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-calendar-week text-success"></i> This Week</td>
                            <td><strong>0</strong></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-calendar-alt text-primary"></i> This Month</td>
                            <td><strong>0</strong></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-calendar text-warning"></i> This Year</td>
                            <td><strong>0</strong></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-chart-line text-danger"></i> Growth Rate</td>
                            <td><strong>0%</strong></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Detailed Statistics -->
              <div class="row mt-4">
                <div class="col-12">
                  <div class="card card-outline card-info">
                    <div class="card-header">
                      <h3 class="card-title">Detailed Statistics</h3>
                      <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" onclick="refreshStats()">
                          <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                      </div>
                    </div>
                    <div class="card-body">
                      <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                          <thead>
                            <tr>
                              <th>Metric</th>
                              <th>Today</th>
                              <th>This Week</th>
                              <th>This Month</th>
                              <th>Total</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td><i class="fas fa-user-plus text-success"></i> New Patients</td>
                              <td>0</td>
                              <td>0</td>
                              <td>0</td>
                              <td>0</td>
                            </tr>
                            <tr>
                              <td><i class="fas fa-flask text-info"></i> Tests Ordered</td>
                              <td>0</td>
                              <td>0</td>
                              <td>0</td>
                              <td>0</td>
                            </tr>
                            <tr>
                              <td><i class="fas fa-check-circle text-success"></i> Tests Completed</td>
                              <td>0</td>
                              <td>0</td>
                              <td>0</td>
                              <td>0</td>
                            </tr>
                            <tr>
                              <td><i class="fas fa-clock text-warning"></i> Tests Pending</td>
                              <td>0</td>
                              <td>0</td>
                              <td>0</td>
                              <td>0</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
function refreshStats() {
    // Placeholder for refreshing statistics
    console.log('Refreshing statistics...');
    // You can add AJAX call here to refresh data
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Statistics page loaded');
});
</script>

<?php include 'includes/adminlte_template_footer.php'; ?>
