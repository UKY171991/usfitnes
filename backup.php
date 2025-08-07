<?php
require_once 'config.php';
require_once 'includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Backup & Restore';
$pageIcon = 'fas fa-database';
$breadcrumbs = ['Administration', 'Backup & Restore'];

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
        <!-- Backup Section -->
        <div class="col-md-6">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-download mr-2"></i>Database Backup
              </h3>
            </div>
            <div class="card-body">
              <p class="card-text">Create a backup of your laboratory database for safekeeping.</p>
              <div class="form-group">
                <label>Backup Options</label>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="includePatients" checked>
                  <label class="form-check-label" for="includePatients">Include Patients</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="includeTests" checked>
                  <label class="form-check-label" for="includeTests">Include Test Orders</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="includeResults" checked>
                  <label class="form-check-label" for="includeResults">Include Test Results</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="includeDoctors" checked>
                  <label class="form-check-label" for="includeDoctors">Include Doctors</label>
                </div>
              </div>
              <button type="button" class="btn btn-primary btn-block" onclick="createBackup()">
                <i class="fas fa-download mr-2"></i>Create Backup
              </button>
            </div>
          </div>
        </div>

        <!-- Restore Section -->
        <div class="col-md-6">
          <div class="card card-warning">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-upload mr-2"></i>Database Restore
              </h3>
            </div>
            <div class="card-body">
              <p class="card-text text-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <strong>Warning:</strong> Restoring will replace current data.
              </p>
              <div class="form-group">
                <label for="backupFile">Select Backup File</label>
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="backupFile" accept=".sql,.zip">
                  <label class="custom-file-label" for="backupFile">Choose backup file...</label>
                </div>
              </div>
              <button type="button" class="btn btn-warning btn-block" onclick="restoreBackup()" disabled>
                <i class="fas fa-upload mr-2"></i>Restore Database
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Backups -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-history mr-2"></i>Recent Backups
              </h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Size</th>
                      <th>Type</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td colspan="5" class="text-center text-muted">No backups found</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Automatic Backup Settings -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-cog mr-2"></i>Automatic Backup Settings
              </h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Enable Automatic Backup</label>
                    <div class="custom-control custom-switch">
                      <input type="checkbox" class="custom-control-input" id="autoBackup">
                      <label class="custom-control-label" for="autoBackup">Enable</label>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="backupFrequency">Backup Frequency</label>
                    <select class="form-control" id="backupFrequency">
                      <option value="daily">Daily</option>
                      <option value="weekly">Weekly</option>
                      <option value="monthly">Monthly</option>
                    </select>
                  </div>
                </div>
              </div>
              <button type="button" class="btn btn-info" onclick="saveBackupSettings()">
                <i class="fas fa-save mr-2"></i>Save Settings
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
function createBackup() {
    if (confirm('Create a backup of the database now?')) {
        alert('Backup functionality would be implemented here.');
    }
}

function restoreBackup() {
    if (confirm('This will replace all current data. Are you sure?')) {
        alert('Restore functionality would be implemented here.');
    }
}

function saveBackupSettings() {
    alert('Backup settings saved successfully!');
}

// Handle file input
document.getElementById('backupFile').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Choose backup file...';
    const label = e.target.nextElementSibling;
    label.textContent = fileName;
    
    const restoreBtn = document.querySelector('button[onclick="restoreBackup()"]');
    restoreBtn.disabled = !e.target.files[0];
});
</script>

<?php include 'includes/adminlte_template_footer.php'; ?>
