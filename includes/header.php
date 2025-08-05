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
  
  <!-- Global Custom CSS with cache busting -->
  <link rel="stylesheet" href="css/global.css?v=<?php echo time(); ?>">
  
  <!-- Page-specific CSS Override -->
  <style>
    /* Debug: Ensure our styles are loading */
    .debug-test {
      background-color: red !important;
      color: white !important;
      padding: 10px !important;
      margin: 10px !important;
    }
    
    /* Force our styles to take precedence over AdminLTE */
    body.hold-transition {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
      background-color: #f4f6f9 !important;
    }
    
    /* Ensure AdminLTE wrapper structure */
    .wrapper {
      min-height: 100vh !important;
      position: relative !important;
    }
    
    .content-wrapper {
      background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%) !important;
      min-height: calc(100vh - 57px) !important;
      margin-left: 250px !important;
    }
    
    @media (max-width: 991.98px) {
      .content-wrapper {
        margin-left: 0 !important;
      }
    }
    
    .main-header.navbar {
      background: linear-gradient(135deg, #2c5aa0 0%, #1e3c72 100%) !important;
      border-bottom: 2px solid #4b6cb7 !important;
    }
    
    .main-sidebar {
      background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%) !important;
      position: fixed !important;
      top: 57px !important;
      left: 0 !important;
      bottom: 0 !important;
      width: 250px !important;
      z-index: 1020 !important;
    }
    
    .sidebar-mini.sidebar-collapse .main-sidebar {
      width: 4.6rem !important;
    }
    
    .sidebar-mini.sidebar-collapse .content-wrapper {
      margin-left: 4.6rem !important;
    }
    
    .card {
      border: none !important;
      border-radius: 0.75rem !important;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
      transition: all 0.3s ease !important;
      background: white !important;
      margin-bottom: 1.5rem !important;
    }
    
    .card:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .card-header {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
      border-bottom: 1px solid #e9ecef !important;
      font-weight: 600 !important;
      color: #2c5aa0 !important;
      padding: 1rem 1.5rem !important;
    }
    
    .card-title {
      color: #2c5aa0 !important;
      font-weight: 600 !important;
      display: flex !important;
      align-items: center !important;
      gap: 0.5rem !important;
      margin: 0 !important;
    }
    
    .card-body {
      padding: 1.5rem !important;
    }
    
    .btn {
      border-radius: 0.375rem !important;
      font-weight: 500 !important;
      transition: all 0.3s ease !important;
    }
    
    .btn:hover {
      transform: translateY(-1px) !important;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #2c5aa0 0%, #1e3c72 100%) !important;
      border-color: #2c5aa0 !important;
      color: white !important;
    }
    
    .btn-primary:hover {
      background: linear-gradient(135deg, #1e3c72 0%, #2c5aa0 100%) !important;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
      color: white !important;
    }
    
    .btn-success {
      background: #28a745 !important;
      border-color: #28a745 !important;
      color: white !important;
    }
    
    .btn-info {
      background: #17a2b8 !important;
      border-color: #17a2b8 !important;
      color: white !important;
    }
    
    .btn-warning {
      background: #ffc107 !important;
      border-color: #ffc107 !important;
      color: #212529 !important;
    }
    
    .btn-danger {
      background: #dc3545 !important;
      border-color: #dc3545 !important;
      color: white !important;
    }
    
    .form-control {
      border-radius: 0.375rem !important;
      transition: all 0.3s ease !important;
      border: 1px solid #dee2e6 !important;
    }
    
    .form-control:focus {
      border-color: #2c5aa0 !important;
      box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.25) !important;
    }
    
    .table thead th {
      background: linear-gradient(135deg, #2c5aa0 0%, #1e3c72 100%) !important;
      color: white !important;
      font-weight: 600 !important;
      border: none !important;
    }
    
    .table-responsive {
      border-radius: 0.75rem !important;
      overflow: hidden !important;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }
    
    .small-box {
      border-radius: 0.75rem !important;
      transition: all 0.3s ease !important;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
      position: relative !important;
      overflow: hidden !important;
    }
    
    .small-box:hover {
      transform: translateY(-5px) !important;
      box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.2) !important;
    }
    
    .small-box .inner {
      padding: 2rem !important;
      position: relative !important;
      z-index: 2 !important;
    }
    
    .small-box h3 {
      font-weight: 700 !important;
      font-size: 2.5rem !important;
      margin: 0 !important;
      color: white !important;
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
    }
    
    .small-box p {
      font-size: 1.125rem !important;
      color: rgba(255, 255, 255, 0.9) !important;
      margin: 0 !important;
      font-weight: 500 !important;
    }
    
    .small-box .icon {
      position: absolute !important;
      top: 1rem !important;
      right: 1rem !important;
      font-size: 4rem !important;
      color: rgba(255, 255, 255, 0.2) !important;
      z-index: 1 !important;
    }
    
    .modal-content {
      border: none !important;
      border-radius: 0.75rem !important;
      box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.2) !important;
      overflow: hidden !important;
    }
    
    .modal-header {
      border-radius: 0.75rem 0.75rem 0 0 !important;
      padding: 1.5rem !important;
    }
    
    .modal-body {
      padding: 1.5rem !important;
    }
    
    .modal-footer {
      padding: 1.5rem !important;
      background: #f8f9fa !important;
      border-top: 1px solid #e9ecef !important;
    }
    
    .badge {
      border-radius: 0.375rem !important;
      font-weight: 500 !important;
      padding: 0.25rem 0.5rem !important;
    }
    
    .content-header h1 {
      color: #2c5aa0 !important;
      font-weight: 600 !important;
      display: flex !important;
      align-items: center !important;
      gap: 0.5rem !important;
      font-size: 1.8rem !important;
      margin: 0 !important;
    }
    
    .content-header {
      padding: 1.5rem 1.5rem 0 !important;
      background: transparent !important;
    }
    
    .content {
      padding: 1.5rem !important;
    }
    
    .breadcrumb {
      background: transparent !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    
    .breadcrumb-item a {
      color: #2c5aa0 !important;
      text-decoration: none !important;
    }
    
    .breadcrumb-item a:hover {
      color: #1e3c72 !important;
      text-decoration: underline !important;
    }
    
    /* DataTables styling */
    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
      border-radius: 0.375rem !important;
      border: 1px solid #dee2e6 !important;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
      border-color: #2c5aa0 !important;
      box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.25) !important;
    }
    
    .dataTables_paginate .paginate_button {
      border-radius: 0.375rem !important;
      margin: 0 0.25rem !important;
    }
    
    .dataTables_paginate .paginate_button:hover {
      background: #2c5aa0 !important;
      color: white !important;
      border-color: #2c5aa0 !important;
    }
    
    .dataTables_paginate .paginate_button.current {
      background: #2c5aa0 !important;
      color: white !important;
      border-color: #2c5aa0 !important;
    }
    
    /* Loading animation */
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .fa-spin {
      animation: spin 1s linear infinite !important;
    }
    
    /* Responsive improvements */
    @media (max-width: 768px) {
      .content-header h1 {
        font-size: 1.5rem !important;
      }
      
      .small-box h3 {
        font-size: 1.8rem !important;
      }
      
      .card-body {
        padding: 1rem !important;
      }
      
      .content-header {
        padding: 1rem 1rem 0 !important;
      }
      
      .content {
        padding: 1rem !important;
      }
    }
    
    /* Ensure proper z-index for navigation */
    .main-header {
      z-index: 1030 !important;
    }
    
    /* Fix for AdminLTE body classes */
    .hold-transition .content-wrapper {
      transition: margin-left 0.3s ease-in-out !important;
    }
    
    /* Input group styling */
    .input-group {
      border-radius: 0.375rem !important;
      overflow: hidden !important;
    }
    
    .input-group .form-control:not(:last-child) {
      border-top-right-radius: 0 !important;
      border-bottom-right-radius: 0 !important;
    }
    
    .input-group .btn:not(:first-child) {
      border-top-left-radius: 0 !important;
      border-bottom-left-radius: 0 !important;
    }
  </style>
  
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