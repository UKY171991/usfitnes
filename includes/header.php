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
  <title>PathLab Pro | <?php echo $page_title ?? 'Laboratory Management System'; ?></title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
  
  <!-- Custom CSS -->
  <style>
    :root {
      --primary-color: #2c5aa0;
      --secondary-color: #f8f9fa;
      --success-color: #28a745;
      --danger-color: #dc3545;
      --warning-color: #ffc107;
      --info-color: #17a2b8;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #f4f6f9;
    }

    /* Sidebar Styling */
    .main-sidebar {
      background: linear-gradient(180deg, #2c3e50, #34495e);
    }

    .brand-link {
      background: rgba(0,0,0,0.1);
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .brand-text {
      color: white !important;
      font-weight: 600;
    }

    .user-panel .info a {
      color: rgba(255,255,255,0.9) !important;
      font-weight: 500;
    }

    .nav-header {
      color: rgba(255,255,255,0.6) !important;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-top: 1rem;
      margin-bottom: 0.5rem;
    }

    .nav-sidebar .nav-link {
      color: rgba(255,255,255,0.8) !important;
      border-radius: 0.375rem;
      margin: 0.125rem 0.5rem;
      padding: 0.625rem 1rem;
      transition: all 0.3s ease;
    }

    .nav-sidebar .nav-link:hover {
      background: rgba(255,255,255,0.1) !important;
      color: white !important;
      transform: translateX(2px);
    }

    .nav-sidebar .nav-link.active {
      background: linear-gradient(45deg, var(--primary-color), #3498db) !important;
      color: white !important;
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .nav-icon {
      margin-right: 0.5rem;
      width: 1.25rem;
      text-align: center;
    }

    /* Content Area */
    .content-wrapper {
      background-color: #f4f6f9;
    }

    .card {
      border: none;
      border-radius: 0.75rem;
      box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
      transition: all 0.3s ease;
    }

    .card:hover {
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }

    .card-header {
      background-color: white;
      border-bottom: 1px solid #e9ecef;
      font-weight: 600;
    }

    /* Buttons */
    .btn {
      border-radius: 0.375rem;
      font-weight: 500;
    }

    .btn-primary {
      background: var(--primary-color);
      border-color: var(--primary-color);
    }

    /* Tables */
    .table {
      border-radius: 0.5rem;
      overflow: hidden;
    }

    .table thead th {
      background-color: #f8f9fa;
      border: none;
      font-weight: 600;
      color: #495057;
    }

    /* Mobile Responsiveness */
    @media (max-width: 767.98px) {
      .nav-sidebar .nav-link {
        padding: 0.5rem 0.75rem;
      }
      
      .nav-sidebar .nav-link p {
        font-size: 0.9rem;
      }
    }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="https://via.placeholder.com/60x60/2c5aa0/ffffff?text=P" alt="PathLab Pro" height="60" width="60">
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