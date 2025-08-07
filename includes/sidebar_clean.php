  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="dashboard.php" class="brand-link text-center">
      <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2ZmZiIgd2lkdGg9IjMwIiBoZWlnaHQ9IjMwIj48cGF0aCBkPSJNMTIgMkM2LjQ4IDIgMiA2LjQ4IDIgMTJzNC40OCAxMCAxMCAxMCAxMC00LjQ4IDEwLTEwUzE3LjUyIDIgMTIgMnpNMTEgNXY0aC0xVjVoMW0wIDh2MmgtMVYxM2gxIi8+PC9zdmc+" alt="PathLab Pro" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">PathLab Pro</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <div style="width: 33px; height: 33px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 14px;">
            <?php echo $user_initial ?? 'S'; ?>
          </div>
        </div>
        <div class="info">
          <a href="#" class="d-block text-white"><?php echo htmlspecialchars($full_name ?? 'System Admin'); ?></a>
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
            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <!-- Patient Management -->
          <li class="nav-header">PATIENT MANAGEMENT</li>
          <li class="nav-item">
            <a href="patients.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'patients.php' ? 'active' : ''; ?>">
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
            <a href="test-orders.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'test-orders.php' ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-vial"></i>
              <p>
                Test Orders
                <span class="badge badge-warning right" id="orders-count">0</span>
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="results.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'results.php' ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-clipboard-list"></i>
              <p>Results</p>
            </a>
          </li>

          <!-- Staff Management -->
          <li class="nav-header">STAFF MANAGEMENT</li>
          <li class="nav-item">
            <a href="doctors.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'doctors.php' ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-user-md"></i>
              <p>
                Doctors
                <span class="badge badge-success right" id="doctors-count">0</span>
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-users"></i>
              <p>Users</p>
            </a>
          </li>

          <!-- Equipment Management -->
          <li class="nav-header">EQUIPMENT</li>
          <li class="nav-item">
            <a href="equipment.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'equipment.php' ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-microscope"></i>
              <p>
                Equipment
                <span class="badge badge-primary right" id="equipment-count">0</span>
              </p>
            </a>
          </li>

          <!-- Reports -->
          <li class="nav-header">REPORTS & ANALYTICS</li>
          <li class="nav-item">
            <a href="reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-chart-bar"></i>
              <p>Reports</p>
            </a>
          </li>

          <!-- Settings -->
          <li class="nav-header">SYSTEM</li>
          <li class="nav-item">
            <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-cog"></i>
              <p>Settings</p>
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
