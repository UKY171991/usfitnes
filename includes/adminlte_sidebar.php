<?php
// Get user information from session
$user_id = $_SESSION['user_id'] ?? '';
$full_name = $_SESSION['full_name'] ?? $_SESSION['name'] ?? 'System Admin';
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'admin';

// Get user initial for avatar
$user_initial = strtoupper(substr($full_name, 0, 1));

// Current page detection for active states
$current_page = basename($_SERVER['PHP_SELF']);

// Function to check if menu item is active
function isMenuActive($pages) {
    global $current_page;
    if (is_array($pages)) {
        return in_array($current_page, $pages);
    }
    return $current_page === $pages;
}

// Function to get menu item class
function getMenuClass($pages) {
    return isMenuActive($pages) ? 'active' : '';
}

// Function to get tree menu class
function getTreeMenuClass($pages) {
    return isMenuActive($pages) ? 'menu-open' : '';
}
?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="dashboard_new.php" class="brand-link">
    <?php if (function_exists('hasLogo') && hasLogo()): ?>
      <img src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <?php else: ?>
      <i class="fas fa-microscope brand-image" style="font-size: 1.8rem; color: #fff; margin-left: 0.5rem; margin-right: 0.5rem;"></i>
    <?php endif; ?>
    <span class="brand-text font-weight-light">PathLab Pro</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <div class="img-circle elevation-2 d-flex align-items-center justify-content-center bg-info" 
             style="width: 34px; height: 34px; font-size: 1.1rem; font-weight: bold; color: white;">
          <?php echo $user_initial; ?>
        </div>
      </div>
      <div class="info">
        <a href="settings.php" class="d-block text-white"><?php echo htmlspecialchars($full_name); ?></a>
        <span class="badge badge-success"><?php echo ucfirst(htmlspecialchars($user_type)); ?></span>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <!-- Dashboard -->
        <li class="nav-item">
          <a href="dashboard_new.php" class="nav-link <?php echo getMenuClass(['dashboard.php', 'dashboard_new.php']); ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- Patient Management Section -->
        <li class="nav-header">PATIENT MANAGEMENT</li>
        
        <li class="nav-item">
          <a href="patients_ajax.php" class="nav-link <?php echo getMenuClass(['patients.php', 'patients_ajax.php']); ?>">
            <i class="nav-icon fas fa-user-injured"></i>
            <p>
              Patients
              <span class="badge badge-info right" id="patients-count">0</span>
            </p>
          </a>
        </li>

        <!-- Test Management Section -->
        <li class="nav-header">TEST MANAGEMENT</li>
        
        <li class="nav-item">
          <a href="test-orders.php" class="nav-link <?php echo getMenuClass('test-orders.php'); ?>">
            <i class="nav-icon fas fa-flask"></i>
            <p>
              Test Orders
              <span class="badge badge-warning right" id="pending-orders">0</span>
            </p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="results.php" class="nav-link <?php echo getMenuClass('results.php'); ?>">
            <i class="nav-icon fas fa-chart-line"></i>
            <p>Test Results</p>
          </a>
        </li>

        <!-- Medical Staff Section -->
        <li class="nav-header">MEDICAL STAFF</li>
        
        <li class="nav-item">
          <a href="doctors_ajax.php" class="nav-link <?php echo getMenuClass(['doctors.php', 'doctors_ajax.php']); ?>">
            <i class="nav-icon fas fa-user-md"></i>
            <p>
              Doctors
              <span class="badge badge-primary right" id="doctors-count">0</span>
            </p>
          </a>
        </li>

        <!-- Equipment Section -->
        <li class="nav-header">EQUIPMENT</li>
        
        <li class="nav-item">
          <a href="equipment_ajax.php" class="nav-link <?php echo getMenuClass(['equipment.php', 'equipment_ajax.php']); ?>">
            <i class="nav-icon fas fa-tools"></i>
            <p>
              Equipment
              <span class="badge badge-secondary right" id="equipment-count">0</span>
            </p>
          </a>
        </li>

        <!-- Reports Section -->
        <li class="nav-header">REPORTS & ANALYTICS</li>
        
        <li class="nav-item has-treeview <?php echo getTreeMenuClass(['reports.php', 'analytics.php', 'statistics.php']); ?>">
          <a href="#" class="nav-link <?php echo getMenuClass(['reports.php', 'analytics.php', 'statistics.php']); ?>">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>
              Reports
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="reports.php" class="nav-link <?php echo getMenuClass('reports.php'); ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Lab Reports</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="analytics.php" class="nav-link <?php echo getMenuClass('analytics.php'); ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Analytics</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="statistics.php" class="nav-link <?php echo getMenuClass('statistics.php'); ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Statistics</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Administration Section -->
        <li class="nav-header">ADMINISTRATION</li>
        
        <li class="nav-item has-treeview <?php echo getTreeMenuClass(['settings.php', 'users.php', 'backup.php', 'system.php']); ?>">
          <a href="#" class="nav-link <?php echo getMenuClass(['settings.php', 'users.php', 'backup.php', 'system.php']); ?>">
            <i class="nav-icon fas fa-cogs"></i>
            <p>
              System
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="settings.php" class="nav-link <?php echo getMenuClass('settings.php'); ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Settings</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="users.php" class="nav-link <?php echo getMenuClass('users.php'); ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>User Management</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="backup.php" class="nav-link <?php echo getMenuClass('backup.php'); ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Backup & Restore</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="system.php" class="nav-link <?php echo getMenuClass('system.php'); ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>System Info</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Quick Actions -->
        <li class="nav-header">QUICK ACTIONS</li>
        
        <li class="nav-item">
          <a href="#" class="nav-link" onclick="addNewPatient()">
            <i class="nav-icon fas fa-plus text-success"></i>
            <p>Add New Patient</p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="#" class="nav-link" onclick="createTestOrder()">
            <i class="nav-icon fas fa-plus text-warning"></i>
            <p>Create Test Order</p>
          </a>
        </li>

        <!-- Logout -->
        <li class="nav-item mt-3">
          <a href="logout.php" class="nav-link text-danger">
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

<script>
// Update sidebar counters
document.addEventListener('DOMContentLoaded', function() {
    // You can update these counters via AJAX calls
    updateSidebarCounters();
});

function updateSidebarCounters() {
    // Example: Update patients count
    fetch('api/get_counts.php')
        .then(response => response.json())
        .then(data => {
            if (data.patients) {
                document.getElementById('patients-count').textContent = data.patients;
            }
            if (data.pending_orders) {
                document.getElementById('pending-orders').textContent = data.pending_orders;
            }
            if (data.doctors) {
                document.getElementById('doctors-count').textContent = data.doctors;
            }
            if (data.equipment) {
                document.getElementById('equipment-count').textContent = data.equipment;
            }
        })
        .catch(error => console.log('Error updating counters:', error));
}

// Quick action functions
function addNewPatient() {
    window.location.href = 'patients.php?action=add';
}

function createTestOrder() {
    window.location.href = 'test-orders.php?action=create';
}
</script>
