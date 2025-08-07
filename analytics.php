<?php
require_once 'config.php';
require_once 'includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Analytics';
$pageIcon = 'fas fa-chart-pie';
$breadcrumbs = ['Reports', 'Analytics'];

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
                <i class="fas fa-chart-pie mr-2"></i>Laboratory Analytics
              </h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="card card-outline card-primary">
                    <div class="card-header">
                      <h3 class="card-title">Test Distribution</h3>
                    </div>
                    <div class="card-body">
                      <canvas id="testDistributionChart" style="height: 300px;"></canvas>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card card-outline card-success">
                    <div class="card-header">
                      <h3 class="card-title">Monthly Trends</h3>
                    </div>
                    <div class="card-body">
                      <canvas id="monthlyTrendsChart" style="height: 300px;"></canvas>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="row mt-4">
                <div class="col-12">
                  <div class="card card-outline card-info">
                    <div class="card-header">
                      <h3 class="card-title">Performance Metrics</h3>
                    </div>
                    <div class="card-body">
                      <p class="text-muted">Advanced analytics and performance metrics will be available here.</p>
                      <div class="row">
                        <div class="col-md-3">
                          <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-flask"></i></span>
                            <div class="info-box-content">
                              <span class="info-box-text">Tests/Day</span>
                              <span class="info-box-number">0</span>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                              <span class="info-box-text">Avg. Turnaround</span>
                              <span class="info-box-number">0h</span>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-percentage"></i></span>
                            <div class="info-box-content">
                              <span class="info-box-text">Success Rate</span>
                              <span class="info-box-number">0%</span>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                              <span class="info-box-text">Active Patients</span>
                              <span class="info-box-number">0</span>
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
        </div>
      </div>
    </div>
  </section>
</div>

<script>
// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Placeholder for future analytics implementation
    console.log('Analytics page loaded - charts can be implemented here');
});
</script>

<?php include 'includes/adminlte_template_footer.php'; ?>
