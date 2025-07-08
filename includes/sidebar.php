  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">    
    <!-- Brand Logo -->
    <a href="dashboard.php" class="brand-link">
      <i class="fas fa-dumbbell brand-image" style="font-size: 2rem; color: #007bff; margin-left: 0.5rem;"></i>
      <span class="brand-text font-weight-bold" style="font-size: 1.2rem; margin-left: 0.5rem;">US Fitness</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://via.placeholder.com/160x160/2c5aa0/ffffff?text=<?php echo $user_initial; ?>" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="settings.php" class="d-block"><?php echo htmlspecialchars($full_name); ?></a>
          <span class="user-role"><?php echo ucfirst(htmlspecialchars($user_type)); ?></span>
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
            <a href="dashboard.php" class="nav-link <?php echo isActive('dashboard.php'); ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <!-- Fitness Management -->
          <li class="nav-header">FITNESS</li>
          <li class="nav-item">
            <a href="test-orders.php" class="nav-link <?php echo isActive('test-orders.php'); ?>">
              <i class="nav-icon fas fa-calendar-check"></i>
              <p>Class Bookings</p>
            </a>
          </li>

          <!-- Management -->
          <li class="nav-header">MANAGEMENT</li>
          <?php if($user_type == 'admin'): ?>
          <li class="nav-item">
            <a href="users.php" class="nav-link <?php echo isActive('users.php'); ?>">
              <i class="nav-icon fas fa-users"></i>
              <p>User Management</p>
            </a>
          </li>
          <?php endif; ?>

          <!-- System -->
          <li class="nav-header">SYSTEM</li>
          <li class="nav-item">
            <a href="settings.php" class="nav-link <?php echo isActive('settings.php'); ?>">
              <i class="nav-icon fas fa-cog"></i>
              <p>Settings</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="logout.php" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
              <p>Logout</p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
