<?php
// Include initialization file
require_once __DIR__ . '/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="csrf-token" content="<?php echo bin2hex(random_bytes(32)); ?>">
  <title>PathLab Pro | <?php echo $page_title ?? 'Laboratory Management System'; ?></title>

  <!-- Preconnect to external domains for performance -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://cdnjs.cloudflare.com">
  <link rel="preconnect" href="https://cdn.jsdelivr.net">

  <!-- Critical CSS - Load first -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Core Framework CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
  
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
  
  <!-- UI Components CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.0.0/select2-bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">
  
  <!-- Chart and Visualization CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css">
  
  <!-- Custom PathLab Pro CSS -->
  <style>
    :root {
      --primary-color: #2c5aa0;
      --secondary-color: #f8f9fa;
      --success-color: #28a745;
      --danger-color: #dc3545;
      --warning-color: #ffc107;
      --info-color: #17a2b8;
      --dark-color: #343a40;
      --light-color: #f8f9fa;
      --pathlab-blue: #3498db;
      --pathlab-green: #2ecc71;
      --pathlab-orange: #f39c12;
      --pathlab-red: #e74c3c;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #f4f6f9;
    }

    .main-header {
      background: linear-gradient(135deg, var(--primary-color), var(--pathlab-blue));
      border-bottom: none;
    }

    .navbar-light .navbar-brand {
      color: white !important;
      font-weight: 600;
    }

    .main-sidebar {
      background: linear-gradient(180deg, #2c3e50, #34495e);
    }

    .nav-sidebar .nav-link {
      color: rgba(255,255,255,0.8) !important;
      border-radius: 0.375rem;
      margin: 0.125rem 0.5rem;
      padding: 0.625rem 1rem;
      transition: all 0.3s ease;
    }

    .nav-sidebar .nav-link:hover,
    .nav-sidebar .nav-link.active {
      background: rgba(255,255,255,0.1) !important;
      color: white !important;
    }

    .card {
      border: none;
      border-radius: 0.75rem;
      box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
      transition: all 0.3s ease;
    }

    .card:hover {
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
      transform: translateY(-2px);
    }

    .btn {
      border-radius: 0.5rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.15);
    }

    .modal-content {
      border: none;
      border-radius: 1rem;
    }

    .modal-header {
      background: linear-gradient(135deg, var(--primary-color), var(--pathlab-blue));
      color: white;
      border-radius: 1rem 1rem 0 0;
    }

    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 9999;
      display: none;
      align-items: center;
      justify-content: center;
    }

    .stat-updated {
      animation: pulse 1s ease-in-out;
    }

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }

    .auto-saved {
      background-color: #d4edda !important;
      border-color: #c3e6cb !important;
      transition: all 0.3s ease;
    }

    .fade-in {
      animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .table-responsive {
      border-radius: 0.5rem;
    }

    .dataTables_wrapper .dataTables_filter input {
      border-radius: 0.5rem;
      border: 1px solid #dee2e6;
      padding: 0.5rem 1rem;
    }

    .dataTables_wrapper .dataTables_length select {
      border-radius: 0.5rem;
      border: 1px solid #dee2e6;
      padding: 0.25rem 0.5rem;
    }

    .toast {
      backdrop-filter: blur(10px);
    }
    
    .small-box {
      border-radius: 0.75rem;
      transition: all 0.3s ease;
    }
    
    .small-box:hover {
      transform: translateY(-2px);
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }

    .info-box {
      border-radius: 0.75rem;
      transition: all 0.3s ease;
    }

    .info-box:hover {
      transform: translateY(-2px);
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #a8a8a8;
    }
  </style>
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/custom.css">

  <?php if (isset($additional_styles)): ?>
  <?php echo $additional_styles; ?>
  <?php endif; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper"
  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <?php if (hasLogo()): ?>
        <img class="animation__shake" src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo" height="60" width="60">
    <?php else: ?>
        <h2 class="animation__shake text-primary font-weight-bold">PathLab Pro</h2>
    <?php endif; ?>
  </div>

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="dashboard.php" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="patients.php" class="nav-link">Patients</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="test-orders.php" class="nav-link">Orders</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search patients, orders..." aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>

      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="https://via.placeholder.com/50x50/007bff/ffffff?text=DR" alt="Doctor" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Dr. Anderson
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Patient CBC results ready</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
        </div>
      </li>

      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge">15</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">15 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i> 4 new test orders
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 8 new patients registered
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file mr-2"></i> 3 reports generated
            <span class="float-right text-muted text-sm">2 days</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li>

      <!-- User Account Menu -->
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
          <img src="https://via.placeholder.com/160x160/2c5aa0/ffffff?text=<?php echo $user_initial; ?>" class="user-image img-circle elevation-2" alt="User Image">
          <span class="d-none d-md-inline"><?php echo htmlspecialchars($full_name); ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <!-- User image -->
          <li class="user-header bg-primary">
            <img src="https://via.placeholder.com/160x160/2c5aa0/ffffff?text=<?php echo $user_initial; ?>" class="img-circle elevation-2" alt="User Image">
            <p>
              <?php echo htmlspecialchars($full_name); ?>
              <small><?php echo ucfirst(htmlspecialchars($user_type)); ?> - Member since <?php echo date('M Y'); ?></small>
            </p>
          </li>
          <!-- Menu Body -->
          <li class="user-body">
            <div class="row">
              <div class="col-4 text-center">
                <a href="patients.php">Patients</a>
              </div>
              <div class="col-4 text-center">
                <a href="test-orders.php">Orders</a>
              </div>
              <div class="col-4 text-center">
                <a href="reports.php">Reports</a>
              </div>
            </div>
          </li>
          <!-- Menu Footer-->
          <li class="user-footer">
            <a href="settings.php" class="btn btn-default btn-flat">Profile</a>
            <a href="logout.php" class="btn btn-default btn-flat float-right">Sign out</a>
          </li>
        </ul>
      </li>
      
      <!-- Fullscreen toggle -->
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      
      <!-- Control Sidebar Toggle -->
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->
