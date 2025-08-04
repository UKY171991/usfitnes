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
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://via.placeholder.com/160x160/2c5aa0/ffffff?text=<?php echo $user_initial; ?>" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="settings.php" class="d-block text-white"><?php echo htmlspecialchars($full_name); ?></a>
          <small class="text-muted"><?php echo ucfirst(htmlspecialchars($user_type)); ?></small>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          
          <!-- Dashboard -->
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo isActive('dashboard.php'); ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <!-- Patient Management -->
          <li class="nav-header">PATIENT MANAGEMENT</li>
          <li class="nav-item has-treeview <?php echo in_array(basename($_SERVER['PHP_SELF']), ['patients.php']) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['patients.php']) ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-users"></i>
              <p>
                Patients
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="patients.php" class="nav-link <?php echo isActive('patients.php'); ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>All Patients</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="patients.php?action=add" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Add New Patient</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- Laboratory Operations -->
          <li class="nav-header">LABORATORY OPERATIONS</li>
          <li class="nav-item has-treeview <?php echo in_array(basename($_SERVER['PHP_SELF']), ['test-orders.php', 'tests.php', 'results.php']) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['test-orders.php', 'tests.php', 'results.php']) ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-flask"></i>
              <p>
                Laboratory Tests
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="test-orders.php" class="nav-link <?php echo isActive('test-orders.php'); ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Test Orders
                    <span class="right badge badge-primary">New</span>
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="tests.php" class="nav-link <?php echo isActive('tests.php'); ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Available Tests</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="results.php" class="nav-link <?php echo isActive('results.php'); ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Test Results</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- Staff & Resources -->
          <li class="nav-header">STAFF & RESOURCES</li>
          <li class="nav-item">
            <a href="doctors.php" class="nav-link <?php echo isActive('doctors.php'); ?>">
              <i class="nav-icon fas fa-user-md"></i>
              <p>Doctors</p>
            </a>
          </li>
          
          <li class="nav-item has-treeview <?php echo in_array(basename($_SERVER['PHP_SELF']), ['equipment.php']) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['equipment.php']) ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-microscope"></i>
              <p>
                Equipment
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="equipment.php" class="nav-link <?php echo isActive('equipment.php'); ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>All Equipment</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="equipment.php?action=maintenance" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Maintenance</p>
                </a>
              </li>
            </ul>
          </li>

          <?php if($user_type == 'admin'): ?>
          <!-- Admin Only Section -->
          <li class="nav-header">ADMINISTRATION</li>
          <li class="nav-item">
            <a href="users.php" class="nav-link <?php echo isActive('users.php'); ?>">
              <i class="nav-icon fas fa-users-cog"></i>
              <p>User Management</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="system_status.php" class="nav-link <?php echo isActive('system_status.php'); ?>">
              <i class="nav-icon fas fa-server"></i>
              <p>System Status</p>
            </a>
          </li>
          <?php endif; ?>

          <!-- Reports & Analytics -->
          <li class="nav-header">REPORTS & ANALYTICS</li>
          <li class="nav-item has-treeview <?php echo in_array(basename($_SERVER['PHP_SELF']), ['reports.php', 'view_all_data.php']) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['reports.php', 'view_all_data.php']) ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-chart-bar"></i>
              <p>
                Reports
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="reports.php" class="nav-link <?php echo isActive('reports.php'); ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Generate Reports</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="view_all_data.php" class="nav-link <?php echo isActive('view_all_data.php'); ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Database View</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- System -->
          <li class="nav-header">SYSTEM</li>
          <li class="nav-item">
            <a href="settings.php" class="nav-link <?php echo isActive('settings.php'); ?>">
              <i class="nav-icon fas fa-cog"></i>
              <p>Settings</p>
            </a>
          </li>
          
          <!-- Quick Access Tools -->
          <li class="nav-header">QUICK ACCESS</li>
          <li class="nav-item">
            <a href="index.php" class="nav-link <?php echo isActive('index.php'); ?>">
              <i class="nav-icon fas fa-home"></i>
              <p>Home Page</p>
            </a>
          </li>
          
          <!-- Logout -->
          <li class="nav-item mt-2">
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
