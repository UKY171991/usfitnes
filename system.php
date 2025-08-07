<?php
require_once 'config.php';
require_once 'includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'System Information';
$pageIcon = 'fas fa-server';
$breadcrumbs = ['Administration', 'System Info'];

// Get system information
$systemInfo = [
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size')
];

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
        <!-- Server Information -->
        <div class="col-md-6">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-server mr-2"></i>Server Information
              </h3>
            </div>
            <div class="card-body">
              <table class="table table-sm">
                <tbody>
                  <tr>
                    <td><strong>PHP Version:</strong></td>
                    <td><?php echo htmlspecialchars($systemInfo['php_version']); ?></td>
                  </tr>
                  <tr>
                    <td><strong>Server Software:</strong></td>
                    <td><?php echo htmlspecialchars($systemInfo['server_software']); ?></td>
                  </tr>
                  <tr>
                    <td><strong>Server Name:</strong></td>
                    <td><?php echo htmlspecialchars($systemInfo['server_name']); ?></td>
                  </tr>
                  <tr>
                    <td><strong>Document Root:</strong></td>
                    <td><small><?php echo htmlspecialchars($systemInfo['document_root']); ?></small></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- PHP Configuration -->
        <div class="col-md-6">
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fab fa-php mr-2"></i>PHP Configuration
              </h3>
            </div>
            <div class="card-body">
              <table class="table table-sm">
                <tbody>
                  <tr>
                    <td><strong>Memory Limit:</strong></td>
                    <td><?php echo htmlspecialchars($systemInfo['memory_limit']); ?></td>
                  </tr>
                  <tr>
                    <td><strong>Max Execution Time:</strong></td>
                    <td><?php echo htmlspecialchars($systemInfo['max_execution_time']); ?>s</td>
                  </tr>
                  <tr>
                    <td><strong>Upload Max Size:</strong></td>
                    <td><?php echo htmlspecialchars($systemInfo['upload_max_filesize']); ?></td>
                  </tr>
                  <tr>
                    <td><strong>POST Max Size:</strong></td>
                    <td><?php echo htmlspecialchars($systemInfo['post_max_size']); ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Database Information -->
      <div class="row">
        <div class="col-md-6">
          <div class="card card-success">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-database mr-2"></i>Database Information
              </h3>
            </div>
            <div class="card-body">
              <?php
              try {
                $dbInfo = $conn->query("SELECT VERSION() as version")->fetch();
                $tableCount = $conn->query("SHOW TABLES")->rowCount();
                echo '<table class="table table-sm">';
                echo '<tr><td><strong>Database Version:</strong></td><td>' . htmlspecialchars($dbInfo['version']) . '</td></tr>';
                echo '<tr><td><strong>Total Tables:</strong></td><td>' . $tableCount . '</td></tr>';
                echo '<tr><td><strong>Connection Status:</strong></td><td><span class="badge badge-success">Connected</span></td></tr>';
                echo '</table>';
              } catch (Exception $e) {
                echo '<div class="alert alert-danger">Database connection error: ' . htmlspecialchars($e->getMessage()) . '</div>';
              }
              ?>
            </div>
          </div>
        </div>

        <!-- System Status -->
        <div class="col-md-6">
          <div class="card card-warning">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-heartbeat mr-2"></i>System Status
              </h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-6">
                  <div class="description-block border-right">
                    <span class="description-percentage text-success">
                      <i class="fas fa-check-circle"></i>
                    </span>
                    <h5 class="description-header">System</h5>
                    <span class="description-text">Running</span>
                  </div>
                </div>
                <div class="col-6">
                  <div class="description-block">
                    <span class="description-percentage text-success">
                      <i class="fas fa-database"></i>
                    </span>
                    <h5 class="description-header">Database</h5>
                    <span class="description-text">Connected</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Installed Extensions -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-puzzle-piece mr-2"></i>PHP Extensions
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-sm btn-primary" onclick="toggleExtensions()">
                  <i class="fas fa-eye"></i> Show/Hide
                </button>
              </div>
            </div>
            <div class="card-body" id="extensions-list" style="display: none;">
              <div class="row">
                <?php
                $extensions = get_loaded_extensions();
                sort($extensions);
                $chunks = array_chunk($extensions, ceil(count($extensions) / 3));
                foreach ($chunks as $chunk) {
                    echo '<div class="col-md-4"><ul class="list-unstyled">';
                    foreach ($chunk as $ext) {
                        echo '<li><i class="fas fa-check text-success mr-2"></i>' . htmlspecialchars($ext) . '</li>';
                    }
                    echo '</ul></div>';
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- System Actions -->
      <div class="row">
        <div class="col-12">
          <div class="card card-secondary">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-tools mr-2"></i>System Actions
              </h3>
            </div>
            <div class="card-body">
              <button type="button" class="btn btn-info mr-2" onclick="clearCache()">
                <i class="fas fa-broom mr-2"></i>Clear Cache
              </button>
              <button type="button" class="btn btn-warning mr-2" onclick="checkSystem()">
                <i class="fas fa-stethoscope mr-2"></i>System Check
              </button>
              <button type="button" class="btn btn-secondary" onclick="exportSystemInfo()">
                <i class="fas fa-download mr-2"></i>Export Info
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
function toggleExtensions() {
    const extensionsList = document.getElementById('extensions-list');
    extensionsList.style.display = extensionsList.style.display === 'none' ? 'block' : 'none';
}

function clearCache() {
    if (confirm('Clear system cache?')) {
        alert('Cache clearing functionality would be implemented here.');
    }
}

function checkSystem() {
    alert('Running system check...');
    // System check functionality would be implemented here
}

function exportSystemInfo() {
    alert('System information export would be implemented here.');
}
</script>

<?php include 'includes/adminlte_template_footer.php'; ?>
