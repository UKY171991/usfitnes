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
      <!-- Sidebar user panel (enhanced) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://via.placeholder.com/160x160/2c5aa0/ffffff?text=<?php echo $user_initial; ?>" class="img-circle elevation-2" alt="User Image">
          <div class="status-indicator bg-success"></div>
        </div>
        <div class="info">
          <a href="settings.php" class="d-block text-white">
            <strong><?php echo htmlspecialchars($full_name); ?></strong>
          </a>
          <small class="text-muted d-flex align-items-center">
            <i class="fas fa-circle text-success mr-1" style="font-size: 0.5rem;"></i>
            <?php echo ucfirst(htmlspecialchars($user_type)); ?> • Online
          </small>
        </div>
      </div>

      <!-- Quick Search -->
      <div class="sidebar-search-form mb-3">
        <div class="input-group">
          <input type="text" class="form-control form-control-sidebar" placeholder="Search pages..." id="sidebarSearch" autocomplete="off">
          <div class="input-group-append">
            <button class="btn btn-sidebar" type="button" id="clearSearch">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="sidebar-stats mb-3 px-3">
        <div class="row text-center">
          <div class="col-4">
            <div class="stat-item">
              <i class="fas fa-users text-info"></i>
              <div class="stat-number" id="quickPatientCount">-</div>
              <div class="stat-label">Patients</div>
            </div>
          </div>
          <div class="col-4">
            <div class="stat-item">
              <i class="fas fa-flask text-warning"></i>
              <div class="stat-number" id="quickTestCount">-</div>
              <div class="stat-label">Tests</div>
            </div>
          </div>
          <div class="col-4">
            <div class="stat-item">
              <i class="fas fa-user-md text-success"></i>
              <div class="stat-number" id="quickDoctorCount">-</div>
              <div class="stat-label">Doctors</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          
          <!-- Dashboard -->
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo isActive('dashboard.php'); ?>">
              <i class="nav-icon fas fa-tachometer-alt text-info"></i>
              <p>
                Dashboard
                <span class="right badge badge-info">Home</span>
              </p>
            </a>
          </li>

          <!-- Patient Management -->
          <li class="nav-header"><i class="fas fa-users mr-1"></i> PATIENT MANAGEMENT</li>
          <li class="nav-item has-treeview <?php echo in_array(basename($_SERVER['PHP_SELF']), ['patients.php']) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['patients.php']) ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-user-injured text-primary"></i>
              <p>
                Patient Records
                <i class="right fas fa-angle-left"></i>
                <span class="right badge badge-primary" id="patientBadge">-</span>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="patients.php" class="nav-link <?php echo isActive('patients.php'); ?>">
                  <i class="far fa-circle nav-icon text-primary"></i>
                  <p>All Patients</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="patients.php?action=add" class="nav-link">
                  <i class="fas fa-plus nav-icon text-success"></i>
                  <p>Add New Patient</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="patients.php?action=search" class="nav-link">
                  <i class="fas fa-search nav-icon text-info"></i>
                  <p>Search Patients</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- Laboratory Operations -->
          <li class="nav-header"><i class="fas fa-flask mr-1"></i> LABORATORY OPERATIONS</li>
          <li class="nav-item has-treeview <?php echo in_array(basename($_SERVER['PHP_SELF']), ['test-orders.php', 'tests.php', 'results.php']) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['test-orders.php', 'tests.php', 'results.php']) ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-vials text-warning"></i>
              <p>
                Test Management
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="test-orders.php" class="nav-link <?php echo isActive('test-orders.php'); ?>">
                  <i class="fas fa-clipboard-list nav-icon text-warning"></i>
                  <p>
                    Test Orders
                    <span class="right badge badge-warning" id="ordersBadge">New</span>
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="tests.php" class="nav-link <?php echo isActive('tests.php'); ?>">
                  <i class="fas fa-flask nav-icon text-info"></i>
                  <p>Available Tests</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="results.php" class="nav-link <?php echo isActive('results.php'); ?>">
                  <i class="fas fa-file-medical-alt nav-icon text-success"></i>
                  <p>Test Results</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- Staff & Resources -->
          <li class="nav-header"><i class="fas fa-user-tie mr-1"></i> STAFF & RESOURCES</li>
          <li class="nav-item">
            <a href="doctors.php" class="nav-link <?php echo isActive('doctors.php'); ?>">
              <i class="nav-icon fas fa-user-md text-success"></i>
              <p>
                Doctors
                <span class="right badge badge-success" id="doctorsBadge">-</span>
              </p>
            </a>
          </li>
          
          <li class="nav-item has-treeview <?php echo in_array(basename($_SERVER['PHP_SELF']), ['equipment.php']) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['equipment.php']) ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-microscope text-purple"></i>
              <p>
                Equipment
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="equipment.php" class="nav-link <?php echo isActive('equipment.php'); ?>">
                  <i class="fas fa-list nav-icon text-purple"></i>
                  <p>All Equipment</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="equipment.php?action=maintenance" class="nav-link">
                  <i class="fas fa-wrench nav-icon text-warning"></i>
                  <p>Maintenance</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="equipment.php?action=add" class="nav-link">
                  <i class="fas fa-plus nav-icon text-success"></i>
                  <p>Add Equipment</p>
                </a>
              </li>
            </ul>
          </li>

          <?php if($user_type == 'admin'): ?>
          <!-- Admin Only Section -->
          <li class="nav-header"><i class="fas fa-crown mr-1 text-warning"></i> ADMINISTRATION</li>
          <li class="nav-item">
            <a href="users.php" class="nav-link <?php echo isActive('users.php'); ?>">
              <i class="nav-icon fas fa-users-cog text-danger"></i>
              <p>
                User Management
                <span class="right badge badge-danger">Admin</span>
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="system_status.php" class="nav-link <?php echo isActive('system_status.php'); ?>">
              <i class="nav-icon fas fa-server text-info"></i>
              <p>
                System Status
                <span class="right badge badge-info" id="systemStatus">OK</span>
              </p>
            </a>
          </li>
          <?php endif; ?>

          <!-- Reports & Analytics -->
          <li class="nav-header"><i class="fas fa-chart-line mr-1"></i> REPORTS & ANALYTICS</li>
          <li class="nav-item has-treeview <?php echo in_array(basename($_SERVER['PHP_SELF']), ['reports.php', 'view_all_data.php']) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['reports.php', 'view_all_data.php']) ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-chart-bar text-primary"></i>
              <p>
                Reports & Data
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="reports.php" class="nav-link <?php echo isActive('reports.php'); ?>">
                  <i class="fas fa-file-chart-line nav-icon text-primary"></i>
                  <p>Generate Reports</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="view_all_data.php" class="nav-link <?php echo isActive('view_all_data.php'); ?>">
                  <i class="fas fa-database nav-icon text-secondary"></i>
                  <p>Database View</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="reports.php?type=analytics" class="nav-link">
                  <i class="fas fa-chart-pie nav-icon text-info"></i>
                  <p>Analytics</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- System & Settings -->
          <li class="nav-header"><i class="fas fa-cog mr-1"></i> SYSTEM</li>
          <li class="nav-item">
            <a href="settings.php" class="nav-link <?php echo isActive('settings.php'); ?>">
              <i class="nav-icon fas fa-cogs text-secondary"></i>
              <p>Settings</p>
            </a>
          </li>
          
          <!-- Quick Access Tools -->
          <li class="nav-header"><i class="fas fa-bolt mr-1"></i> QUICK ACCESS</li>
          <li class="nav-item">
            <a href="index.php" class="nav-link <?php echo isActive('index.php'); ?>">
              <i class="nav-icon fas fa-home text-success"></i>
              <p>Public Homepage</p>
            </a>
          </li>
          
          <!-- Emergency & Support -->
          <li class="nav-item">
            <a href="#" class="nav-link" onclick="showEmergencyContacts()">
              <i class="nav-icon fas fa-phone text-danger"></i>
              <p>Emergency Contacts</p>
            </a>
          </li>
          
          <!-- Help & Documentation -->
          <li class="nav-item">
            <a href="#" class="nav-link" onclick="showHelp()">
              <i class="nav-icon fas fa-question-circle text-info"></i>
              <p>Help & Support</p>
            </a>
          </li>
          
          <!-- Logout -->
          <li class="nav-item mt-3" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
            <a href="logout.php" class="nav-link text-danger" onclick="return confirm('Are you sure you want to logout?')">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>
                <strong>Logout</strong>
                <small class="d-block">Sign out safely</small>
              </p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
