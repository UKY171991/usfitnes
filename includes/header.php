<?php
// Include initialization file
require_once __DIR__ . '/init.php';

// Get user information for header
$user_id = $_SESSION['user_id'] ?? '';
$full_name = $_SESSION['full_name'] ?? $_SESSION['name'] ?? 'User';
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>PathLab Pro | <?php echo $page_title ?? 'Laboratory Management System'; ?></title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAABILAAASCwAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
  
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
  
  <!-- Toastr CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
  
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  
  <!-- Global Custom CSS -->
  <link rel="stylesheet" href="css/global.css">
  
  <!-- Additional Custom CSS -->
  <style>
  <!-- Additional Custom CSS -->
  <style>
    /* Page-specific overrides can be added here */
    
    /* Ensure DataTables responsiveness */
    .dataTables_wrapper .dataTables_processing {
        position: absolute;
        top: 50%;
        left: 50%;
        width: auto;
        margin-left: -50px;
        margin-top: -25px;
        padding: 1rem 2rem;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow-lg);
        font-weight: var(--font-weight-medium);
        color: var(--primary-color);
    }
    
    /* Custom scrollbar for tables */
    .dataTables_scrollBody::-webkit-scrollbar {
        height: 6px;
    }
    
    .dataTables_scrollBody::-webkit-scrollbar-track {
        background: var(--light);
    }
    
    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 3px;
    }
    
    /* Loading spinner for AJAX calls */
    .ajax-loading {
        position: relative;
    }
    
    .ajax-loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid var(--light);
        border-top: 2px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        z-index: 999;
    }
    
    /* Enhanced form focus states */
    .form-control:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.25) !important;
        transform: translateY(-1px);
    }
    
    /* Button loading state */
    .btn-loading {
        pointer-events: none;
        opacity: 0.8;
        position: relative;
    }
    
    .btn-loading::before {
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        width: 16px;
        height: 16px;
        margin: -8px 0 0 -8px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    /* Hide original button text when loading */
    .btn-loading span {
        visibility: hidden;
    }
    
    /* Enhanced hover effects for interactive elements */
    .clickable {
        cursor: pointer;
        transition: var(--transition);
    }
    
    .clickable:hover {
        transform: translateY(-1px);
        box-shadow: var(--box-shadow-lg);
    }
  </style>
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

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
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- User Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-user"></i>
          <?php echo htmlspecialchars($full_name ?? 'User'); ?>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header"><?php echo ucfirst(htmlspecialchars($user_type ?? 'user')); ?></span>
          <div class="dropdown-divider"></div>
          <a href="settings.php" class="dropdown-item">
            <i class="fas fa-user mr-2"></i> Profile
          </a>
          <div class="dropdown-divider"></div>
          <a href="logout.php" class="dropdown-item" onclick="return confirm('Are you sure you want to logout?')">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </a>
        </div>
      </li>
      
      <!-- Fullscreen toggle -->
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->