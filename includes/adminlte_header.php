<?php
// Include initialization file
require_once __DIR__ . '/init.php';

// Get user information for header
$user_id = $_SESSION['user_id'] ?? '';
$full_name = $_SESSION['full_name'] ?? $_SESSION['name'] ?? 'System Admin';
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'admin';
$user_initial = strtoupper(substr($full_name, 0, 1));

// Set default page title if not set
$page_title = $page_title ?? 'PathLab Pro - Laboratory Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo htmlspecialchars($page_title); ?></title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAABILAAASCwAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
  
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
  
  <!-- Toastr -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
  
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  
  <!-- Select2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
  
  <!-- Tempus Dominus Bootstrap 4 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">
  
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/adminlte-custom.css?v=<?php echo time(); ?>">
  
  <style>
    /* Custom AdminLTE3 Enhancements */
    .main-header .navbar-nav .nav-link {
      padding: 0.5rem 1rem;
    }
    
    .user-panel .info a {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 150px;
    }
    
    .content-wrapper {
      min-height: calc(100vh - 57px);
    }
    
    .brand-link {
      padding: 0.8125rem 0.5rem;
      background-color: rgba(255,255,255,.1);
    }
    
    .sidebar-dark-primary .brand-link {
      border-bottom: 1px solid #4f5962;
    }
    
    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active {
      background-color: #007bff;
      color: #fff;
    }
    
    .nav-sidebar .nav-item > .nav-link {
      padding: 0.5rem 0.5rem 0.5rem 1rem;
    }
    
    .nav-sidebar .nav-treeview > .nav-item > .nav-link {
      padding: 0.5rem 0.5rem 0.5rem 2rem;
    }
    
    /* Custom card styles */
    .info-box {
      box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
      border-radius: 0.375rem;
      margin-bottom: 1rem;
    }
    
    .card {
      box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
      margin-bottom: 1rem;
      border-radius: 0.375rem;
    }
    
    .card-header {
      background-color: transparent;
      border-bottom: 1px solid #dee2e6;
      padding: 0.75rem 1.25rem;
      position: relative;
      border-top-left-radius: 0.375rem;
      border-top-right-radius: 0.375rem;
    }
    
    .btn {
      border-radius: 0.25rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .content-wrapper, .right-side, .main-footer {
        margin-left: 0;
      }
      
      .main-sidebar {
        margin-left: -250px;
      }
      
      body.sidebar-open .main-sidebar {
        margin-left: 0;
      }
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <i class="fas fa-microscope fa-spin fa-3x text-primary"></i>
    <p class="mt-3">Loading PathLab Pro...</p>
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
        <a href="test-orders.php" class="nav-link">Tests</a>
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
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
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
            <div class="media">
              <div class="media-object bg-info rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                <i class="fas fa-user text-white"></i>
              </div>
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Lab Technician
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Test results ready for review</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
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
            <i class="fas fa-envelope mr-2"></i> 4 new messages
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 8 friend requests
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file mr-2"></i> 3 new reports
            <span class="float-right text-muted text-sm">2 days</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li>
      
      <!-- User Dropdown Menu -->
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
          <div class="user-image bg-info rounded-circle d-inline-flex align-items-center justify-content-center" 
               style="width: 25px; height: 25px; font-size: 0.8rem; font-weight: bold; color: white;">
            <?php echo $user_initial; ?>
          </div>
          <span class="d-none d-md-inline"><?php echo htmlspecialchars($full_name); ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <!-- User image -->
          <li class="user-header bg-primary">
            <div class="user-image bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                 style="width: 90px; height: 90px; font-size: 2rem; font-weight: bold; color: #007bff;">
              <?php echo $user_initial; ?>
            </div>
            <p>
              <?php echo htmlspecialchars($full_name); ?>
              <small>Member since Nov. 2023</small>
            </p>
          </li>
          <!-- Menu Body -->
          <li class="user-body">
            <div class="row">
              <div class="col-4 text-center">
                <a href="patients.php">Patients</a>
              </div>
              <div class="col-4 text-center">
                <a href="test-orders.php">Tests</a>
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
    </ul>
  </nav>
  <!-- /.navbar -->
