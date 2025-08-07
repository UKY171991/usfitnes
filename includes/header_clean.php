<?php
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
  <link rel="icon" type="image/x-icon" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzAwN2JmZiI+PHBhdGggZD0iTTEyIDJDNi40OCAyIDIgNi40OCAyIDEyczQuNDggMTAgMTAgMTAgMTAtNC40OCAxMC0xMFMxNy41MiAyIDEyIDJ6TTExIDV2NGgtMVY1aDFtMCA4djJoLTFWMTNoMSIvPjwvc3ZnPg==">
  
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- AdminLTE Theme style -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
  
  <!-- Toastr -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/global.css?v=<?php echo time(); ?>">
  
  <style>
    .content-wrapper {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
    }
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .card-header {
      background: transparent;
      border-bottom: 2px solid #e9ecef;
    }
    .btn {
      border-radius: 8px;
    }
    .table {
      border-radius: 10px;
      overflow: hidden;
    }
    .modal-content {
      border-radius: 15px;
    }
    .navbar-light .navbar-nav .nav-link {
      color: #fff;
    }
    .main-sidebar {
      background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
    }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzAwN2JmZiIgd2lkdGg9IjYwIiBoZWlnaHQ9IjYwIj48cGF0aCBkPSJNMTIgMkM2LjQ4IDIgMiA2LjQ4IDIgMTJzNC40OCAxMCAxMCAxMCAxMC00LjQ4IDEwLTEwUzE3LjUyIDIgMTIgMnpNMTEgNXY0aC0xVjVoMW0wIDh2MmgtMVYxM2gxIi8+PC9zdmc+" alt="PathLabPro" height="60" width="60">
  </div>

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-light" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link text-white" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="dashboard.php" class="nav-link text-white">
          <i class="fas fa-home mr-1"></i>Home
        </a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link text-white" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item-text">3 New Messages</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <div class="media">
              <div class="media-body">
                <h3 class="dropdown-item-title">System Alert</h3>
                <p class="text-sm">Equipment maintenance due</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 2 hours ago</p>
              </div>
            </div>
          </a>
        </div>
      </li>
      
      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link text-white" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge">5</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item-text">5 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file-medical mr-2"></i> 3 new test orders
            <span class="float-right text-muted text-sm">1 min</span>
          </a>
        </div>
      </li>

      <!-- User Account Menu -->
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle text-white" data-toggle="dropdown">
          <span class="d-none d-md-inline"><?php echo htmlspecialchars($full_name); ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <!-- User image -->
          <li class="user-header text-center" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);">
            <div class="user-avatar mx-auto mb-2" style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white;">
              <?php echo $user_initial; ?>
            </div>
            <p class="text-white">
              <?php echo htmlspecialchars($full_name); ?>
              <small class="d-block"><?php echo ucfirst($user_type); ?></small>
            </p>
          </li>
          
          <!-- Menu Footer-->
          <li class="user-footer">
            <a href="settings.php" class="btn btn-default btn-flat">
              <i class="fas fa-user-cog mr-1"></i>Settings
            </a>
            <a href="logout.php" class="btn btn-default btn-flat float-right">
              <i class="fas fa-sign-out-alt mr-1"></i>Sign out
            </a>
          </li>
        </ul>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->
