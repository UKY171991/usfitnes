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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- AdminLTE 3 Theme -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  
  <!-- Bootstrap 4 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
  
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
  
  <!-- Toastr Notifications -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
  
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  
  <!-- Select2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
  
  <!-- Tempus Dominus Bootstrap 4 (Date/Time Picker) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">
  
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css">
  
  <!-- Chart.js -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.2.1/dist/chart.min.css">
  
  <!-- PathLab Pro Custom CSS -->
  <link rel="stylesheet" href="css/pathlab-custom.css?v=<?php echo time(); ?>">
  
  <style>
    /* PathLab Pro Custom Styles */
    :root {
      --primary-color: #007bff;
      --secondary-color: #6c757d;
      --success-color: #28a745;
      --info-color: #17a2b8;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --light-color: #f8f9fa;
      --dark-color: #343a40;
    }
    
    .wrapper {
      min-height: 100vh;
    }
    
    /* Brand styling */
    .brand-link {
      padding: 0.8125rem 0.5rem;
      background-color: rgba(255,255,255,.1);
      border-bottom: 1px solid rgba(255,255,255,.2);
      transition: all 0.3s ease-in-out;
    }
    
    .brand-link:hover {
      background-color: rgba(255,255,255,.2);
      text-decoration: none;
    }
    
    .brand-text {
      font-weight: 600 !important;
      font-size: 1.1rem;
    }
    
    /* Sidebar styling */
    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active {
      background-color: var(--primary-color);
      color: #fff;
      border-radius: 0.375rem;
      margin: 0 0.5rem;
    }
    
    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link {
      transition: all 0.3s ease-in-out;
      margin: 0.1rem 0.5rem;
      border-radius: 0.375rem;
    }
    
    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link:hover {
      background-color: rgba(255,255,255,.1);
    }
    
    /* Content wrapper */
    .content-wrapper {
      min-height: calc(100vh - 3.5rem);
      background-color: #f4f6f9;
    }
    
    /* Header styling */
    .content-header {
      padding: 15px 0.5rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      margin-bottom: 1rem;
      border-radius: 0 0 1rem 1rem;
    }
    
    .content-header h1 {
      color: white;
      margin: 0;
      font-weight: 600;
    }
    
    .breadcrumb {
      background-color: transparent;
      margin-bottom: 0;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
      color: rgba(255,255,255,0.7);
    }
    
    .breadcrumb-item a {
      color: rgba(255,255,255,0.8);
    }
    
    .breadcrumb-item.active {
      color: white;
    }
    
    /* Card enhancements */
    .card {
      box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
      border-radius: 0.5rem;
      border: none;
      margin-bottom: 1rem;
      transition: all 0.3s ease-in-out;
    }
    
    .card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,.15);
    }
    
    .card-header {
      background-color: transparent;
      border-bottom: 1px solid rgba(0,0,0,.125);
      padding: 0.75rem 1rem;
    }
    
    .card-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 0;
    }
    
    /* Info boxes */
    .info-box {
      box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
      border-radius: 0.5rem;
      border: none;
      transition: all 0.3s ease-in-out;
    }
    
    .info-box:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,.15);
    }
    
    /* Navigation pills */
    .nav-pills .nav-link {
      border-radius: 0.5rem;
    }
    
    /* Buttons */
    .btn {
      border-radius: 0.375rem;
      font-weight: 500;
      transition: all 0.3s ease-in-out;
    }
    
    .btn:hover {
      transform: translateY(-1px);
    }
    
    /* Tables */
    .table {
      border-radius: 0.5rem;
      overflow: hidden;
    }
    
    .table thead th {
      border-top: none;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
    }
    
    /* Badges */
    .badge {
      font-size: 0.75em;
      border-radius: 0.375rem;
    }
    
    /* Loading spinner */
    .loading-spinner {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(255,255,255,.3);
      border-radius: 50%;
      border-top-color: #fff;
      animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .content-header {
        padding: 10px 0.5rem;
      }
      
      .content-header h1 {
        font-size: 1.5rem;
      }
      
      .sidebar-mini.sidebar-collapse .content-wrapper {
        margin-left: 0;
      }
    }
    
    /* Print styles */
    @media print {
      .main-sidebar,
      .main-header,
      .content-header,
      .btn,
      .card-tools {
        display: none !important;
      }
      
      .content-wrapper {
        margin-left: 0 !important;
        padding: 0 !important;
      }
    }
  </style>
  
  <?php echo $additional_css ?? ''; ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
          <i class="fas fa-bars"></i>
        </a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="dashboard.php" class="nav-link">
          <i class="fas fa-home mr-1"></i>Home
        </a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      
      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge" id="notification-count">0</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-header">Notifications</span>
          <div class="dropdown-divider"></div>
          <div id="notifications-list">
            <a href="#" class="dropdown-item">
              <i class="fas fa-envelope mr-2"></i> No new notifications
            </a>
          </div>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li>
      
      <!-- User Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <div class="user-image d-inline-block mr-1" style="width: 25px; height: 25px; background-color: #007bff; color: white; border-radius: 50%; text-align: center; line-height: 25px; font-size: 12px; font-weight: bold;">
            <?php echo $user_initial; ?>
          </div>
          <span class="d-none d-md-inline"><?php echo htmlspecialchars($full_name); ?></span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <div class="dropdown-header">
            <strong><?php echo htmlspecialchars($full_name); ?></strong><br>
            <small class="text-muted"><?php echo ucfirst(htmlspecialchars($user_type)); ?></small>
          </div>
          <div class="dropdown-divider"></div>
          <a href="settings.php" class="dropdown-item">
            <i class="fas fa-user mr-2"></i> Profile Settings
          </a>
          <a href="settings.php" class="dropdown-item">
            <i class="fas fa-cog mr-2"></i> System Settings
          </a>
          <div class="dropdown-divider"></div>
          <a href="logout.php" class="dropdown-item dropdown-footer text-danger">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </a>
        </div>
      </li>
      
      <!-- Control Sidebar -->
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->
