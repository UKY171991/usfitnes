<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user information
$user_id = $_SESSION['user_id'] ?? '';
$full_name = $_SESSION['full_name'] ?? $_SESSION['name'] ?? 'System Admin';
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'admin';
$user_initial = strtoupper(substr($full_name, 0, 1));

// Set page title
$pageTitle = $pageTitle ?? 'Laboratory Management System';
$pageIcon = $pageIcon ?? 'fas fa-home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo htmlspecialchars($pageTitle); ?> - USFitness Lab</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/logo.png">
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- AdminLTE 3 Theme -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
    
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">
    
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        .wrapper {
            min-height: 100vh;
        }
        
        .main-header {
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        
        .brand-link {
            padding: 0.8125rem 1rem;
            background-color: #ffffff;
            border-bottom: 1px solid rgba(0,0,0,.125);
            transition: all 0.3s ease-in-out;
        }
        
        .brand-link:hover {
            text-decoration: none;
            background-color: #f8f9fa;
        }
        
        .brand-text {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--primary-color);
        }
        
        .main-sidebar {
            background-color: #343a40;
        }
        
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: var(--primary-color);
            color: #fff;
            border-radius: 0.375rem;
            margin: 0 0.5rem;
        }
        
        .content-wrapper {
            background-color: #f4f6f9;
            min-height: calc(100vh - 3.5rem);
        }
        
        .card {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border: 0;
        }
        
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,.125);
            padding: 0.75rem 1rem;
        }
        
        .btn {
            border-radius: 0.375rem;
        }
        
        .table th {
            border-top: 0;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        
        .modal-footer {
            border-top: 1px solid rgba(0,0,0,.125);
        }
        
        /* DataTables customizations */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
        }
        
        /* Select2 customizations */
        .select2-container--bootstrap4 .select2-selection {
            height: calc(2.25rem + 2px);
        }
        
        /* Loading spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        /* Toast customizations */
        #toast-container {
            z-index: 9999;
        }
        
        /* Info boxes */
        .info-box {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: 0.375rem;
        }
        
        .small-box {
            border-radius: 0.375rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }
    </style>
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
                    <a href="dashboard_modern.php" class="nav-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="patients_modern.php" class="nav-link">
                        <i class="fas fa-users"></i> Patients
                    </a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Notifications Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" title="Notifications">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-header">3 Notifications</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-users mr-2"></i> 5 new patients today
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-flask mr-2"></i> 12 pending test results
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                    </div>
                </li>
                
                <!-- User Menu -->
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <div class="user-image bg-primary d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 28px; height: 28px; font-size: 0.875rem; font-weight: 600;">
                            <?php echo $user_initial; ?>
                        </div>
                        <span class="d-none d-md-inline ml-2"><?php echo htmlspecialchars($full_name); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <!-- User image -->
                        <li class="user-header bg-primary">
                            <div class="user-image bg-white text-primary d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 65px; height: 65px; font-size: 1.5rem; font-weight: 600;">
                                <?php echo $user_initial; ?>
                            </div>
                            <p class="mt-2">
                                <?php echo htmlspecialchars($full_name); ?>
                                <small>Member since <?php echo date('M Y'); ?></small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <a href="settings.php" class="btn btn-default btn-flat">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <a href="logout.php" class="btn btn-default btn-flat float-right">
                                <i class="fas fa-sign-out-alt"></i> Sign out
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="dashboard_modern.php" class="brand-link">
                <img src="img/logo.png" alt="Lab Logo" class="brand-image img-circle elevation-3" style="opacity: .8; width: 33px; height: 33px;">
                <span class="brand-text">USFitness Lab</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="dashboard_modern.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard_modern.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        
                        <!-- Patients -->
                        <li class="nav-item">
                            <a href="patients_modern.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'patients_modern.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-user-injured"></i>
                                <p>Patients</p>
                            </a>
                        </li>
                        
                        <!-- Doctors -->
                        <li class="nav-item">
                            <a href="doctors_modern.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'doctors_modern.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-user-md"></i>
                                <p>Doctors</p>
                            </a>
                        </li>
                        
                        <!-- Test Orders -->
                        <li class="nav-item">
                            <a href="test-orders_modern.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'test-orders_modern.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Test Orders</p>
                            </a>
                        </li>
                        
                        <!-- Results -->
                        <li class="nav-item">
                            <a href="results_modern.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'results_modern.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-file-medical"></i>
                                <p>Test Results</p>
                            </a>
                        </li>
                        
                        <!-- Equipment -->
                        <li class="nav-item">
                            <a href="equipment_modern.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'equipment_modern.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-tools"></i>
                                <p>Equipment</p>
                            </a>
                        </li>
                        
                        <!-- Reports -->
                        <li class="nav-item">
                            <a href="reports.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Reports</p>
                            </a>
                        </li>
                        
                        <!-- Divider -->
                        <li class="nav-header">SYSTEM</li>
                        
                        <!-- Users -->
                        <li class="nav-item">
                            <a href="users_modern.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'users_modern.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>Users</p>
                            </a>
                        </li>
                        
                        <!-- Settings -->
                        <li class="nav-item">
                            <a href="settings.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>Settings</p>
                            </a>
                        </li>
                        
                        <!-- System Status -->
                        <li class="nav-item">
                            <a href="system_status.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'system_status.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-heartbeat"></i>
                                <p>System Status</p>
                            </a>
                        </li>
                        
                    </ul>
                </nav>
            </div>
        </aside>
