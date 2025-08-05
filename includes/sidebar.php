<?php
// Get user information from session - using variables set in init.php
$user_id = $_SESSION['user_id'] ?? '';
$full_name = $_SESSION['full_name'] ?? $_SESSION['name'] ?? 'User';
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'user';

// Get user initial for avatar
$user_initial = strtoupper(substr($full_name, 0, 1));
?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="dashboard.php" class="brand-link">
    <?php if (hasLogo()): ?>
      <img src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">PathLab Pro</span>
    <?php else: ?>
      <i class="fas fa-microscope brand-image" style="font-size: 2rem; color: #007bff; margin-left: 0.5rem;"></i>
      <span class="brand-text font-weight-bold" style="font-size: 1.2rem; margin-left: 0.5rem;">PathLab Pro</span>
    <?php endif; ?>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="https://via.placeholder.com/160x160/2c5aa0/ffffff?text=<?php echo $user_initial; ?>" class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="settings.php" class="d-block"><?php echo htmlspecialchars($full_name); ?></a>
        <small class="text-muted"><?php echo ucfirst(htmlspecialchars($user_type)); ?></small>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <!-- Dashboard -->
        <li class="nav-item">
          <a href="dashboard.php" class="nav-link <?php echo isActive('dashboard.php'); ?>" data-title="Dashboard">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- Patient Management -->
        <li class="nav-header">PATIENT MANAGEMENT</li>
        <li class="nav-item">
          <a href="patients.php" class="nav-link <?php echo isActive('patients.php'); ?>" data-title="Patients">
            <i class="nav-icon fas fa-user-injured"></i>
            <p>
              Patients
              <span class="badge badge-info right" id="patients-count">0</span>
            </p>
          </a>
        </li>

        <!-- Test Management -->
        <li class="nav-header">TEST MANAGEMENT</li>
        <li class="nav-item">
          <a href="test-orders.php" class="nav-link <?php echo isActive('test-orders.php'); ?>" data-title="Test Orders">
            <i class="nav-icon fas fa-clipboard-list"></i>
            <p>
              Test Orders
              <span class="badge badge-warning right" id="pending-orders">0</span>
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="tests.php" class="nav-link <?php echo isActive('tests.php'); ?>" data-title="Available Tests">
            <i class="nav-icon fas fa-flask"></i>
            <p>Available Tests</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="results.php" class="nav-link <?php echo isActive('results.php'); ?>" data-title="Test Results">
            <i class="nav-icon fas fa-file-medical-alt"></i>
            <p>Test Results</p>
          </a>
        </li>

        <!-- Staff Management -->
        <li class="nav-header">STAFF MANAGEMENT</li>
        <li class="nav-item">
          <a href="doctors.php" class="nav-link <?php echo isActive('doctors.php'); ?>" data-title="Doctors">
            <i class="nav-icon fas fa-user-md"></i>
            <p>Doctors</p>
          </a>
        </li>

        <!-- Equipment -->
        <li class="nav-header">LABORATORY</li>
        <li class="nav-item">
          <a href="equipment.php" class="nav-link <?php echo isActive('equipment.php'); ?>" data-title="Equipment">
            <i class="nav-icon fas fa-microscope"></i>
            <p>Equipment</p>
          </a>
        </li>

        <!-- Reports -->
        <li class="nav-header">REPORTS</li>
        <li class="nav-item">
          <a href="reports.php" class="nav-link <?php echo isActive('reports.php'); ?>" data-title="Reports">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>Reports</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="view_all_data.php" class="nav-link <?php echo isActive('view_all_data.php'); ?>" data-title="View All Data">
            <i class="nav-icon fas fa-database"></i>
            <p>View All Data</p>
          </a>
        </li>

        <?php if($user_type == 'admin'): ?>
        <!-- Admin Only -->
        <li class="nav-header">ADMINISTRATION</li>
        <li class="nav-item">
          <a href="users.php" class="nav-link <?php echo isActive('users.php'); ?>" data-title="User Management">
            <i class="nav-icon fas fa-users-cog"></i>
            <p>User Management</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="system_status.php" class="nav-link <?php echo isActive('system_status.php'); ?>" data-title="System Status">
            <i class="nav-icon fas fa-server"></i>
            <p>
              System Status
              <span class="badge badge-success right">
                <i class="fas fa-circle" style="font-size: 0.6rem;"></i>
              </span>
            </p>
          </a>
        </li>
        <?php endif; ?>

        <!-- Settings -->
        <li class="nav-header">SYSTEM</li>
        <li class="nav-item">
          <a href="settings.php" class="nav-link <?php echo isActive('settings.php'); ?>" data-title="Settings">
            <i class="nav-icon fas fa-cogs"></i>
            <p>Settings</p>
          </a>
        </li>

        <!-- Public Pages -->
        <li class="nav-header">QUICK ACCESS</li>
        <li class="nav-item">
          <a href="index.php" class="nav-link <?php echo isActive('index.php'); ?>" data-title="Homepage">
            <i class="nav-icon fas fa-home"></i>
            <p>Homepage</p>
          </a>
        </li>

        <!-- Logout -->
        <li class="nav-item" style="margin-top: 2rem;">
          <a href="logout.php" class="nav-link logout-link" onclick="return confirm('Are you sure you want to logout?')" data-title="Logout">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</aside>