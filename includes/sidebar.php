<?php
// Get user information from session - using variables set in init.php
$user_id = $_SESSION['user_id'] ?? '';
$full_name = $_SESSION['full_name'] ?? $_SESSION['name'] ?? 'System Admin';
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'admin';

// Get user initial for avatar
$user_initial = strtoupper(substr($full_name, 0, 1));

// Current page detection for active states
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="dashboard.php" class="brand-link">
    <?php if (hasLogo()): ?>
      <img src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <?php else: ?>
      <i class="fas fa-microscope brand-image" style="font-size: 1.8rem; color: #fff; margin-left: 0.5rem;"></i>
    <?php endif; ?>
    <span class="brand-text font-weight-light">PathLab Pro</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <div class="img-circle elevation-2 d-flex align-items-center justify-content-center bg-info" 
             style="width: 34px; height: 34px; font-size: 1.1rem; font-weight: bold; color: white;">
          <?php echo $user_initial; ?>
        </div>
      </div>
      <div class="info">
        <a href="#" class="d-block text-white"><?php echo htmlspecialchars($full_name); ?></a>
        <span class="badge badge-success"><?php echo ucfirst(htmlspecialchars($user_type)); ?></span>
      </div>
    </div>

    <!-- SidebarSearch Form -->
    <div class="form-inline">
      <div class="input-group" data-widget="sidebar-search">
        <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-sidebar">
            <i class="fas fa-search fa-fw"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <!-- Dashboard -->
        <li class="nav-item">
          <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- Patient Management Section -->
        <li class="nav-header">PATIENT MANAGEMENT</li>
        <li class="nav-item">
          <a href="patients.php" class="nav-link <?php echo ($current_page == 'patients.php') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-user-injured"></i>
            <p>
              Patients
              <span class="badge badge-info right" id="patients-count">0</span>
            </p>
          </a>
        </li>

        <!-- Test Management Section -->
        <li class="nav-header">TEST MANAGEMENT</li>
        <li class="nav-item has-treeview <?php echo (in_array($current_page, ['test-orders.php', 'tests.php', 'results.php'])) ? 'menu-open' : ''; ?>">
          <a href="#" class="nav-link <?php echo (in_array($current_page, ['test-orders.php', 'tests.php', 'results.php'])) ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-flask"></i>
            <p>
              Laboratory Tests
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="test-orders.php" class="nav-link <?php echo ($current_page == 'test-orders.php') ? 'active' : ''; ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>
                  Test Orders
                  <span class="badge badge-warning right" id="pending-orders">0</span>
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="tests.php" class="nav-link <?php echo ($current_page == 'tests.php') ? 'active' : ''; ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Available Tests</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="results.php" class="nav-link <?php echo ($current_page == 'results.php') ? 'active' : ''; ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Test Results</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Staff Management Section -->
        <li class="nav-header">STAFF MANAGEMENT</li>
        <li class="nav-item">
          <a href="doctors.php" class="nav-link <?php echo ($current_page == 'doctors.php') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-user-md"></i>
            <p>Doctors</p>
          </a>
        </li>

        <!-- Laboratory Equipment -->
        <li class="nav-header">LABORATORY</li>
        <li class="nav-item">
          <a href="equipment.php" class="nav-link <?php echo ($current_page == 'equipment.php') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-microscope"></i>
            <p>Equipment</p>
          </a>
        </li>

        <!-- Reports Section -->
        <li class="nav-header">REPORTS & ANALYTICS</li>
        <li class="nav-item has-treeview <?php echo (in_array($current_page, ['reports.php', 'view_all_data.php'])) ? 'menu-open' : ''; ?>">
          <a href="#" class="nav-link <?php echo (in_array($current_page, ['reports.php', 'view_all_data.php'])) ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-chart-line"></i>
            <p>
              Reports
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="reports.php" class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Analytics</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="view_all_data.php" class="nav-link <?php echo ($current_page == 'view_all_data.php') ? 'active' : ''; ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Data Overview</p>
              </a>
            </li>
          </ul>
        </li>

        <?php if($user_type == 'admin'): ?>
        <!-- Administration Section -->
        <li class="nav-header">ADMINISTRATION</li>
        <li class="nav-item has-treeview <?php echo (in_array($current_page, ['users.php', 'system_status.php', 'settings.php'])) ? 'menu-open' : ''; ?>">
          <a href="#" class="nav-link <?php echo (in_array($current_page, ['users.php', 'system_status.php', 'settings.php'])) ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-cogs"></i>
            <p>
              System Admin
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="users.php" class="nav-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>User Management</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="system_status.php" class="nav-link <?php echo ($current_page == 'system_status.php') ? 'active' : ''; ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>
                  System Status
                  <span class="badge badge-success right">
                    <i class="fas fa-circle" style="font-size: 0.6rem;"></i>
                  </span>
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="settings.php" class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Settings</p>
              </a>
            </li>
          </ul>
        </li>
        <?php else: ?>
        <!-- Settings for non-admin users -->
        <li class="nav-header">SETTINGS</li>
        <li class="nav-item">
          <a href="settings.php" class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-user-cog"></i>
            <p>Profile Settings</p>
          </a>
        </li>
        <?php endif; ?>

        <!-- Quick Access Section -->
        <li class="nav-header">QUICK ACCESS</li>
        <li class="nav-item">
          <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-home"></i>
            <p>Public Homepage</p>
          </a>
        </li>

        <!-- Divider -->
        <li class="nav-item mt-3">
          <hr class="sidebar-divider">
        </li>

        <!-- Logout -->
        <li class="nav-item">
          <a href="logout.php" class="nav-link text-danger" onclick="return confirm('Are you sure you want to logout?')">
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

<style>
/* AdminLTE 3 Custom Sidebar Styles */
.sidebar-divider {
  border-color: rgba(255,255,255,0.1);
  margin: 0.5rem 1rem;
}

.nav-sidebar .nav-link.text-danger:hover {
  background-color: rgba(220, 53, 69, 0.1);
  color: #dc3545 !important;
}

.sidebar-search-results .list-group-item {
  border-color: rgba(255,255,255,0.1);
  background-color: rgba(255,255,255,0.1);
}

.nav-header {
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.1rem;
  color: rgba(255,255,255,0.6);
  margin-top: 1rem;
}

.nav-header:first-child {
  margin-top: 0;
}

/* Enhanced user panel */
.user-panel .info a {
  font-weight: 500;
  font-size: 0.9rem;
}

/* Treeview improvements */
.nav-treeview .nav-link {
  padding-left: 2.8rem;
}

.nav-treeview .nav-link .nav-icon {
  margin-left: -0.6rem;
  margin-right: 0.5rem;
}

/* Badge improvements */
.nav-link .badge {
  font-size: 0.75rem;
}

/* Search form styling */
.form-control-sidebar {
  background-color: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.3);
  color: #fff;
}

.form-control-sidebar:focus {
  background-color: rgba(255,255,255,0.2);
  border-color: rgba(255,255,255,0.5);
  color: #fff;
  box-shadow: none;
}

.form-control-sidebar::placeholder {
  color: rgba(255,255,255,0.6);
}

.btn-sidebar {
  background-color: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.3);
  color: rgba(255,255,255,0.8);
}

.btn-sidebar:hover {
  background-color: rgba(255,255,255,0.2);
  color: #fff;
}
</style>